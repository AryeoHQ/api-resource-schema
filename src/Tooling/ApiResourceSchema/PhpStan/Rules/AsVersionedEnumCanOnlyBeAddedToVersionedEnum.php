<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum;
use Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<ClassLike>
 */
#[NodeType(ClassLike::class)]
class AsVersionedEnumCanOnlyBeAddedToVersionedEnum extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return ($node instanceof Class_ || $node instanceof Enum_)
            && $this->inherits($node, AsVersionedEnum::class)
            && $this->doesNotInherit($node, VersionedEnum::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(AsVersionedEnum::class).'] trait can only be used on implementations of ['.class_basename(VersionedEnum::class).'].',
            $node->name?->getStartLine() ?? $node->getStartLine(),
            'AsVersionedEnum.VersionedEnum.only'
        );
    }
}
