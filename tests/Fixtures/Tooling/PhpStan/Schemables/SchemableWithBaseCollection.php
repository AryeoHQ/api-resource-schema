<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemables;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Fixtures\Support\Users\Schemas\V2\User;

#[CollectedBy(Collection::class)]
#[UseSchema(User::class)]
class SchemableWithBaseCollection extends Model implements Schemable
{
    use TransformsToSchema;
}
