<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class TransformsToSchemaCollectionCanOnlyBeAddedToSchemableCollection extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, TransformsToSchemaCollection::class) && $this->doesNotInherit($node, SchemableCollection::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(TransformsToSchemaCollection::class).'] trait can only be used on implementations of ['.class_basename(SchemableCollection::class).'].',
            $this->findTransformsToSchemaCollectionTrait($node)->getStartLine(),
            'TransformsToSchemaCollection.SchemableCollection.only'
        );
    }

    private function findTransformsToSchemaCollectionTrait(Class_ $node): null|TraitUse
    {
        return collect($node->stmts)
            ->filter(fn ($stmt): bool => $stmt instanceof TraitUse)
            ->first(function (TraitUse $stmt): bool {
                return collect($stmt->traits)
                    ->map(fn ($trait) => $trait->toString())
                    ->contains(TransformsToSchemaCollection::class);
            });
    }
}
