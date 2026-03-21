<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Console\Commands\MakeResource\Events;

use Illuminate\Support\Stringable;

final class BuildingSchema
{
    public Stringable $fqcn;

    /** @var array<int, string> */
    public array $properties = [];

    /** @var array<int, string> */
    public array $imports = [];

    public function __construct(string $fqcn)
    {
        $this->fqcn = str($fqcn);
    }
}
