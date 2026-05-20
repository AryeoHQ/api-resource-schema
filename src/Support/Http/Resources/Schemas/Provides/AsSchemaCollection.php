<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Provides;

use Support\Http\Resources\Schemas\Attributes;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Contracts\Version;

trait AsSchemaCollection
{
    public Version $schemaVersion;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->schemaVersion = $this->schemaVersion();
    }

    protected function schemaVersion(): Version
    {
        return Attributes\Version\Version::resolve($this->collects);
    }

    protected function collects(): string
    {
        return $this->collects = Collects::resolve(static::class); // @phpstan-ignore assign.propertyType
    }
}
