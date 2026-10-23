<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceWithResponse extends JsonResource
{
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->header('X-Trace', 'trace');
    }
}
