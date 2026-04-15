<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes;

use Attribute;
use LogicException;
use Support\Http\Resources\Schemas\Contracts\Schema;

#[Attribute(Attribute::TARGET_CLASS)]
final class Collects
{
    public string $schema;

    public function __construct(string $schema)
    {
        throw_unless(
            is_a($schema, Schema::class, true), // @phpstan-ignore-line
            LogicException::class,
            "The [{$schema}] class must implement the [Schema]."
        );

        $this->schema = $schema;
    }
}
