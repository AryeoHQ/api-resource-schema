<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\Version\Exceptions\NotFound;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Exceptions\Unrepresented;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Represents;
use Support\Http\Resources\Schemas\Fields\Concerns\TransformsToVersion;
use Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum;
use Tests\Fixtures\Support\Money\Money;
use Tests\Fixtures\Support\Money\Versions\V1\Money as V1Money;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Users\Status;
use Tests\Fixtures\Support\Users\Versions;
use Tests\TestCase;

#[CoversClass(Represents::class)]
#[CoversTrait(AsVersionedEnum::class)]
#[CoversTrait(TransformsToVersion::class)]
class VersionableTest extends TestCase
{
    #[Test]
    public function it_resolves_the_target_for_each_version(): void
    {
        $this->assertSame(Versions\V1\Status::Pending, Status::Finalized->toVersion(ApiVersion::V1));
        $this->assertSame(Versions\V2\Status::Open, Status::Finalized->toVersion(ApiVersion::V2));
    }

    #[Test]
    public function many_source_cases_map_to_one_target_case(): void
    {
        $this->assertSame(Versions\V1\Status::Pending, Versions\V1\Status::representing(Status::Draft));
        $this->assertSame(Versions\V1\Status::Pending, Versions\V1\Status::representing(Status::Open));
        $this->assertSame(Versions\V1\Status::Pending, Versions\V1\Status::representing(Status::Finalized));
    }

    #[Test]
    public function it_throws_when_no_target_is_declared_for_the_version(): void
    {
        $this->expectException(NotFound::class);

        Status::Draft->toVersion(ApiVersion::V3);
    }

    #[Test]
    public function it_throws_when_no_case_represents_the_value(): void
    {
        $this->expectException(Unrepresented::class);

        Versions\V1\Status::representing(new Money(100));
    }

    #[Test]
    public function it_resolves_a_non_enum_target(): void
    {
        $this->assertInstanceOf(V1Money::class, new Money(150)->toVersion(ApiVersion::V1));
        $this->assertSame('1.5', json_encode(new Money(150)->toVersion(ApiVersion::V1)));
    }
}
