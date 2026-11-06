<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Contracts;

interface Versioned
{
    public static function representing(Versionable $value): static;
}
