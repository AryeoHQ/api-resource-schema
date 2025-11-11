<?php

declare(strict_types=1);

namespace Tests\Fixtures\Posts;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Fixtures\Users\User;

#[UseFactory(Factory::class)]
#[UseResource(Schema::class)]
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

    public function toApiResource(): Schema
    {
        return new Schema($this);
    }
}
