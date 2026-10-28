<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use InvalidArgumentException;
use LogicException;
use Stringable;
use Support\Database\Eloquent\Concurrency\Contracts\NamesConcurrencyRevision;
use Support\Database\Eloquent\Concurrency\Contracts\Revisioned;
use Support\Database\Eloquent\Concurrency\Revisions;
use Support\Http\Resources\Schemas\Contracts\Version;

final readonly class EntityTag implements Stringable
{
    private const string ALGORITHM = 'xxh128';

    private const string DIGEST = '/^[0-9a-f]{32}$/D';

    private const string REVISION = '/^(0|[1-9][0-9]*)$/D';

    private const string TOKEN = '/^[\x21\x23-\x2B\x2D\x2F-\x3C\x3E-\x7E]+$/D';

    private function __construct(
        public string $api,
        public Revisions $revisions,
        public null|string $digest,
    ) {
        throw_unless(
            self::isWellFormed($api, $revisions, $digest),
            InvalidArgumentException::class,
            sprintf('An entity tag for API version [%s] is malformed.', $api),
        );
    }

    public static function forRepresentation(Revisioned $resource, Version $api, string $body): null|self
    {
        $revisions = $resource->concurrencyRevisions();

        if ($revisions === null || preg_match(self::TOKEN, (string) $api->value) !== 1) {
            return null;
        }

        return new self((string) $api->value, $revisions, hash(self::ALGORITHM, $body));
    }

    public static function forNamedRevision(Revisioned $owner, NamesConcurrencyRevision $revision, Version $api): self
    {
        $value = $owner->concurrencyRevisions()?->get($revision) ?? throw new LogicException(
            sprintf('The [%s] concurrency revision of [%s] is not loaded.', $revision->value, $owner::class),
        );

        return new self((string) $api->value, Revisions::of(null, [(string) $revision->value => $value]), null);
    }

    public static function parse(string $value): null|self
    {
        if (preg_match('/^"([^"]*)"$/', trim($value), $matches) !== 1) {
            return null;
        }

        $segments = explode('.', $matches[1]);
        $api = array_shift($segments);
        $named = [];
        $digest = null;

        $own = self::revision($segments[0] ?? '');

        if ($own !== null) {
            array_shift($segments);
            $digest = array_pop($segments);
        }

        foreach ($segments as $segment) {
            [$name, $revision] = array_pad(explode('=', $segment, 2), 2, '');

            $parsed = self::revision($revision);

            if ($parsed === null || array_key_exists($name, $named)) {
                return null;
            }

            $named[$name] = $parsed;
        }

        try {
            $revisions = Revisions::of($own, $named);
        } catch (InvalidArgumentException) {
            return null;
        }

        return self::isWellFormed($api, $revisions, $digest) ? new self($api, $revisions, $digest) : null;
    }

    public function __toString(): string
    {
        $segments = [$this->api];

        if ($this->revisions->own !== null) {
            $segments[] = (string) $this->revisions->own;
        }

        foreach ($this->revisions->named as $name => $revision) {
            $segments[] = $name.'='.$revision;
        }

        if ($this->digest !== null) {
            $segments[] = $this->digest;
        }

        return '"'.implode('.', $segments).'"';
    }

    private static function revision(string $value): null|int
    {
        $revision = (int) $value;

        return preg_match(self::REVISION, $value) === 1 && (string) $revision === $value ? $revision : null;
    }

    private static function isWellFormed(string $api, Revisions $revisions, null|string $digest): bool
    {
        if (preg_match(self::TOKEN, $api) !== 1) {
            return false;
        }

        return $digest === null
            ? $revisions->own === null
            : $revisions->own !== null && preg_match(self::DIGEST, $digest) === 1;
    }
}
