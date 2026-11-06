<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Represents;

enum RepresentsWithoutAsVersionedEnum: string
{
    #[Represents(ValidSource::Draft)]
    case A = 'a';
}
