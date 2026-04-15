<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\SchemaCollections;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;

class MissingAsSchemaCollection extends ResourceCollection implements SchemaCollection
{
    public string $collects = 'Tests\Fixtures\Tooling\PhpStan\Schemas\ValidSchema';
}
