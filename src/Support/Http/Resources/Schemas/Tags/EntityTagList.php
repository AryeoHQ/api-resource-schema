<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use Support\Database\Eloquent\Concurrency\Revisions;
use Support\Http\Resources\Schemas\Contracts\Version;

final readonly class EntityTagList
{
    private const string TAG = '(?:W\/)?"[\x21\x23-\x7E\x80-\xFF]*+"';

    private const string FIELD = '/^[ \t]*+(?:'.self::TAG.')?[ \t]*+(?:,[ \t]*+(?:'.self::TAG.')?[ \t]*+)*$/D';

    private const string WEAK = '/^W\//';

    /**
     * @param  list<string>  $tags
     */
    private function __construct(public bool $any, public array $tags) {}

    /**
     * @param  list<null|string>  $lines
     */
    public static function parse(array $lines): null|self
    {
        $field = implode(', ', $lines);

        if (trim($field, " \t") === '*') {
            return new self(true, []);
        }

        if (preg_match(self::FIELD, $field) !== 1) {
            return null;
        }

        preg_match_all('/'.self::TAG.'/', $field, $tags);

        return new self(false, $tags[0]);
    }

    public function matchesRevisions(Version $api, Revisions $compared): bool
    {
        if ($this->any) {
            return true;
        }

        foreach ($this->tags as $tag) {
            $parsed = EntityTag::parse($tag);

            if ($parsed !== null && $parsed->api === (string) $api->value && self::contains($parsed->revisions, $compared)) {
                return true;
            }
        }

        return false;
    }

    public function matchesWeakly(string $etag): bool
    {
        return $this->any || in_array(self::opaque($etag), array_map(self::opaque(...), $this->tags), true);
    }

    private static function contains(Revisions $tagged, Revisions $compared): bool
    {
        if ($compared->own !== null && $tagged->own !== $compared->own) {
            return false;
        }

        foreach ($compared->named as $name => $revision) {
            if (($tagged->named[$name] ?? null) !== $revision) {
                return false;
            }
        }

        return true;
    }

    private static function opaque(string $tag): string
    {
        return (string) preg_replace(self::WEAK, '', trim($tag));
    }
}
