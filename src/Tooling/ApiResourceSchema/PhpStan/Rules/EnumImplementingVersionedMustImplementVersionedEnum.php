<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Fields\Contracts\Versioned;
use Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Enum_>
 */
#[NodeType(Enum_::class)]
class EnumImplementingVersionedMustImplementVersionedEnum extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Versioned::class) && $this->doesNotInherit($node, VersionedEnum::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            'An enum implementing ['.class_basename(Versioned::class).'] must implement ['.class_basename(VersionedEnum::class).'].',
            $node->name?->getStartLine() ?? $node->getStartLine(),
            'Versioned.enum.VersionedEnum.required'
        );
    }
}
