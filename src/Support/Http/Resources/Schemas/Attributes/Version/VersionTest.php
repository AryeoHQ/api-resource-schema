<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\Version;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\Version\Exceptions\NotDefined;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Users\Schemas;
use Tests\Fixtures\Tooling\PhpStan\Schemas\MissingVersion;
use Tests\TestCase;

#[CoversClass(Version::class)]
class VersionTest extends TestCase
{
    #[Test]
    public function it_resolves_the_version(): void
    {
        $this->assertSame(ApiVersion::V2, Version::resolve(Schemas\V2\User::class));
    }

    #[Test]
    public function it_throws_when_the_attribute_is_missing(): void
    {
        $this->expectException(NotDefined::class);

        Version::resolve(MissingVersion::class);
    }
}
