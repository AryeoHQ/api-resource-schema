<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Support\Http\Resources\Schemas\Fields\Contracts\Versioned;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<ClassLike>
 */
#[NodeType(ClassLike::class)]
class RepresentedByTargetMustBeVersioned extends Rule
{
    private readonly SchemaVersionResolver $resolver;

    public function __construct(SchemaVersionResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return ($node instanceof Class_ || $node instanceof Enum_) && $this->hasAttribute($node, RepresentedBy::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->resolver->attributeClassArgs($node, RepresentedBy::class)
            ->filter(function (array $target): bool {
                $reflection = (new ObjectType($target['class']))->getClassReflection();

                return $reflection !== null && ! $this->inherits($reflection, Versioned::class);
            })
            ->each(fn (array $target) => $this->error(
                '['.class_basename(RepresentedBy::class).'] target ['.class_basename($target['class']).'] must implement ['.class_basename(Versioned::class).'].',
                $target['line'],
                'RepresentedBy.target.Versioned.required'
            ));
    }
}
