<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class UseSchemaMustNotDuplicateVersion extends Rule
{
    private readonly SchemaVersionResolver $resolver;

    public function __construct(SchemaVersionResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->hasAttribute($node, UseSchema::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->resolver->attributeClassArgs($node, UseSchema::class)
            ->map(fn (array $schema): array => [...$schema, 'version' => $this->resolver->versionOf($schema['class'])])
            ->reject(fn (array $schema): bool => $schema['version'] === null)
            ->groupBy('version')
            ->filter(fn ($schemas): bool => $schemas->count() > 1)
            ->each(fn ($schemas, $version) => $schemas->skip(1)->each(fn (array $schema) => $this->error(
                '['.class_basename(UseSchema::class).'] declares more than one schema for version ['.class_basename($version).'].',
                $schema['line'],
                'UseSchema.version.duplicate'
            )));
    }
}
