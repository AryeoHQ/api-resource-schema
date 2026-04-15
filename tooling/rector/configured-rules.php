<?php

use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;
use Tooling\Rector\Rules\AddInterfaceByTrait;
use Tooling\Rector\Rules\AddTraitByInterface;

return [
    AddInterfaceByTrait::class => [
        AsSchema::class => Schema::class,
        AsSchemaCollection::class => SchemaCollection::class,
    ],
    AddTraitByInterface::class => [
        Schema::class => AsSchema::class,
        SchemaCollection::class => AsSchemaCollection::class,
    ],
];
