<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Console\Commands\MakeResource\Events;

use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;

final class BuildingSchema
{
    public Stringable $fqcn;

    /** @var \Illuminate\Support\Collection<int, string> */
    public Collection $properties {
        get => $this->properties ??= collect();
    }

    /** @var \Illuminate\Support\Collection<int, string> */
    public Collection $imports {
        get => $this->imports ??= collect();
    }

    public function __construct(string $fqcn)
    {
        $this->fqcn = str($fqcn);
    }
}
