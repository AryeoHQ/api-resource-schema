<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\TeamUser\Schemas;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Support\Http\Resources\Schemas\Attributes\Collects;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;

#[Collects(TeamUser::class)]
class TeamUsers extends ResourceCollection implements SchemaCollection
{
    use AsSchemaCollection;
}
