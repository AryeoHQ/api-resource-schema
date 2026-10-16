<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Support\Http\Resources\Schemas\Tags\Tag;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Users\User;

Route::middleware('api')->group(function (): void {
    Route::get('users', fn () => User::all()->toSchemaCollection(ApiVersion::V2));

    Route::get('users/tagged', fn () => User::all()->toSchemaCollection(ApiVersion::V2)->withETag(new Tag('"users-order"')));

    Route::get('users/pages', fn () => User::paginate(2)->toSchemaCollection(ApiVersion::V2)->withETag(new Tag('"users-order"')));

    Route::get('users/{user}', fn (User $user) => $user->toSchema(ApiVersion::V2));
});
