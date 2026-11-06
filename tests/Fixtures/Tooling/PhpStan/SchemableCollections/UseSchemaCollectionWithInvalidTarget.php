<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\SchemableCollections;

use Illuminate\Database\Eloquent\Collection;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tests\Fixtures\Tooling\PhpStan\Schemas\ValidSchema;

#[UseSchemaCollection(ValidSchema::class)]
class UseSchemaCollectionWithInvalidTarget extends Collection implements SchemableCollection
{
    use TransformsToSchemaCollection;
}
