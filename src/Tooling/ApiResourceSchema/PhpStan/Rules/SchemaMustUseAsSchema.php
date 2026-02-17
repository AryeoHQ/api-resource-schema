<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class SchemaMustUseAsSchema extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Schema::class)
            && $this->doesNotInherit($node, AsSchema::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '[Schema] must use the [AsSchema] trait.',
            $node->name->getStartLine(),
            'apiResourceSchema.schemaMustUseAsSchema'
        );
    }
}
