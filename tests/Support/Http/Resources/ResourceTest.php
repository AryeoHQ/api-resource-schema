<?php

declare(strict_types=1);

namespace Tests\Support\Http\Resources;

use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Discarded;
use Tests\Fixtures\Support\Users\User;
use Tests\Support\Http\Resources\Schemas\Concerns\ConditionallyLoadsAttributesCases;
use Tests\Support\Http\Resources\Schemas\Provides\SchemaCases;
use Tests\TestCase;

class ResourceTest extends TestCase
{
    use ConditionallyLoadsAttributesCases;
    use SchemaCases;

    #[Test]
    public function to_array(): void
    {
        $data = ['first_name' => 'John', 'last_name' => 'Doe'];

        $resource = User::factory()->state($data)->create()->toResource();

        $result = collect(
            (array) $resource->toArray(request())
        )->reject(fn ($value) => $value instanceof Discarded);

        $this->assertSame($data, $result->toArray());
    }

    #[Test]
    public function to_json(): void
    {
        $data = ['first_name' => 'John', 'last_name' => 'Doe'];
        $resource = User::factory()->state($data)->create()->toResource();

        $this->assertSame(json_encode($data), $resource->toJson());
    }

    #[Test]
    public function to_json_contains_discardable_field_when_filled(): void
    {
        request()->merge(['with_email' => true]);
        $data = ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@doe.com'];
        $resource = User::factory()->state($data)->create()->toResource();

        $this->assertSame(json_encode($data), $resource->toJson());
    }
}
