<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class SchemaMustNotDefineWithResponse extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, [AsSchema::class, AsSchemaCollection::class])
            && $this->withResponse($node) !== null;
    }

    public function handle(Node $node, Scope $scope): void
    {
        $trait = $this->inherits($node, AsSchema::class) ? AsSchema::class : AsSchemaCollection::class;

        $this->error(
            'A class that uses ['.class_basename($trait).'] must not define [withResponse], because ['.class_basename($trait).'] sets the ETag there.',
            $this->withResponse($node)?->getStartLine() ?? $node->getStartLine(),
            'Schema.withResponse.override'
        );
    }

    private function withResponse(Class_ $node): null|ClassMethod
    {
        return collect($node->getMethods())->first(
            fn (ClassMethod $method): bool => $method->name->toLowerString() === 'withresponse'
        );
    }
}
