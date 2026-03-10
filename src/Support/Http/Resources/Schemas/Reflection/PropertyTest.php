<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Reflection;

use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Tests\Fixtures\Support\Teams;
use Tests\Fixtures\Support\TeamUser;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    #[Test]
    public function it_is_makeable(): void
    {
        $property = Property::make(
            new ReflectionProperty(Teams\Schema::class, 'name')
        );

        $this->assertInstanceOf(Property::class, $property);
    }

    #[Test]
    public function it_can_get_property_name(): void
    {
        $property = Property::make(
            new ReflectionProperty(Teams\Schema::class, 'name')
        );

        $this->assertSame('name', $property->name->toString());
    }

    #[Test]
    public function it_can_check_if_property_is_public(): void
    {
        $property = Property::make(
            new ReflectionProperty(Teams\Schema::class, 'name')
        );

        $this->assertTrue($property->isPublic);
    }

    #[Test]
    public function it_can_check_if_property_is_on_class(): void
    {
        $property = Property::make(
            new ReflectionProperty(Teams\Schema::class, 'name')
        );

        $this->assertTrue($property->isOn(Teams\Schema::class));
        $this->assertFalse($property->isOn(TeamUser\Schema::class));
    }

    #[Test]
    public function it_forwards_calls_to_reflection(): void
    {
        $property = Property::make(
            new ReflectionProperty(Teams\Schema::class, 'name')
        );

        $this->assertTrue($property->hasType());
    }

    #[Test]
    public function it_throws_exception_for_invalid_method_calls(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $property = Property::make(
            new ReflectionProperty(Teams\Schema::class, 'name')
        );

        $property->invalidMethod(); // @phpstan-ignore-line
    }
}
