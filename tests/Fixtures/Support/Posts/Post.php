<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Posts;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Fixtures\Support\Users\User;

#[UseFactory(Factory::class)]
#[UseResource(Schemas\Post::class)]
class Post extends Model
{
    /** @use HasFactory<Factory>  */
    use HasFactory;

    protected $fillable = ['title', 'rating'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
