<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class UseSchemaCollectionTargetMustBeSchemaCollection extends Rule
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
            ->filter(function (array $target): bool {
                $reflection = (new ObjectType($target['class']))->getClassReflection();

                return $reflection !== null && ! $this->inherits($reflection, SchemaCollection::class);
            })
            ->each(fn (array $target) => $this->error(
                '['.class_basename(UseSchemaCollection::class).'] target ['.class_basename($target['class']).'] must implement ['.class_basename(SchemaCollection::class).'].',
                $target['line'],
                'UseSchemaCollection.target.SchemaCollection.required'
            ));
    }
}
