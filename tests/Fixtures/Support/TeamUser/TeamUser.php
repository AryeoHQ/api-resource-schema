<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\TeamUser;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Fixtures\Support\Teams\Team;
use Tests\Fixtures\Support\Users\User;

#[CollectedBy(Collection\TeamUsers::class)]
#[UseFactory(Factory::class)]
#[UseSchema(Schemas\TeamUser::class)]
class TeamUser extends Pivot implements Schemable
{
    /** @use HasFactory<Factory>  */
    use HasFactory;

    use TransformsToSchema;

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
