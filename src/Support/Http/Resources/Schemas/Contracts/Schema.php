<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Contracts;

use Illuminate\Http\Request;

/**
 * @method static self make(mixed ...$parameters)
 */
interface Schema
{
    public Version $schemaVersion { get; }

    public string $collectedBy { get; }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(Request $request): array;
}
