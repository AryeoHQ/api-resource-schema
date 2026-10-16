<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface Contributor
{
    /**
     * @param  TModel  $resource
     */
    public function contribute(Model $resource): string;
}
