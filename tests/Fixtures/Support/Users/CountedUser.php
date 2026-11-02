<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Users;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;

#[CollectedBy(Collection\Users::class)]
#[UseSchema(Schemas\V1\User::class)]
#[UseSchema(Schemas\V2\User::class)]
class CountedUser extends User
{
    protected $table = 'users';

    protected $withCount = ['posts'];

    public function getForeignKey(): string
    {
        return 'user_id';
    }
}
