<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\TeamUser\Collection;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tests\Fixtures\Support\TeamUser\Schemas\TeamUsers as SchemaCollectionTeamUsers;
use Tests\Fixtures\Support\TeamUser\TeamUser;

/**
 * @extends EloquentCollection<int, TeamUser>
 */
#[UseSchemaCollection(SchemaCollectionTeamUsers::class)]
class TeamUsers extends EloquentCollection implements SchemableCollection
{
    use TransformsToSchemaCollection;
}
