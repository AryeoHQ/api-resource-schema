<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Collection;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class SchemableCollectedByMustImplementSchemableCollection extends Rule
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

        if ($collectionClass === null) {
            return;
        }

        if ($collectionClass === Collection::class) {
            return;
        }

        $objectType = new ObjectType($collectionClass);
        $reflection = $objectType->getClassReflection();

        if ($reflection === null) {
            return;
        }

        if ($objectType->isInstanceOf(SchemableCollection::class)->yes()) {
            return;
        }

        $collectionFile = $reflection->getFileName();

        if ($collectionFile === null) {
            return;
        }

        $collectionLine = $this->resolver->classStartLine($collectionFile, $reflection->getNativeReflection());

        $this->errors->push(
            RuleErrorBuilder::message('['.class_basename($collectionClass).'] must implement ['.class_basename(SchemableCollection::class).'].')
                ->file($collectionFile)
                ->line($collectionLine)
                ->identifier('Schemable.CollectedBy.SchemableCollection.required')
                ->build()
        );
    }
}
