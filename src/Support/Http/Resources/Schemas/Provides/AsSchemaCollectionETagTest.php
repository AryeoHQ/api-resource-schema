<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Provides;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Support\Users\User;
use Tests\TestCase;
use Workbench\App\Providers\WorkbenchServiceProvider;

#[CoversTrait(AsSchemaCollection::class)]
class AsSchemaCollectionETagTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), WorkbenchServiceProvider::class];
    }

    #[Test]
    public function a_plain_list_carries_no_tag(): void
    {
        User::factory()->count(2)->create();

        $this->getJson('users')->assertOk()->assertHeaderMissing('ETag');
    }

    #[Test]
    public function a_list_carries_the_tag_it_was_given(): void
    {
        User::factory()->count(2)->create();

        $this->getJson('users/tagged')->assertOk()->assertHeader('ETag', '"users-order"');
    }

    #[Test]
    public function a_paginated_list_carries_the_tag_it_was_given(): void
    {
        User::factory()->count(3)->create();

        $this->getJson('users/pages')->assertOk()->assertJsonCount(2, 'data')->assertHeader('ETag', '"users-order"');
    }

    #[Test]
    public function the_tag_leaves_the_body_unchanged(): void
    {
        User::factory()->count(2)->create();

        $this->getJson('users/tagged')->assertExactJson($this->getJson('users')->json());
    }
}
