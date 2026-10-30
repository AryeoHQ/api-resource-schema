<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class SchemaMustNotDefineWithResponse extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, [Schema::class, SchemaCollection::class])
            && $node->getMethod('withResponse') !== null;
    }

    public function handle(Node $node, Scope $scope): void
    {
        $contract = $this->inherits($node, Schema::class) ? Schema::class : SchemaCollection::class;

        $this->error(
            '['.class_basename($contract).'] must not define [withResponse()]; it would drop the entity tag.',
            $node->getMethod('withResponse')?->name->getStartLine() ?? $node->getStartLine(),
            class_basename($contract).'.WithResponse.forbidden'
        );
    }
}
