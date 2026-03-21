<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Users;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Fixtures\Support\Teams\Team;
use Tests\Fixtures\Support\TeamUser\TeamUser;

/**
 * @property string $first_name
 * @property string|null $middle_name
 * @property string|null $middle_initial
 * @property string $last_name
 * @property string $full_name
 */
#[UseFactory(Factory::class)]
#[UseResource(Schema::class)]
class User extends Model
{
    /** @use HasFactory<Factory>  */
    use HasFactory;

    use SoftDeletes;

    protected $hidden = ['biography'];

    protected $fillable = ['first_name', 'middle_name', 'last_name', 'biography', 'email', 'username', 'date_of_birth'];

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return BelongsToMany<Team, $this, TeamUser>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->using(TeamUser::class)->withPivot('id');
    }

    /**
     * @return Attribute<string|null, never> @phpstan-ignore generics.notGeneric
     */
    public function middleInitial(): Attribute
    {
        return Attribute::make(
            get: fn (): null|string => $this->middle_name ? strtoupper($this->middle_name[0]).'.' : null,
        );
    }

    /**
     * @return Attribute<string, never> @phpstan-ignore generics.notGeneric
     */
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => collect([$this->first_name, $this->middle_name, $this->last_name])->filter()->join(' '),
        );
    }

    protected function casts()
    {
        return [
            'date_of_birth' => 'date',
        ];
    }
}
