<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Concerns;

use Support\Http\Resources\Schemas\Fields\Discarded;
use Support\Http\Resources\Schemas\Fields\Exceptions\MergeUnlessNotSupported;
use Support\Http\Resources\Schemas\Fields\Exceptions\MergeWhenNotSupported;

trait ConditionallyLoadsAttributes
{
    protected function when($condition, $value, $default = new Discarded)
    {
        return parent::when($condition, $value, $default);
    }

    public function unless($condition, $value, $default = new Discarded)
    {
        return parent::unless($condition, $value, $default);
    }

    protected function mergeWhen($condition, $value, $default = new Discarded)
    {
        throw new MergeWhenNotSupported;
    }

    protected function mergeUnless($condition, $value, $default = new Discarded)
    {
        throw new MergeUnlessNotSupported;
    }

    public function whenHas($attribute, $value = null, $default = new Discarded)
    {
        return parent::whenHas($attribute, $value, $default);
    }

    protected function whenNull($value, $default = new Discarded)
    {
        return parent::whenNull($value, $default);
    }

    protected function whenNotNull($value, $default = new Discarded)
    {
        return parent::whenNotNull($value, $default);
    }

    protected function whenAppended($attribute, $value = null, $default = new Discarded)
    {
        return parent::whenAppended($attribute, $value, $default);
    }

    protected function whenLoaded($relationship, $value = null, $default = new Discarded)
    {
        return parent::whenLoaded($relationship, $value, $default);
    }

    public function whenCounted($relationship, $value = null, $default = new Discarded)
    {
        return parent::whenCounted($relationship, $value, $default);
    }

    public function whenAggregated($relationship, $column, $aggregate, $value = null, $default = new Discarded)
    {
        return parent::whenAggregated($relationship, $column, $aggregate, $value, $default);
    }

    public function whenExistsLoaded($relationship, $value = null, $default = new Discarded)
    {
        return parent::whenExistsLoaded($relationship, $value, $default);
    }

    protected function whenPivotLoaded($table, $value, $default = new Discarded)
    {
        return parent::whenPivotLoaded($table, $value, $default);
    }

    protected function whenPivotLoadedAs($accessor, $table, $value, $default = new Discarded)
    {
        return parent::whenPivotLoadedAs($accessor, $table, $value, $default);
    }

    protected function transform($value, callable $callback, $default = new Discarded)
    {
        return parent::transform($value, $callback, $default);
    }
}
