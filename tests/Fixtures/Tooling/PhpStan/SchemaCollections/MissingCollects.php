<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\SchemaCollections;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;

class MissingCollects extends ResourceCollection implements SchemaCollection
{
    use AsSchemaCollection;
}
