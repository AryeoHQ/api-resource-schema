<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Provides;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Http\Resources\Schemas\Attributes;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Contracts\Version;
use Support\Http\Resources\Schemas\Tags\Tag;

trait AsSchemaCollection
{
    public Version $schemaVersion;

    protected null|Tag $etag = null;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->schemaVersion = $this->schemaVersion();
    }

    public function withETag(Tag $tag): static
    {
        $this->etag = $tag;

        return $this;
    }

    public function withResponse(Request $request, JsonResponse $response): void
    {
        if ($this->etag !== null) {
            $response->setEtag((string) $this->etag);
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
