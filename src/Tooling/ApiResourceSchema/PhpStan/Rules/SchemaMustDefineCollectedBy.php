<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Attributes\CollectedBy;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class SchemaMustDefineCollectedBy extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Schema::class)
            && $this->doesNotHaveAttribute($node, CollectedBy::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '[Schema] must have the [CollectedBy] attribute.',
            $node->name->getStartLine(),
            'apiResourceSchema.schemaMustDefineCollectedBy'
        );
    }
}
