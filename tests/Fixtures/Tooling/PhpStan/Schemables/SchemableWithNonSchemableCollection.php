<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemables;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Model;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Fixtures\Support\Users\Schemas\V2\User;
use Tests\Fixtures\Tooling\PhpStan\SchemableCollections\PlainCustomCollection;

#[CollectedBy(PlainCustomCollection::class)]
#[UseSchema(User::class)]
class SchemableWithNonSchemableCollection extends Model implements Schemable
{
    use TransformsToSchema;
}
