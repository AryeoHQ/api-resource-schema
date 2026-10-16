<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Support\Http\Resources\Schemas\Attributes\ContributesTag\ContributesTag;
use Support\Http\Resources\Schemas\Attributes\Hidden\Hidden;
use Support\Http\Resources\Schemas\Attributes\Untagged\Untagged;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class ConditionalFieldMustBeCoveredByTag extends Rule
{
    /** @var array<string, int> */
    private const array RELATION_ARGUMENT = [
        'whenLoaded' => 0,
        'whenCounted' => 0,
        'whenAggregated' => 0,
        'whenExistsLoaded' => 0,
        'whenPivotLoaded' => 0,
        'whenPivotLoadedAs' => 1,
        'whenHas' => 0,
    ];

    /** @var array<int, string> */
    private const array CONDITIONAL = ['when', 'unless'];

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Schema::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $covered = $this->coveredRelations($node);

        $this->fields($node)->each(function (Property $field) use ($covered, $scope): void {
            $this->checkRelations($field, $covered);
            $this->checkCaller($field, $scope);
            $this->checkRequestCondition($field, $scope);
        });
    }

    /**
     * @param  Collection<int, string>  $covered
     */
    private function checkRelations(Property $field, Collection $covered): void
    {
        foreach ($this->helperCalls($field, array_keys(self::RELATION_ARGUMENT)) as $call) {
            $helper = $call->name instanceof Identifier ? $call->name->toString() : '';

            if ($helper === 'whenHas') {
                $this->checkAttribute($field, $call, $covered);

                continue;
            }

            $relation = $call->getArgs()[self::RELATION_ARGUMENT[$helper]]->value ?? null;

            if (! $relation instanceof String_) {
                $this->error(
                    '['.class_basename(Schema::class).'] field ['.$this->name($field).'] calls ['.$helper.'] with a non-literal relation, which no ['.class_basename(ContributesTag::class).'] attribute can cover.',
                    $call->getStartLine(),
                    'Schema.ContributesTag.literalRelation'
                );

                continue;
            }

            if ($covered->doesntContain($relation->value)) {
                $this->error(
                    '['.class_basename(Schema::class).'] field ['.$this->name($field).'] calls ['.$helper.'] for relation ['.$relation->value.'] and needs a ['.class_basename(ContributesTag::class).'] attribute for that relation.',
                    $call->getStartLine(),
                    'Schema.ContributesTag.required'
                );
            }
        }
    }

    /**
     * @param  Collection<int, string>  $covered
     */
    private function checkAttribute(Property $field, MethodCall $call, Collection $covered): void
    {
        if ($this->hasAttribute($field, Untagged::class)) {
            return;
        }

        $attribute = $call->getArgs()[0]->value ?? null;

        if (! $attribute instanceof String_) {
            $this->error(
                '['.class_basename(Schema::class).'] field ['.$this->name($field).'] calls [whenHas] with a non-literal attribute, which no ['.class_basename(ContributesTag::class).'] attribute can cover.',
                $call->getStartLine(),
                'Schema.ContributesTag.literalRelation'
            );

            return;
        }

        if ($covered->doesntContain($attribute->value)) {
            $this->error(
                '['.class_basename(Schema::class).'] field ['.$this->name($field).'] calls [whenHas] for attribute ['.$attribute->value.'] and needs a ['.class_basename(ContributesTag::class).'] attribute for it, or the ['.class_basename(Untagged::class).'] attribute.',
                $call->getStartLine(),
                'Schema.ContributesTag.required'
            );
        }
    }

    private function checkRequestCondition(Property $field, Scope $scope): void
    {
        foreach ($this->helperCalls($field, self::CONDITIONAL) as $call) {
            $condition = $call->getArgs()[0]->value ?? null;

            if ($condition === null || (new NodeFinder)->findFirst([$condition], fn (Node $node): bool => $this->readsRequest($node, $scope)) === null) {
                continue;
            }

            $helper = $call->name instanceof Identifier ? $call->name->toString() : '';

            $this->error(
                '['.class_basename(Schema::class).'] field ['.$this->name($field).'] gates ['.$helper.'] on the request and should use [whenRequested] instead.',
                $call->getStartLine(),
                'Schema.whenRequested.required'
            );
        }
    }

    private function readsRequest(Node $node, Scope $scope): bool
    {
        return match (true) {
            $node instanceof FuncCall && $this->calls($node, ['request']) => true,
            $node instanceof StaticCall => $node->class instanceof Name && in_array($node->class->toString(), [RequestFacade::class, 'Request'], true),
            $node instanceof Expr => (new ObjectType(Request::class))->isSuperTypeOf($scope->getType($node))->yes(),
            default => false,
        };
    }

    /**
     * @param  array<int, string>  $helpers
     * @return array<int, MethodCall>
     */
    private function helperCalls(Property $field, array $helpers): array
    {
        /** @var array<int, MethodCall> */
        return (new NodeFinder)->find($field->hooks, fn (Node $node): bool => $node instanceof MethodCall
            && $node->var instanceof Variable
            && $node->var->name === 'this'
            && $node->name instanceof Identifier
            && in_array($node->name->toString(), $helpers, true));
    }

    private function checkCaller(Property $field, Scope $scope): void
    {
        if ($this->hasAttribute($field, Untagged::class)) {
            return;
        }

        $read = (new NodeFinder)->findFirst($field->hooks, fn (Node $node): bool => $this->readsCaller($node, $scope));

        if ($read !== null) {
            $this->error(
                '['.class_basename(Schema::class).'] field ['.$this->name($field).'] reads the authenticated user and needs the ['.class_basename(Untagged::class).'] attribute.',
                $read->getStartLine(),
                'Schema.Untagged.required'
            );
        }
    }

    private function readsCaller(Node $node, Scope $scope): bool
    {
        return match (true) {
            $node instanceof FuncCall => $this->calls($node, ['auth']),
            $node instanceof StaticCall => $this->callsAuthFacade($node),
            $node instanceof MethodCall, $node instanceof NullsafeMethodCall => $node->name instanceof Identifier
                && $node->name->toLowerString() === 'user'
                && $this->isCallerSource($node->var, $scope),
            default => false,
        };
    }

    private function isCallerSource(Expr $receiver, Scope $scope): bool
    {
        if ($this->calls($receiver, ['request', 'auth']) || $this->callsAuthFacade($receiver)) {
            return true;
        }

        $type = $scope->getType($receiver);

        return (new ObjectType(Request::class))->isSuperTypeOf($type)->yes()
            || (new ObjectType(Guard::class))->isSuperTypeOf($type)->yes();
    }

    /**
     * @param  array<int, string>  $functions
     */
    private function calls(Expr $node, array $functions): bool
    {
        return $node instanceof FuncCall && $node->name instanceof Name && in_array($node->name->toLowerString(), $functions, true);
    }

    private function callsAuthFacade(Expr $node): bool
    {
        return $node instanceof StaticCall && $node->class instanceof Name && in_array($node->class->toString(), [Auth::class, 'Auth'], true);
    }

    /**
     * @return Collection<int, Property>
     */
    private function fields(Class_ $node): Collection
    {
        return collect($node->stmts)
            ->filter(fn (Node $stmt): bool => $stmt instanceof Property
                && $stmt->isPublic()
                && ! $stmt->isStatic()
                && $stmt->hooks !== []
                && $this->doesNotHaveAttribute($stmt, Hidden::class))
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function coveredRelations(Class_ $node): Collection
    {
        return collect($node->attrGroups)
            ->flatMap(fn (AttributeGroup $group): array => $group->attrs)
            ->filter(fn (Attribute $attribute): bool => $attribute->name->toString() === ContributesTag::class)
            ->map(function (Attribute $attribute): null|string {
                $relation = collect($attribute->args)->first(
                    fn (Arg $arg, int $index): bool => $arg->name === null ? $index === 1 : $arg->name->toString() === 'relation'
                )?->value;

                return $relation instanceof String_ ? $relation->value : null;
            })
            ->whereNotNull()
            ->values();
    }

    private function name(Property $field): string
    {
        return $field->props[0]->name->toString();
    }
}
