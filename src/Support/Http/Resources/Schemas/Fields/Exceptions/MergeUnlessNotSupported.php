<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Exceptions;

use LogicException;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Throwable;

class MergeUnlessNotSupported extends LogicException
{
    public function __construct(string $message = '', int $code = 0, null|Throwable $previous = null)
    {
        $message = with(Schema::class, fn (string $class) => "`mergeUnless()` is not supported in [$class] resources.");

        parent::__construct($message, $code, $previous);
    }
}
