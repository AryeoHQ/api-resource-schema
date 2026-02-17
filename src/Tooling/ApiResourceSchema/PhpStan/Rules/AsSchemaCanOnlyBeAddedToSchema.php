<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class AsSchemaCanOnlyBeAddedToSchema extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, AsSchema::class)
            && $this->doesNotInherit($node, Schema::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '[AsSchema] trait can only be used on implementations of [Schema].',
            $this->findAsSchemaTrait($node)->getStartLine(),
            'apiResourceSchema.asSchemaOnlyOnSchema'
        );
    }

    private function findAsSchemaTrait(Class_ $node): null|TraitUse
    {
        return collect($node->stmts)
            ->filter(fn ($stmt): bool => $stmt instanceof TraitUse)
            ->first(function (TraitUse $stmt): bool {
                return collect($stmt->traits)
                    ->map(fn ($trait) => $trait->toString())
                    ->contains(AsSchema::class);
            });
    }
}
