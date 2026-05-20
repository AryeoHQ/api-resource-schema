<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Posts;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Fixtures\Support\Users\User;

#[CollectedBy(Collection\Posts::class)]
#[UseFactory(Factory::class)]
#[UseSchema(Schemas\Post::class)]
class Post extends Model implements Schemable
{
    /** @use HasFactory<Factory>  */
    use HasFactory;

    use TransformsToSchema;

    protected $fillable = ['title', 'rating'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
