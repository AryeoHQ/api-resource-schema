<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\Version\Exceptions;

use BackedEnum;
use Illuminate\Support\Stringable;
use LogicException;

class NotFound extends LogicException
{
    private Stringable $template { get => str('No schema is defined for version [%s::%s] on [%s].'); }

    public function __construct(BackedEnum $version, string $target)
    {
        parent::__construct(
            $this->template->replaceArray('%s', [$version::class, $version->name, $target])->toString()
        );
    }
}
