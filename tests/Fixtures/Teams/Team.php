<?php

declare(strict_types=1);

namespace Tests\Fixtures\Teams;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\Fixtures\TeamUser\TeamUser;
use Tests\Fixtures\Users\User;

#[UseFactory(Factory::class)]
#[UseResource(Schema::class)]
class Team extends Model
{
    /** @use HasFactory<Factory>  */
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * @return BelongsToMany<User, $this, TeamUser>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(TeamUser::class)->withPivot('id');
    }
}
