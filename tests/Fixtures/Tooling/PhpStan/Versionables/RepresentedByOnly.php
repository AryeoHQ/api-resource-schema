<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;

#[RepresentedBy(ValidTarget::class)]
class RepresentedByOnly {}
