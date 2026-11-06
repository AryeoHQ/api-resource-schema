<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\SchemableCollections;

use Illuminate\Database\Eloquent\Collection;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tests\Fixtures\Tooling\PhpStan\SchemaCollections\AnotherV1SchemaCollection;
use Tests\Fixtures\Tooling\PhpStan\SchemaCollections\ValidSchemaCollection;

#[UseSchemaCollection(ValidSchemaCollection::class)]
#[UseSchemaCollection(AnotherV1SchemaCollection::class)]
class DuplicateSchemaCollectionVersions extends Collection implements SchemableCollection
{
    use TransformsToSchemaCollection;
}
