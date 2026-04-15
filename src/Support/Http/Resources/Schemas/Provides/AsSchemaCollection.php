<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Provides;

use ReflectionObject;
use Support\Http\Resources\Schemas\Attributes\Collects;
use Support\Http\Resources\Schemas\Attributes\Exceptions\CollectsNotDefined;

trait AsSchemaCollection
{
    protected function collects(): null|string
    {
        $this->collects ??= throw_unless( // @phpstan-ignore-line
            data_get(new ReflectionObject($this)->getAttributes(Collects::class), 0)?->newInstance()->schema,
            CollectsNotDefined::class,
            $this
        );

        return $this->collects;
    }
}
