<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Represents;
use Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Enum_>
 */
#[NodeType(Enum_::class)]
class VersionedEnumMustNotDefineRepresenting extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, VersionedEnum::class) && $node->getMethod('representing') instanceof ClassMethod;
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(VersionedEnum::class).'] must map its cases with the ['.class_basename(Represents::class).'] attribute instead of defining [representing()].',
            $node->getMethod('representing')?->getStartLine() ?? $node->getStartLine(),
            'VersionedEnum.representing.disallowed'
        );
    }
}
