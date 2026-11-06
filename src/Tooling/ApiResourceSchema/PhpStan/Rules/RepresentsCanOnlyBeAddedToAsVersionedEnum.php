<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Represents;
use Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Enum_>
 */
#[NodeType(Enum_::class)]
class RepresentsCanOnlyBeAddedToAsVersionedEnum extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->doesNotInherit($node, AsVersionedEnum::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        collect($node->stmts)
            ->filter(fn (Node\Stmt $stmt): bool => $stmt instanceof EnumCase && $this->hasAttribute($stmt, Represents::class))
            ->each(fn (EnumCase $case) => $this->error(
                '['.class_basename(Represents::class).'] attribute can only be used on cases of an enum using the ['.class_basename(AsVersionedEnum::class).'] trait.',
                $case->getStartLine(),
                'Represents.AsVersionedEnum.only'
            ));
    }
}
