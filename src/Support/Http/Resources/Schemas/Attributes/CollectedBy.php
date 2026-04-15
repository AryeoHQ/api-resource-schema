<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes;

use Attribute;
use LogicException;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;

#[Attribute(Attribute::TARGET_CLASS)]
final class CollectedBy
{
    public string $schemaCollection;

    public function __construct(string $schemaCollection)
    {
        throw_unless(
            is_a($schemaCollection, SchemaCollection::class, true), // @phpstan-ignore-line
            LogicException::class,
            "The [{$schemaCollection}] class must implement the [SchemaCollection]."
        );

        $this->schemaCollection = $schemaCollection;
    }
}
