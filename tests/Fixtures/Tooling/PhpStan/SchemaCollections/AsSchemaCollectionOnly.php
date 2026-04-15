<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\SchemaCollections;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;

class AsSchemaCollectionOnly extends ResourceCollection
{
    use AsSchemaCollection;

    public string $collects = 'Tests\Fixtures\Tooling\PhpStan\Schemas\ValidSchema';
}
