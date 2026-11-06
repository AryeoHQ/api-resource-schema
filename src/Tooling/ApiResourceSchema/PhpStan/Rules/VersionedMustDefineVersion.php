<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Fields\Contracts\Versioned;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<ClassLike>
 */
#[NodeType(ClassLike::class)]
class VersionedMustDefineVersion extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return ($node instanceof Enum_ || ($node instanceof Class_ && ! $node->isAbstract()))
            && $this->inherits($node, Versioned::class)
            && $this->doesNotHaveAttribute($node, Version::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(Versioned::class).'] must have the ['.class_basename(Version::class).'] attribute.',
            $node->name?->getStartLine() ?? $node->getStartLine(),
            'Versioned.Version.required'
        );
    }
}
