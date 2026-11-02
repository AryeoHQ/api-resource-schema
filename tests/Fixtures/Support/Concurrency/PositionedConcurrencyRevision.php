<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Concurrency;

use Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Support\Database\Eloquent\Concurrency\Attributes\NamedConcurrencyRevision;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class PositionedConcurrencyRevision extends NamedConcurrencyRevision
{
    /**
     * @param  Relation<*, *, *>  $relation
     * @return Builder<*>
     */
    public function digestQuery(Relation $relation): Builder
    {
        return $relation->getQuery()->clone()->reorder()
            ->orderBy($relation->getRelated()->qualifyColumn('position'))
            ->orderBy($relation->getRelated()->getQualifiedKeyName());
    }
}
