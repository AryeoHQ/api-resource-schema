<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Tags;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;

#[Version(ApiVersion::V1)]
class MissingUntagged extends JsonResource implements Schema
{
    use AsSchema;

    public bool $canEdit { get => auth()->check(); }

    public mixed $viewerId { get => Auth::id(); }

    public mixed $owner { get => request()->user(); }

    public mixed $requester { get => app(Request::class)->user(); }
}
