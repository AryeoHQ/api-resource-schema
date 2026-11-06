<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemables;

use Illuminate\Database\Eloquent\Model;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Fixtures\Tooling\PhpStan\Schemas\ValidSchema;
use Tests\Fixtures\Tooling\PhpStan\Schemas\ValidSchemaWithCollectedBy;

#[UseSchema(ValidSchema::class)]
#[UseSchema(ValidSchemaWithCollectedBy::class)]
class DuplicateSchemaVersions extends Model implements Schemable
{
    use TransformsToSchema;
}
