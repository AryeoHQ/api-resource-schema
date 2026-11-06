<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Attributes\Represents\Exceptions;

use Illuminate\Support\Stringable;
use LogicException;
use UnitEnum;

class Unrepresented extends LogicException
{
    private Stringable $template { get => str('No case of [%s] represents [%s].'); }

    public function __construct(object $value, string $target)
    {
        $represented = $value instanceof UnitEnum ? $value::class.'::'.$value->name : $value::class;

        parent::__construct(
            $this->template->replaceArray('%s', [$target, $represented])->toString()
        );
    }
}
