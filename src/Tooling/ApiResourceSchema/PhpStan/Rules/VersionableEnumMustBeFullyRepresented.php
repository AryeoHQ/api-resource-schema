<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\AttributeReflection;
use PHPStan\Reflection\EnumCaseReflection;
use PHPStan\Type\Enum\EnumCaseObjectType;
use PHPStan\Type\ObjectType;
use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Represents;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;
use Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Enum_>
 */
#[NodeType(Enum_::class)]
class VersionableEnumMustBeFullyRepresented extends Rule
{
    private readonly SchemaVersionResolver $resolver;

    public function __construct(SchemaVersionResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Versionable::class) && $this->hasAttribute($node, RepresentedBy::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $source = $node->namespacedName?->toString();

        if ($source === null) {
            return;
        }

        $cases = collect($node->stmts)
            ->filter(fn (Node\Stmt $stmt): bool => $stmt instanceof EnumCase)
            ->map(fn (EnumCase $case): string => $case->name->toString());

        $this->resolver->attributeClassArgs($node, RepresentedBy::class)->each(function (array $target) use ($source, $cases): void {
            $counts = $this->representedCaseCounts($target['class'], $source);

            if ($counts === null) {
                return;
            }

            $cases->each(function (string $case) use ($counts, $source, $target): void {
                $count = $counts->get($case, 0);

                if ($count === 1) {
                    return;
                }

                $this->error(
                    $count === 0
                        ? '['.class_basename($target['class']).'] does not represent ['.class_basename($source).'::'.$case.'].'
                        : '['.class_basename($source).'::'.$case.'] is represented by more than one case of ['.class_basename($target['class']).'].',
                    $target['line'],
                    $count === 0 ? 'Represents.case.missing' : 'Represents.case.duplicate'
                );
            });
        });
    }

    /**
     * @return \Illuminate\Support\Collection<string, int>|null
     */
    private function representedCaseCounts(string $target, string $source): null|\Illuminate\Support\Collection
    {
        $reflection = (new ObjectType($target))->getClassReflection();

        if ($reflection === null || ! $reflection->isEnum() || ! $this->inherits($reflection, AsVersionedEnum::class)) {
            return null;
        }

        return collect($reflection->getEnumCases())
            ->flatMap(fn (EnumCaseReflection $case): array => $case->getAttributes())
            ->filter(fn (AttributeReflection $attribute): bool => $attribute->getName() === Represents::class)
            ->flatMap(fn (AttributeReflection $attribute): array => ($attribute->getArgumentTypes()['cases'] ?? null)?->getEnumCases() ?? [])
            ->filter(fn (EnumCaseObjectType $case): bool => $case->getClassName() === $source)
            ->map(fn (EnumCaseObjectType $case): string => $case->getEnumCaseName())
            ->countBy();
    }
}
