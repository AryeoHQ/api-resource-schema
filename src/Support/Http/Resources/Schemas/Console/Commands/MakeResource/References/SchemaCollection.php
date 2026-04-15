<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Console\Commands\MakeResource\References;

use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\References\GenericClass;

final class SchemaCollection extends GenericClass
{
    public Stringable $stubPath {
        get => str(__DIR__.'/stubs/schema-collection.stub');
    }

    public Schema $schema {
        get => resolve(Schema::class, [
            'name' => $this->name->singular(),
            'baseNamespace' => $this->baseNamespace,
        ]);
    }
}
