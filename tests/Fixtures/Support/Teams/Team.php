<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Teams;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Fixtures\Support\TeamUser\TeamUser;
use Tests\Fixtures\Support\Users\User;

#[CollectedBy(Collection\Teams::class)]
#[UseFactory(Factory::class)]
#[UseSchema(Schemas\Team::class)]
class Team extends Model implements Schemable
{
    /** @use HasFactory<Factory>  */
    use HasFactory;

    use TransformsToSchema;

    protected $fillable = ['name'];

    /**
     * @return BelongsToMany<User, $this, TeamUser>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(TeamUser::class)->withPivot('id');
    }
}
