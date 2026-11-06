<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Support\Http\Resources\Schemas\Attributes\Hidden\Hidden;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class VersionableFieldMustSupportSchemaVersion extends Rule
{
    private readonly SchemaVersionResolver $resolver;

    public function __construct(SchemaVersionResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Schema::class) && $this->hasAttribute($node, Version::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $schema = $node->namespacedName?->toString();
        $version = $schema === null ? null : $this->resolver->versionOf($schema);

        if ($version === null) {
            return;
        }

        collect($node->getProperties())
            ->filter(fn (Property $property): bool => $property->isPublic() && ! $property->isStatic() && $property->type !== null && $this->doesNotHaveAttribute($property, Hidden::class))
            ->each(fn (Property $property) => collect($this->resolver->typeClassNames($property->type))
                ->filter(fn (string $class): bool => $this->isVersionableField($class))
                ->reject(fn (string $class): bool => $this->resolver->representedByTargets($class)
                    ->map(fn (string $target) => $this->resolver->versionOf($target))
                    ->contains($version))
                ->each(fn (string $class) => $this->error(
                    '['.class_basename($class).'] has no ['.class_basename(RepresentedBy::class).'] target for version ['.$version.'] required by ['.class_basename($schema).'].',
                    $property->getStartLine(),
                    'Versionable.field.RepresentedBy.required'
                )));
    }

    private function isVersionableField(string $class): bool
    {
        $reflection = (new ObjectType($class))->getClassReflection();

        return $reflection !== null
            && $this->inherits($reflection, Versionable::class)
            && $this->doesNotInherit($reflection, Schemable::class)
            && $this->resolver->representedByTargets($class)->isNotEmpty();
    }
}
