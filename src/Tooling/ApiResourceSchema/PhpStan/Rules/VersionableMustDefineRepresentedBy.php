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
class VersionableMustDefineRepresentedBy extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return ($node instanceof Enum_ || ($node instanceof Class_ && ! $node->isAbstract()))
            && $this->inherits($node, Versionable::class)
            && $this->doesNotHaveAttribute($node, RepresentedBy::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(Versionable::class).'] must have the ['.class_basename(RepresentedBy::class).'] attribute.',
            $node->name?->getStartLine() ?? $node->getStartLine(),
            'Versionable.RepresentedBy.required'
        );
    }
}
