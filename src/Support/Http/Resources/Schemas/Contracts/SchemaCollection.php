<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Contracts;

interface SchemaCollection
{
    public Version $schemaVersion { get; }

    /** @var string */
    public $collects { get; set; }
}
