<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;
use Tests\Fixtures\Support\Posts\Schemas\Post;
use Tests\Fixtures\Support\Posts\Schemas\Posts;

class Controller extends BaseController
{
    public function index(): Posts
    {
        return Post::collection([]);
    }

    public function plain(): AnonymousResourceCollection
    {
        return JsonResource::collection([]);
    }
}
