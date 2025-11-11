<?php

declare(strict_types=1);

namespace Tests\Fixtures\Posts;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SchemaCollection extends ResourceCollection
{
    use \Support\Http\Resources\Schemas\Provides\SchemaCollection;
}
