<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemables;

use Illuminate\Database\Eloquent\Model;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;

class TransformsToSchemaOnly extends Model
{
    use TransformsToSchema;
}
