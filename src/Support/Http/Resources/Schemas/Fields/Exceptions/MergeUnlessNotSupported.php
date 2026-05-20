<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Exceptions;

use Illuminate\Support\Stringable;
use LogicException;
use Support\Http\Resources\Schemas\Contracts\Schema;

class MergeUnlessNotSupported extends LogicException
{
    private Stringable $template { get => str('`mergeUnless()` is not supported in [%s] resources.'); }

    public function __construct()
    {
        parent::__construct(
            $this->template->replaceArray('%s', [Schema::class])->toString()
        );
    }
}
