<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Users\Collection;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tests\Fixtures\Support\Users\User;

/**
 * @extends EloquentCollection<int, User>
 */
#[UseSchemaCollection(Schemas\V1\Users::class)]
#[UseSchemaCollection(Schemas\V2\Users::class)]
class Users extends EloquentCollection implements SchemableCollection
{
    use TransformsToSchemaCollection;
}
