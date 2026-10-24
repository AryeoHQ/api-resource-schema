<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Provides;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Http\Resources\Schemas\Attributes;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Contracts\Version;
use Support\Http\Resources\Schemas\Tags\EntityTag;

trait AsSchemaCollection
{
    public Version $schemaVersion;

    protected null|EntityTag $entityTag = null;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->schemaVersion = $this->schemaVersion();
    }

    public function withTag(EntityTag $tag): static
    {
        $this->entityTag = $tag;

        return $this;
    }

    public function withResponse(Request $request, JsonResponse $response): void
    {
        if ($this->entityTag !== null) {
            $response->setEtag((string) $this->entityTag);
        }
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
