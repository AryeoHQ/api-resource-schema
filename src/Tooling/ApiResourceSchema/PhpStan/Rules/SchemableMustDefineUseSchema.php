<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class SchemableMustDefineUseSchema extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return ! $node->isAbstract()
            && $this->inherits($node, Schemable::class)
            && $this->doesNotHaveAttribute($node, UseSchema::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(Schemable::class).'] must have the ['.class_basename(UseSchema::class).'] attribute.',
            $node->name?->getStartLine() ?? $node->getStartLine(),
            'Schemable.UseSchema.required'
        );
    }
}
