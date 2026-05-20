<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Contracts;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

interface Schemable
{
    /** @var Collection<int, class-string<Schema&JsonResource>> */
    public Collection $schemas { get; }

    /** @var Collection<int, Version> */
    public Collection $schemaVersions { get; }

    public function toSchema(Version $version): Schema&JsonResource;
}
