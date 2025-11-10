<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Reflection;

use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use ReflectionProperty;

/**
 * @mixin ReflectionProperty
 */
final class Property
{
    public readonly ReflectionProperty $reflection;

    public Stringable $name {
        get => $this->name ??= Str::of($this->reflection->getName());
    }

    public bool $isPublic {
        get => $this->isPublic ??= $this->reflection->isPublic();
    }

    public function __construct(ReflectionProperty $reflection)
    {
        $this->reflection = $reflection;
    }

    public static function make(ReflectionProperty $reflection): self
    {
        return new self($reflection);
    }

    public function isOn(string $class): bool
    {
        return $this->reflection->getDeclaringClass()->getName() === $class;
    }

    /**
     * @param  array<array-key, mixed>  $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        throw_unless(
            method_exists($this->reflection, $method),
            BadMethodCallException::class,
            "Method {$method} does not exist on ".self::class
        );

        $result = $this->reflection->{$method}(...$arguments);

        return $result instanceof ReflectionProperty ? $this : $result;
    }
}
