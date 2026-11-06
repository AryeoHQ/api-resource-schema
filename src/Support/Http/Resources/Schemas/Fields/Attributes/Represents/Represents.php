<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Attributes\Represents;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
final class Represents
{
    /** @var array<int, UnitEnum> */
    public array $cases;

    public function __construct(UnitEnum ...$cases)
    {
        $this->cases = $cases;
    }
}
