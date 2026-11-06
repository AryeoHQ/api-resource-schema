<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\Exceptions;

use Illuminate\Support\Stringable;
use LogicException;
use Support\Http\Resources\Schemas\Fields\Contracts\Versioned;

class Invalid extends LogicException
{
    private Stringable $template { get => str('[%s] must implement [%s].'); }

    public function __construct(string $class)
    {
        parent::__construct(
            $this->template->replaceArray('%s', [$class, Versioned::class])->toString()
        );
    }
}
