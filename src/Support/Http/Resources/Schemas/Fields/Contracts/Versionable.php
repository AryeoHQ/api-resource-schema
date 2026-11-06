<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Contracts;

use Support\Http\Resources\Schemas\Contracts\Version;

interface Versionable
{
    public function toVersion(Version $version): Versioned;
}
