<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Fields\Concerns\TransformsToVersion;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<ClassLike>
 */
#[NodeType(ClassLike::class)]
class VersionableMustUseTransformsToVersion extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return ($node instanceof Class_ || $node instanceof Enum_)
            && $this->inherits($node, Versionable::class)
            && $this->doesNotInherit($node, TransformsToVersion::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(Versionable::class).'] must use the ['.class_basename(TransformsToVersion::class).'] trait.',
            $node->name?->getStartLine() ?? $node->getStartLine(),
            'Versionable.TransformsToVersion.required'
        );
    }
}
