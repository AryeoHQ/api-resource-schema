<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Contracts;

use BackedEnum;

interface VersionedEnum extends BackedEnum, Versioned {}
