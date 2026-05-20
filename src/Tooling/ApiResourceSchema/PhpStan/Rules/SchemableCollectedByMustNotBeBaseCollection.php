<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Collection;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class SchemableCollectedByMustNotBeBaseCollection extends Rule
{
    private readonly SchemaVersionResolver $resolver;

    public function __construct(SchemaVersionResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Schemable::class) && $this->hasAttribute($node, CollectedBy::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $collectionClass = $this->resolver->collectedByClass($node);

        if ($collectionClass === null || $collectionClass !== Collection::class) {
            return;
        }

        $attributeLine = collect($node->attrGroups)
            ->flatMap(fn ($attrGroup) => $attrGroup->attrs)
            ->first(fn ($attr) => $attr->name instanceof FullyQualified && $attr->name->toString() === CollectedBy::class)
            ?->getStartLine();

        $this->error(
            '['.class_basename(Schemable::class).'] ['.class_basename(CollectedBy::class).'] must not be the base Eloquent Collection.',
            $attributeLine ?? $node->name?->getStartLine() ?? $node->getStartLine(),
            'Schemable.CollectedBy.EloquentCollection.disallowed'
        );
    }
}
