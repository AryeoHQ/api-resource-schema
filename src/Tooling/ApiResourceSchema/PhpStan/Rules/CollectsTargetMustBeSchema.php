<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class CollectsTargetMustBeSchema extends Rule
{
    private readonly SchemaVersionResolver $resolver;

    public function __construct(SchemaVersionResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->hasAttribute($node, Collects::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->resolver->attributeClassArgs($node, Collects::class)
            ->filter(function (array $target): bool {
                $reflection = (new ObjectType($target['class']))->getClassReflection();

                return $reflection !== null && ! $this->inherits($reflection, Schema::class);
            })
            ->each(fn (array $target) => $this->error(
                '['.class_basename(Collects::class).'] target ['.class_basename($target['class']).'] must implement ['.class_basename(Schema::class).'].',
                $target['line'],
                'Collects.target.Schema.required'
            ));
    }
}
