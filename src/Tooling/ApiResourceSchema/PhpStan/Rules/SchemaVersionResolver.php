<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use Illuminate\Support\Collection;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PHPStan\Type\ObjectType;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Attributes\Version\Version;

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
    private function extractAllAttributeClassArgs(Class_ $node, string $attributeClass): array
    {
        return collect($node->attrGroups)
            ->flatMap(fn ($attrGroup) => $attrGroup->attrs)
            ->filter(fn ($attr) => $attr->name instanceof FullyQualified
                && $attr->name->toString() === $attributeClass
                && $attr->args !== [])
            ->map(fn ($attr) => $attr->args[0]->value)
            ->filter(fn ($value) => $value instanceof ClassConstFetch && $value->class instanceof FullyQualified)
            ->map(fn (ClassConstFetch $value) => $value->class->toString())
            ->values()
            ->all();
    }
}
