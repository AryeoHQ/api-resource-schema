<?php

use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;
use Tooling\Rector\Rules\AddInterfaceByTrait;
use Tooling\Rector\Rules\AddTraitByInterface;

return [
    AddInterfaceByTrait::class => [
        AsSchema::class => Schema::class,
        AsSchemaCollection::class => SchemaCollection::class,
        TransformsToSchema::class => Schemable::class,
        TransformsToSchemaCollection::class => SchemableCollection::class,
    ],
    AddTraitByInterface::class => [
        Schema::class => AsSchema::class,
        SchemaCollection::class => AsSchemaCollection::class,
        Schemable::class => TransformsToSchema::class,
        SchemableCollection::class => TransformsToSchemaCollection::class,
    ],
];
