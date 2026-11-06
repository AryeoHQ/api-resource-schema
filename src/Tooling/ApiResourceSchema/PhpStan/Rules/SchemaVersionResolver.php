<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use Illuminate\Support\Collection;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\UnionType;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PHPStan\Type\ObjectType;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;

class SchemaVersionResolver
{
    private readonly Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return \Illuminate\Support\Collection<int, int|string>
     */
    public function modelVersions(Class_ $node): Collection
    {
        return collect($this->extractAllAttributeClassArgs($node, UseSchema::class))
            ->map(fn (string $schemaClass) => $this->resolveVersionFromSchema($schemaClass))
            ->whereNotNull()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    public function representedByTargets(string $class): Collection
    {
        $reflection = (new ObjectType($class))->getClassReflection();

        return match ($reflection === null) {
            true => collect(),
            false => collect($reflection->getNativeReflection()->getAttributes(RepresentedBy::class))
                ->map(fn (\ReflectionAttribute $attribute): mixed => $attribute->getArguments()[0] ?? null)
                ->filter(fn (mixed $target): bool => is_string($target))
                ->values(),
        };
    }

    public function versionOf(string $class): null|int|string
    {
        return $this->resolveVersionFromSchema($class);
    }

    /**
     * @return \Illuminate\Support\Collection<int, int|string>
     */
    public function schemableVersions(string $class): Collection
    {
        $reflection = (new ObjectType($class))->getClassReflection();

        return match ($reflection === null) {
            true => collect(),
            false => collect($reflection->getNativeReflection()->getAttributes(UseSchema::class))
                ->map(fn (\ReflectionAttribute $attribute): mixed => $attribute->getArguments()[0] ?? null)
                ->filter(fn (mixed $schema): bool => is_string($schema))
                ->map(fn (string $schema) => $this->resolveVersionFromSchema($schema))
                ->whereNotNull()
                ->values(),
        };
    }

    /**
     * @return array<int, string>
     */
    public function typeClassNames(Identifier|Name|ComplexType $type): array
    {
        return match (true) {
            $type instanceof Name => [$type->toString()],
            $type instanceof NullableType => $this->typeClassNames($type->type),
            $type instanceof UnionType, $type instanceof IntersectionType => collect($type->types)
                ->flatMap(fn (Identifier|Name|IntersectionType $inner): array => $this->typeClassNames($inner))
                ->all(),
            default => [],
        };
    }

    public function schemaCollectionVersionOf(string $class): null|int|string
    {
        return $this->resolveVersionFromSchemaCollection($class);
    }

    /**
     * @return \Illuminate\Support\Collection<int, int|string>
     */
    public function collectionVersions(string $collectionClass): Collection
    {
        $collectionReflection = (new ObjectType($collectionClass))->getClassReflection();

        return match ($collectionReflection === null) {
            true => collect(),
            false => collect($collectionReflection->getNativeReflection()->getAttributes(UseSchemaCollection::class))
                ->map(fn (\ReflectionAttribute $attribute) => $this->resolveVersionFromSchemaCollection($attribute->newInstance()->schemaCollection))
                ->whereNotNull()
                ->values(),
        };
    }

    public function collectedByClass(Class_ $node): null|string
    {
        $attr = collect($node->attrGroups)
            ->flatMap(fn ($attrGroup) => $attrGroup->attrs)
            ->first(fn ($attr) => $attr->name instanceof FullyQualified
                && $attr->name->toString() === \Illuminate\Database\Eloquent\Attributes\CollectedBy::class);

        if ($attr === null || $attr->args === []) {
            return null;
        }

        $value = $attr->args[0]->value;

        return match ($value instanceof ClassConstFetch && $value->class instanceof FullyQualified) {
            true => $value->class->toString(),
            false => null,
        };
    }

    /**
     * @param  \ReflectionClass<object>  $nativeReflection
     */
    public function classStartLine(string $file, \ReflectionClass $nativeReflection): int
    {
        $stmts = $this->parser->parse(file_get_contents($file));

        /** @var \PhpParser\Node\Stmt\Class_|null $classNode */
        $classNode = (new NodeFinder)->findFirst($stmts, fn (\PhpParser\Node $n) => $n instanceof Class_ && $n->name?->toString() === $nativeReflection->getShortName());

        $line = $classNode?->name?->getStartLine() ?? $nativeReflection->getStartLine();

        return match ($line !== false) {
            true => $line,
            false => 1,
        };
    }

    private function resolveVersionFromSchema(string $class): null|int|string
    {
        $reflection = (new ObjectType($class))->getClassReflection();

        if ($reflection === null) {
            return null;
        }

        $versionAttributes = $reflection->getNativeReflection()->getAttributes(Version::class);

        if ($versionAttributes === []) {
            return null;
        }

        return $versionAttributes[0]->newInstance()->version->value;
    }

    private function resolveVersionFromSchemaCollection(string $schemaCollectionClass): null|int|string
    {
        $reflection = (new ObjectType($schemaCollectionClass))->getClassReflection();

        if ($reflection === null) {
            return null;
        }

        $collectsAttributes = $reflection->getNativeReflection()->getAttributes(Collects::class);

        if ($collectsAttributes === []) {
            return null;
        }

        $schemaClass = $collectsAttributes[0]->newInstance()->schema;

        return $this->resolveVersionFromSchema($schemaClass);
    }

    /**
     * @param  class-string  $attributeClass
     * @return array<int, string>
     */
    private function extractAllAttributeClassArgs(ClassLike $node, string $attributeClass): array
    {
        return $this->attributeClassArgs($node, $attributeClass)->pluck('class')->all();
    }

    /**
     * @param  class-string  $attributeClass
     * @return Collection<int, array{class: string, line: int}>
     */
    public function attributeClassArgs(ClassLike $node, string $attributeClass): Collection
    {
        return collect($node->attrGroups)
            ->flatMap(fn (AttributeGroup $attrGroup): array => $attrGroup->attrs)
            ->filter(fn (Attribute $attr): bool => $attr->name instanceof FullyQualified
                && $attr->name->toString() === $attributeClass
                && $attr->args !== [])
            ->map(fn (Attribute $attr): array => ['value' => $attr->args[0]->value, 'line' => $attr->getStartLine()])
            ->filter(fn (array $arg): bool => $arg['value'] instanceof ClassConstFetch && $arg['value']->class instanceof FullyQualified)
            ->map(fn (array $arg): array => ['class' => $arg['value']->class->toString(), 'line' => $arg['line']])
            ->values();
    }
}
