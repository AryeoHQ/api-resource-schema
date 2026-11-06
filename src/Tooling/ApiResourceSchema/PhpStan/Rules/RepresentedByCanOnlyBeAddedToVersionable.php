<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<ClassLike>
 */
#[NodeType(ClassLike::class)]
class RepresentedByCanOnlyBeAddedToVersionable extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return ($node instanceof Class_ || $node instanceof Enum_)
            && $this->hasAttribute($node, RepresentedBy::class)
            && $this->doesNotInherit($node, Versionable::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(RepresentedBy::class).'] attribute can only be used on implementations of ['.class_basename(Versionable::class).'].',
            $node->name?->getStartLine() ?? $node->getStartLine(),
            'RepresentedBy.Versionable.only'
        );
    }
}
