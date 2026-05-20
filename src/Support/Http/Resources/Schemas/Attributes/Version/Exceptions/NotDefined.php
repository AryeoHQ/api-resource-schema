<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\Version\Exceptions;

use Illuminate\Support\Stringable;
use LogicException;
use Support\Http\Resources\Schemas\Attributes\Version\Version;

class NotDefined extends LogicException
{
    private Stringable $template { get => str('The [%s] class must define #[%s].'); }

    public function __construct(string $schema)
    {
        parent::__construct(
            $this->template->replaceArray('%s', [$schema, Version::class])->toString()
        );
    }
}
