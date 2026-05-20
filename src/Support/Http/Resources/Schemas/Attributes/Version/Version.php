<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\Version;

use Attribute;
use ReflectionClass;
use Support\Http\Resources\Schemas\Contracts;

#[Attribute(Attribute::TARGET_CLASS)]
final class Version
{
    public Contracts\Version $version;

    public function __construct(Contracts\Version $version)
    {
        $this->version = $version;
    }

    /**
     * @param  class-string  $class
     */
    public static function resolve(string $class): Contracts\Version
    {
        $version = data_get((new ReflectionClass($class))->getAttributes(self::class), 0)?->newInstance()?->version;

        throw_unless($version, Exceptions\NotDefined::class, $class);

        return $version;
    }
}
