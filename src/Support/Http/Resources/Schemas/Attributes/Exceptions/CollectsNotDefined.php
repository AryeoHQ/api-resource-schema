<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\Exceptions;

use Illuminate\Support\Stringable;
use LogicException;
use Support\Http\Resources\Schemas\Attributes\Collects;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;

class CollectsNotDefined extends LogicException
{
    private Stringable $template { get => str('[%s] is missing the [%s] attribute.'); }

    public function __construct(SchemaCollection $schemaCollection)
    {
        parent::__construct(
            $this->template->replaceArray('%s', [class_basename($schemaCollection), class_basename(Collects::class)])->toString()
        );
    }
}
