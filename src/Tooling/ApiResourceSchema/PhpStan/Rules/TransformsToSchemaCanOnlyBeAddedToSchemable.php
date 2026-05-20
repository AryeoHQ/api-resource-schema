<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class TransformsToSchemaCanOnlyBeAddedToSchemable extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, TransformsToSchema::class) && $this->doesNotInherit($node, Schemable::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(TransformsToSchema::class).'] trait can only be used on implementations of ['.class_basename(Schemable::class).'].',
            $this->findTransformsToSchemaTrait($node)->getStartLine(),
            'TransformsToSchema.Schemable.only'
        );
    }

    private function findTransformsToSchemaTrait(Class_ $node): null|TraitUse
    {
        return collect($node->stmts)
            ->filter(fn ($stmt): bool => $stmt instanceof TraitUse)
            ->first(function (TraitUse $stmt): bool {
                return collect($stmt->traits)
                    ->map(fn ($trait) => $trait->toString())
                    ->contains(TransformsToSchema::class);
            });
    }
}
