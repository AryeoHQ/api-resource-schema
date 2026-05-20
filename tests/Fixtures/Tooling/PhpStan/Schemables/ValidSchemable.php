<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemables;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Model;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Fixtures\Tooling\PhpStan\SchemableCollections\ValidSchemableCollectionWithParity;
use Tests\Fixtures\Tooling\PhpStan\Schemas\ValidSchema;
use Tests\Fixtures\Tooling\PhpStan\Schemas\ValidSchemaV2;

#[CollectedBy(ValidSchemableCollectionWithParity::class)]
#[UseSchema(ValidSchema::class)]
#[UseSchema(ValidSchemaV2::class)]
class ValidSchemable extends Model implements Schemable
{
    use TransformsToSchema;
}
