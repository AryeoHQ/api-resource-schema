<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Support\Http\Resources\Schemas\Tags\Tag;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Tags\Schemas\Account;
use Tests\Fixtures\Support\Tags\Schemas\Article;
use Tests\Fixtures\Support\Tags\Schemas\Member;
use Tests\Fixtures\Support\Tags\Schemas\Tombstone;
use Tests\Fixtures\Support\Users\User;

Route::middleware('api')->group(function (): void {
    Route::get('users', fn () => User::all()->toSchemaCollection(ApiVersion::V2));

    Route::get('users/tagged', fn () => User::all()->toSchemaCollection(ApiVersion::V2)->withETag(new Tag('"users-order"')));

    Route::get('users/pages', fn () => User::paginate(2)->toSchemaCollection(ApiVersion::V2)->withETag(new Tag('"users-order"')));

    Route::get('users/{user}', fn (User $user) => $user->toSchema(ApiVersion::V2));

    Route::get('users/{user}/appended', fn (User $user) => $user->append('full_name')->toSchema(ApiVersion::V2));

    Route::get('users/{user}/revisions/{revision}', fn (User $user, string $revision) => $user->toSchema(ApiVersion::V2)->additional(['meta' => ['revision' => (int) $revision]]));

    Route::get('members/{user}', fn (User $user) => Member::make($user));

    Route::get('members/{user}/partial', fn (string $user) => Member::make(User::select(['id', 'first_name', 'middle_name'])->findOrFail($user)));

    Route::get('accounts/{user}', fn (string $user) => Account::make(User::withTrashed()->findOrFail($user)));

    Route::get('tombstones/{user}', fn (User $user) => Tombstone::make($user));

    Route::get('posts/{post}/article', fn (Post $post) => Article::make($post));
});
