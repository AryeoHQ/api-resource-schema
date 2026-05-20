<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
class SchemaMustDefineVersion extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Schema::class) && $this->doesNotHaveAttribute($node, Version::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            '['.class_basename(Schema::class).'] must have the ['.class_basename(Version::class).'] attribute.',
            $node->name?->getStartLine() ?? $node->getStartLine(),
            'Schema.Version.required'
        );
    }
}
