<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\CollectedBy\Exceptions;

use Illuminate\Support\Stringable;
use LogicException;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;

class NotDefined extends LogicException
{
    private Stringable $template { get => str('[%s] does not define #[%s].'); }

    public function __construct(string $schemaCollection)
    {
        parent::__construct(
            $this->template->replaceArray('%s', [$schemaCollection, CollectedBy::class])->toString()
        );
    }
}
