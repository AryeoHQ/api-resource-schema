<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class SchemableCollectionMustDefineUseSchemaCollection extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return ! $node->isAbstract()
            && $this->inherits($node, SchemableCollection::class)
            && $this->doesNotHaveAttribute($node, UseSchemaCollection::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(SchemableCollection::class).'] must have the ['.class_basename(UseSchemaCollection::class).'] attribute.',
            $node->name?->getStartLine() ?? $node->getStartLine(),
            'SchemableCollection.UseSchemaCollection.required'
        );
    }
}
