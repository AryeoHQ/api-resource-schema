<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Users\Collection\Schemas\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;
use Tests\Fixtures\Support\Users\Schemas\V2\User;

#[Collects(User::class)]
class Users extends ResourceCollection implements SchemaCollection
{
    use AsSchemaCollection;
}
