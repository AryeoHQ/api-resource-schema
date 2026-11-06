<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<ClassLike>
 */
#[NodeType(ClassLike::class)]
class RepresentedByMustNotDuplicateVersion extends Rule
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
            ->map(fn (array $target): array => [...$target, 'version' => $this->resolver->versionOf($target['class'])])
            ->reject(fn (array $target): bool => $target['version'] === null)
            ->groupBy('version')
            ->filter(fn ($targets): bool => $targets->count() > 1)
            ->each(fn ($targets, $version) => $targets->skip(1)->each(fn (array $target) => $this->error(
                '['.class_basename(RepresentedBy::class).'] declares more than one target for version ['.class_basename($version).'].',
                $target['line'],
                'RepresentedBy.version.duplicate'
            )));
    }
}
