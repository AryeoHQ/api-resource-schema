<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\Exceptions;

use Illuminate\Support\Stringable;
use LogicException;
use Support\Http\Resources\Schemas\Attributes\CollectedBy;

class CollectedByNotDefined extends LogicException
{
    private Stringable $template { get => str('[%s] is missing the [%s] attribute.'); }

    public function __construct(string $schemaCollection)
    {
        parent::__construct(
            $this->template->replaceArray('%s', [$schemaCollection, CollectedBy::class])->toString()
        );
    }
}
