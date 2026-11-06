<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class UseSchemaCollectionMustNotDuplicateVersion extends Rule
{
    private readonly SchemaVersionResolver $resolver;

    public function __construct(SchemaVersionResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->hasAttribute($node, UseSchemaCollection::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->resolver->attributeClassArgs($node, UseSchemaCollection::class)
            ->map(fn (array $schemaCollection): array => [...$schemaCollection, 'version' => $this->resolver->schemaCollectionVersionOf($schemaCollection['class'])])
            ->reject(fn (array $schemaCollection): bool => $schemaCollection['version'] === null)
            ->groupBy('version')
            ->filter(fn ($schemaCollections): bool => $schemaCollections->count() > 1)
            ->each(fn ($schemaCollections, $version) => $schemaCollections->skip(1)->each(fn (array $schemaCollection) => $this->error(
                '['.class_basename(UseSchemaCollection::class).'] declares more than one schema collection for version ['.class_basename($version).'].',
                $schemaCollection['line'],
                'UseSchemaCollection.version.duplicate'
            )));
    }
}
