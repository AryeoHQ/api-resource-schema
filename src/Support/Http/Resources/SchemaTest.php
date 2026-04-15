<?php

declare(strict_types=1);

namespace Support\Http\Resources;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\Exceptions\CollectedByNotDefined;
use Support\Http\Resources\Schemas\Concerns\ConditionallyLoadsAttributesTestCases;
use Support\Http\Resources\Schemas\Fields\Discarded;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Fixtures\Support\Posts\Schemas;
use Tests\Fixtures\Support\Users\User;
use Tests\Fixtures\Tooling\PhpStan\Schemas\MissingCollectedBy;
use Tests\TestCase;

#[CoversTrait(AsSchema::class)]
class SchemaTest extends TestCase
{
    use ConditionallyLoadsAttributesTestCases;

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

    #[Test]
    public function it_is_collected_by(): void
    {
        $this->assertInstanceOf(
            Schemas\Posts::class,
            Schemas\Post::collection(Post::factory()->times(2)->make())
        );
    }

    #[Test]
    public function it_throws_an_exception_when_collected_by_is_missing(): void
    {
        $this->expectException(CollectedByNotDefined::class);

        MissingCollectedBy::collection([]);
    }
}
