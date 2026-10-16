<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;
use Tests\Fixtures\Tooling\PhpStan\Schemas\ValidSchema;

#[Collects(ValidSchema::class)]
class SchemaCollectionWithResponse extends ResourceCollection implements SchemaCollection
{
    use AsSchemaCollection;

    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->header('X-Trace', 'trace');
    }
}
