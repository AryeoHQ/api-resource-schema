<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\SchemaCollections;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Support\Http\Resources\Schemas\Attributes\Collects;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;
use Tests\Fixtures\Tooling\PhpStan\Schemas\ValidSchema;

#[Collects(ValidSchema::class)]
class ValidSchemaCollection extends ResourceCollection implements SchemaCollection
{
    use AsSchemaCollection;
}
