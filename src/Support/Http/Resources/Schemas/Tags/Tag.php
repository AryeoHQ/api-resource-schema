<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Stringable;

final class Tag implements Stringable
{
    private const string OPAQUE = '"[\x21\x23-\x7E\x80-\xFF]*"';

    private const string ELEMENT = '(W\/)?('.self::OPAQUE.')';

    private const string LIST = '/^[ \t]*(?:'.self::ELEMENT.')?(?:[ \t]*,[ \t]*(?:'.self::ELEMENT.')?)*[ \t]*$/';

    public readonly string $value;

    public function __construct(string $value)
    {
        throw_unless(
            preg_match('/^'.self::OPAQUE.'$/', $value) === 1,
            InvalidArgumentException::class,
            "[{$value}] is not a strong entity tag."
        );

        $this->value = $value;
    }

    public static function hash(string $bytes): self
    {
        return new self('"'.hash('xxh128', $bytes).'"');
    }

    public function matches(string $ifMatch): bool
    {
        return trim($ifMatch, " \t") === '*' || $this->strongTags($ifMatch)->contains($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return Collection<int, string>
     */
    private function strongTags(string $ifMatch): Collection
    {
        if (preg_match(self::LIST, $ifMatch) !== 1) {
            return collect();
        }

        preg_match_all('/'.self::ELEMENT.'/', $ifMatch, $elements, PREG_SET_ORDER);

        return collect($elements)
            ->reject(fn (array $element): bool => $element[1] === 'W/')
            ->map(fn (array $element): string => $element[2])
            ->values();
    }
}
