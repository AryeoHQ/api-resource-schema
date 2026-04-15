<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Console\Commands\MakeResource\References;

use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\References\GenericClass;

final class Schema extends GenericClass
{
    public Stringable $stubPath {
        get => str(__DIR__.'/stubs/schema.stub');
    }
}
