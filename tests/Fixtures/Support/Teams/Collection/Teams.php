<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Teams\Collection;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tests\Fixtures\Support\Teams\Schemas\Teams as SchemaCollectionTeams;
use Tests\Fixtures\Support\Teams\Team;

/**
 * @extends EloquentCollection<int, Team>
 */
#[UseSchemaCollection(SchemaCollectionTeams::class)]
class Teams extends EloquentCollection implements SchemableCollection
{
    use TransformsToSchemaCollection;
}
