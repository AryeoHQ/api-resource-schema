<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Tags;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Request as RequestFacade;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;

#[Version(ApiVersion::V1)]
class RequestGatedCondition extends JsonResource implements Schema
{
    use AsSchema;

    public mixed $email { get => $this->when(request()->boolean('with_email'), fn () => $this->resource->email); }

    public mixed $username { get => $this->unless(! RequestFacade::has('with_username'), fn () => $this->resource->username); }

    public mixed $phone { get => $this->when(app(Request::class)->has('with_phone'), fn () => $this->resource->phone); }
}
