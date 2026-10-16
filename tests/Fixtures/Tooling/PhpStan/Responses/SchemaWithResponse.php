<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;

#[Version(ApiVersion::V1)]
class SchemaWithResponse extends JsonResource implements Schema
{
    use AsSchema;

    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->header('X-Trace', 'trace');
    }
}
