<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Contracts;

use Support\Http\Resources\Schemas\Tags\Tag;

interface SchemaCollection
{
    public Version $schemaVersion { get; }

    /** @var string */
    public $collects { get; set; }

    public function withETag(Tag $tag): static;
}
