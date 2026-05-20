<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\SchemableCollections;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;

class ValidSchemableCollection extends EloquentCollection implements SchemableCollection
{
    use TransformsToSchemaCollection;
}
