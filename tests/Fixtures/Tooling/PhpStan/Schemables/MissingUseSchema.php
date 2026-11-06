<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemables;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Model;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Fixtures\Tooling\PhpStan\SchemableCollections\ValidSchemableCollectionWithParity;

#[CollectedBy(ValidSchemableCollectionWithParity::class)]
class MissingUseSchema extends Model implements Schemable
{
    use TransformsToSchema;
}
