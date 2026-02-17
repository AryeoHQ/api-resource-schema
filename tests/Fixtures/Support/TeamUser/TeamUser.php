<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\TeamUser;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Tests\Fixtures\Support\Teams\Team;
use Tests\Fixtures\Support\Users\User;

#[UseFactory(Factory::class)]
#[UseResource(Schema::class)]
class TeamUser extends Pivot
{
    /** @use HasFactory<Factory>  */
    use HasFactory;

    public $incrementing = true;

    public $timestamps = false;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
