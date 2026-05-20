<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\Collects\Exceptions;

use Illuminate\Support\Stringable;
use LogicException;
use Support\Http\Resources\Schemas\Contracts\Schema;

class Invalid extends LogicException
{
    private Stringable $template { get => str('[%s] must implement [%s].'); }

    public function __construct(string $class)
    {
        parent::__construct(
            $this->template->replaceArray('%s', [$class, Schema::class])->toString()
        );
    }
}
