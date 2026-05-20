<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class UseSchemaMustHaveMatchingUseSchemaCollection extends Rule
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

        $missing = $this->resolver->modelVersions($node)
            ->diff($this->resolver->collectionVersions($collectionClass));

        if ($missing->isEmpty()) {
            return;
        }

        $collectionReflection = (new ObjectType($collectionClass))->getClassReflection();
        $collectionFile = $collectionReflection?->getFileName();

        if ($collectionFile === null) {
            return;
        }

        $collectionLine = $this->resolver->classStartLine($collectionFile, $collectionReflection->getNativeReflection());

        $missing->each(fn (string $version) => $this->errors->push(
            RuleErrorBuilder::message('['.class_basename(SchemableCollection::class).'] is missing #['.class_basename(UseSchemaCollection::class).'] for version ['.$version.'] declared by ['.class_basename($node->namespacedName->toString()).'].')
                ->file($collectionFile)
                ->line($collectionLine)
                ->identifier('UseSchema.CollectedBy.UseSchemaCollection.matchingRequired')
                ->build()
        ));
    }
}
