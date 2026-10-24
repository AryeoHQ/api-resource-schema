<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use InvalidArgumentException;
use Stringable;
use Support\Database\Eloquent\Concurrency\ConcurrencyRevision;
use Support\Database\Eloquent\Concurrency\Contracts\NamesConcurrencyRevision;
use Support\Database\Eloquent\Concurrency\Contracts\Revisioned;
use Support\Http\Resources\Schemas\Contracts\Version;

final readonly class EntityTag implements Stringable
{
    private const string ALGORITHM = 'xxh128';

    private const string DIGEST = '/^[0-9a-f]{32}$/';

    private const string REVISION = '/^(0|[1-9][0-9]*)$/';

    private const string TOKEN = '/^[\x21\x23-\x2B\x2D\x2F-\x3C\x3E-\x7E]+$/';

    /**
     * @param  array<string, int>  $revisions
     */
    private function __construct(
        public string $api,
        public array $revisions,
        public null|string $digest,
    ) {
        throw_unless(
            self::isWellFormed($api, $revisions, $digest),
            InvalidArgumentException::class,
            sprintf('An entity tag for API version [%s] with revisions [%s] is malformed.', $api, json_encode($revisions)),
        );
    }

    public static function forRepresentation(Revisioned $resource, Version $api, string $body): null|self
    {
        $revisions = $resource->concurrencyRevisions();

        if ($revisions === null || preg_match(self::TOKEN, (string) $api->value) !== 1) {
            return null;
        }

        return new self(
            (string) $api->value,
            array_intersect_key($revisions, [ConcurrencyRevision::Self->value => true]) + $revisions,
            hash(self::ALGORITHM, $body),
        );
    }

    public static function forNamedRevision(Revisioned $owner, NamesConcurrencyRevision $revision, Version $api): self
    {
        return new self((string) $api->value, [(string) $revision->value => $owner->concurrencyRevision($revision)], null);
    }

    public static function parse(string $value): null|self
    {
        if (preg_match('/^"([^"]*)"$/', trim($value), $matches) !== 1) {
            return null;
        }

        $segments = explode('.', $matches[1]);
        $api = array_shift($segments);
        $revisions = [];
        $digest = null;

        $own = self::revision($segments[0] ?? '');

        if ($own !== null) {
            array_shift($segments);
            $revisions[ConcurrencyRevision::Self->value] = $own;
            $digest = array_pop($segments);
        }

        foreach ($segments as $segment) {
            [$name, $revision] = array_pad(explode('=', $segment, 2), 2, '');

            $named = self::revision($revision);

            if ($named === null || array_key_exists($name, $revisions)) {
                return null;
            }

            $revisions[$name] = $named;
        }

        return self::isWellFormed($api, $revisions, $digest) ? new self($api, $revisions, $digest) : null;
    }

    /**
     * @param  array<string, int>  $compared
     */
    public static function matchesAny(string $ifMatch, Version $api, array $compared): bool
    {
        throw_if(
            $compared === [],
            InvalidArgumentException::class,
            'Matching an entity tag requires at least one compared revision.',
        );

        if (trim($ifMatch) === '*') {
            return true;
        }

        foreach (explode(',', $ifMatch) as $candidate) {
            if (self::parse($candidate)?->matches($api, $compared) === true) {
                return true;
            }
        }

        return false;
    }

    public function __toString(): string
    {
        $segments = [$this->api];

        foreach ($this->revisions as $name => $revision) {
            $segments[] = $name === ConcurrencyRevision::Self->value ? (string) $revision : $name.'='.$revision;
        }

        if ($this->digest !== null) {
            $segments[] = $this->digest;
        }

        return '"'.implode('.', $segments).'"';
    }

    /**
     * @param  array<string, int>  $compared
     */
    private function matches(Version $api, array $compared): bool
    {
        if ($this->api !== (string) $api->value) {
            return false;
        }

        foreach ($compared as $name => $revision) {
            if (($this->revisions[$name] ?? null) !== $revision) {
                return false;
            }
        }

        return true;
    }

    private static function revision(string $value): null|int
    {
        $revision = (int) $value;

        return preg_match(self::REVISION, $value) === 1 && (string) $revision === $value ? $revision : null;
    }

    /**
     * @param  array<string, int>  $revisions
     */
    private static function isWellFormed(string $api, array $revisions, null|string $digest): bool
    {
        if ($revisions === [] || preg_match(self::TOKEN, $api) !== 1) {
            return false;
        }

        foreach ($revisions as $name => $revision) {
            if (preg_match(self::TOKEN, (string) $name) !== 1 || $revision < 0) {
                return false;
            }
        }

        if ($digest === null) {
            return ! array_key_exists(ConcurrencyRevision::Self->value, $revisions);
        }

        return array_key_first($revisions) === ConcurrencyRevision::Self->value && preg_match(self::DIGEST, $digest) === 1;
    }
}
