<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
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
    ];

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Schema::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $covered = $this->coveredRelations($node);

        $this->fields($node)->each(function (Property $field) use ($covered): void {
            $this->checkRelations($field, $covered);
            $this->checkCaller($field);
        });
    }

    /**
     * @param  Collection<int, string>  $covered
     */
    private function checkRelations(Property $field, Collection $covered): void
    {
        /** @var array<int, MethodCall> $calls */
        $calls = (new NodeFinder)->find($field->hooks, fn (Node $node): bool => $node instanceof MethodCall
            && $node->var instanceof Variable
            && $node->var->name === 'this'
            && $node->name instanceof Identifier
            && array_key_exists($node->name->toString(), self::RELATION_ARGUMENT));

        foreach ($calls as $call) {
            $helper = $call->name instanceof Identifier ? $call->name->toString() : '';
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

    private function checkCaller(Property $field): void
    {
        if ($this->hasAttribute($field, Untagged::class)) {
            return;
        }

        $read = (new NodeFinder)->findFirst($field->hooks, fn (Node $node): bool => $this->readsCaller($node));

        if ($read !== null) {
            $this->error(
                '['.class_basename(Schema::class).'] field ['.$this->name($field).'] reads the authenticated user and needs the ['.class_basename(Untagged::class).'] attribute.',
                $read->getStartLine(),
                'Schema.Untagged.required'
            );
        }
    }

    private function readsCaller(Node $node): bool
    {
        return match (true) {
            $node instanceof FuncCall => $node->name instanceof Name && $node->name->toLowerString() === 'auth',
            $node instanceof StaticCall => $node->class instanceof Name && in_array($node->class->toString(), [Auth::class, 'Auth'], true),
            $node instanceof MethodCall, $node instanceof NullsafeMethodCall => $node->name instanceof Identifier && $node->name->toLowerString() === 'user',
            default => false,
        };
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
