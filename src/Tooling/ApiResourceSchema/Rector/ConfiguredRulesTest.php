<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\Rector;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfiguredRulesTest extends TestCase
{
    #[Test]
    public function it_registers_schemable_and_schemable_collection_pairings(): void
    {
        $rules = require dirname(__DIR__, 4).'/tooling/rector/configured-rules.php';

        $this->assertArrayHasKey(\Tooling\Rector\Rules\AddInterfaceByTrait::class, $rules);
        $this->assertArrayHasKey(\Tooling\Rector\Rules\AddTraitByInterface::class, $rules);

        $this->assertSame(
            \Support\Http\Resources\Schemas\Contracts\Schemable::class,
            $rules[\Tooling\Rector\Rules\AddInterfaceByTrait::class][\Support\Http\Resources\Schemas\Concerns\TransformsToSchema::class]
        );

        $this->assertSame(
            \Support\Http\Resources\Schemas\Contracts\SchemableCollection::class,
            $rules[\Tooling\Rector\Rules\AddInterfaceByTrait::class][\Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection::class]
        );

        $this->assertSame(
            \Support\Http\Resources\Schemas\Concerns\TransformsToSchema::class,
            $rules[\Tooling\Rector\Rules\AddTraitByInterface::class][\Support\Http\Resources\Schemas\Contracts\Schemable::class]
        );

        $this->assertSame(
            \Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection::class,
            $rules[\Tooling\Rector\Rules\AddTraitByInterface::class][\Support\Http\Resources\Schemas\Contracts\SchemableCollection::class]
        );
    }

    #[Test]
    public function it_registers_versionable_and_versioned_pairings(): void
    {
        $rules = require dirname(__DIR__, 4).'/tooling/rector/configured-rules.php';

        $this->assertSame(
            \Support\Http\Resources\Schemas\Fields\Contracts\Versionable::class,
            $rules[\Tooling\Rector\Rules\AddInterfaceByTrait::class][\Support\Http\Resources\Schemas\Fields\Concerns\TransformsToVersion::class]
        );

        $this->assertSame(
            \Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum::class,
            $rules[\Tooling\Rector\Rules\AddInterfaceByTrait::class][\Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum::class]
        );

        $this->assertSame(
            \Support\Http\Resources\Schemas\Fields\Concerns\TransformsToVersion::class,
            $rules[\Tooling\Rector\Rules\AddTraitByInterface::class][\Support\Http\Resources\Schemas\Fields\Contracts\Versionable::class]
        );

        $this->assertSame(
            \Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum::class,
            $rules[\Tooling\Rector\Rules\AddTraitByInterface::class][\Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum::class]
        );

        $this->assertArrayNotHasKey(
            \Support\Http\Resources\Schemas\Fields\Contracts\Versioned::class,
            $rules[\Tooling\Rector\Rules\AddTraitByInterface::class]
        );
    }
}
