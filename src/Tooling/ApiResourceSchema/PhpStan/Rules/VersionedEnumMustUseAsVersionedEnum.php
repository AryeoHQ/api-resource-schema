<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum;
use Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Enum_>
 */
#[NodeType(Enum_::class)]
class VersionedEnumMustUseAsVersionedEnum extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, VersionedEnum::class) && $this->doesNotInherit($node, AsVersionedEnum::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(VersionedEnum::class).'] must use the ['.class_basename(AsVersionedEnum::class).'] trait.',
            $node->name?->getStartLine() ?? $node->getStartLine(),
            'VersionedEnum.AsVersionedEnum.required'
        );
    }
}
