<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class UseSchemaCollectionMustHaveMatchingUseSchema extends Rule
{
    private readonly SchemaVersionResolver $resolver;

    public function __construct(SchemaVersionResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Schemable::class)
            && $this->hasAttribute($node, UseSchema::class)
            && $this->hasAttribute($node, CollectedBy::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $collectionClass = $this->resolver->collectedByClass($node);

        if ($collectionClass === null) {
            return;
        }

        $this->resolver->collectionVersions($collectionClass)
            ->diff($this->resolver->modelVersions($node))
            ->each(fn (string $version) => $this->error(
                '['.class_basename(Schemable::class).'] is missing #['.class_basename(UseSchema::class).'] for version ['.$version.'] declared by ['.class_basename($collectionClass).'].',

                $node->name?->getStartLine() ?? $node->getStartLine(),
                'UseSchemaCollection.Collects.UseSchema.matchingRequired'
            ));
    }
}
