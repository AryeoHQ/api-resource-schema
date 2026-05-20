<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class SchemaCollectionMustDefineCollects extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, SchemaCollection::class)
            && $this->doesNotHaveAttribute($node, Collects::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(SchemaCollection::class).'] must have the ['.class_basename(Collects::class).'] attribute.',
            $node->name?->getStartLine() ?? $node->getStartLine(),
            'SchemaCollection.Collects.required'
        );
    }
}
