<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class AsSchemaCollectionCanOnlyBeAddedToSchemaCollection extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, AsSchemaCollection::class)
            && $this->doesNotInherit($node, SchemaCollection::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '[AsSchemaCollection] trait can only be used on implementations of [SchemaCollection].',
            $this->findAsSchemaCollectionTrait($node)->getStartLine(),
            'apiResourceSchema.asSchemaCollectionOnlyOnSchemaCollection'
        );
    }

    private function findAsSchemaCollectionTrait(Class_ $node): null|TraitUse
    {
        return collect($node->stmts)
            ->filter(fn ($stmt): bool => $stmt instanceof TraitUse)
            ->first(function (TraitUse $stmt): bool {
                return collect($stmt->traits)
                    ->map(fn ($trait) => $trait->toString())
                    ->contains(AsSchemaCollection::class);
            });
    }
}
