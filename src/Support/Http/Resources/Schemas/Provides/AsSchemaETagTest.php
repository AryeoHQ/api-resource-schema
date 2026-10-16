<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Provides;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Users\User;
use Tests\TestCase;
use Workbench\App\Providers\WorkbenchServiceProvider;

#[CoversTrait(AsSchema::class)]
class AsSchemaETagTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), WorkbenchServiceProvider::class];
    }

    #[Test]
    public function a_get_returns_the_schema_tag(): void
    {
        $user = User::factory()->create();

        $this->getJson("users/{$user->id}")
            ->assertOk()
            ->assertHeader('ETag', (string) User::findOrFail($user->id)->toTag(ApiVersion::V2));
    }

    #[Test]
    public function the_tag_changes_after_an_update(): void
    {
        $user = User::factory()->create(['first_name' => 'John']);
        $before = $this->getJson("users/{$user->id}")->headers->get('ETag');

        $user->update(['first_name' => 'Jane']);

        $after = $this->getJson("users/{$user->id}")->assertHeader('ETag')->headers->get('ETag');

        $this->assertNotSame($before, $after);
    }

    #[Test]
    public function a_request_flag_keeps_the_tag(): void
    {
        $user = User::factory()->create();
        $without = $this->getJson("users/{$user->id}")->headers->get('ETag');

        $this->getJson("users/{$user->id}?with_email=1")
            ->assertJsonPath('data.email', $user->email)
            ->assertHeader('ETag', (string) $without);
    }

    #[Test]
    public function the_body_is_the_schema(): void
    {
        $user = User::factory()->create();

        $this->getJson("users/{$user->id}")
            ->assertExactJson(['data' => User::findOrFail($user->id)->toSchema(ApiVersion::V2)->resolve()]);
    }
}
