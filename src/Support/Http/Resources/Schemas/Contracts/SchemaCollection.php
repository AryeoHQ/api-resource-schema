<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Contracts;

use Support\Http\Resources\Schemas\Tags\EntityTag;

interface SchemaCollection
{
    public Version $schemaVersion { get; }

    /** @var string */
    public $collects { get; set; }

    public function withTag(EntityTag $tag): static;
}
