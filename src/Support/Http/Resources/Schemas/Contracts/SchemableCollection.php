<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Contracts;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

interface SchemableCollection
{
    /** @var Collection<int, class-string<SchemaCollection&ResourceCollection>> */
    public Collection $schemas { get; }

    /** @var Collection<int, Version> */
    public Collection $schemaVersions { get; }

    public function toSchemaCollection(Version $version): SchemaCollection&ResourceCollection;
}
