<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemables;

use Illuminate\Database\Eloquent\Model;
use Support\Http\Resources\Schemas\Contracts\Schemable;

abstract class MissingTransformsToSchema extends Model implements Schemable {}
