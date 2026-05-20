<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class SchemableCollectionMustUseTransformsToSchemaCollection extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, SchemableCollection::class) && $this->doesNotInherit($node, TransformsToSchemaCollection::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(SchemableCollection::class).'] must use the ['.class_basename(TransformsToSchemaCollection::class).'] trait.',
            $node->name?->getStartLine() ?? $node->getStartLine(),
            'SchemableCollection.TransformsToSchemaCollection.required'
        );
    }
}
