<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Users;

use Support\Database\Eloquent\Concurrency\Contracts\NamesConcurrencyRevision;

enum UserRevision: string implements NamesConcurrencyRevision
{
    case PostsOrder = 'posts_order';
}
