<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\SchemaCollections;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;
use Tests\Fixtures\Tooling\PhpStan\Schemas\ValidSchemaWithCollectedBy;

#[Collects(ValidSchemaWithCollectedBy::class)]
class AnotherV1SchemaCollection extends ResourceCollection implements SchemaCollection
{
    use AsSchemaCollection;
}
