# API Resource Schema

A light, typed layer on top of Laravel API Resources that lets you describe your JSON contract with native PHP accessor properties instead of large array structures.

## Benefits
Laravel's Resources are powerful, but big `toArray()` methods grow messy and untyped. This package keeps the same familiar conditional capabilities (include, whenLoaded, counts, aggregates, pivots, request flags) while giving you:

- Strong typing per field (IDE + static analysis)
- A simple sentinel (`Discarded`) to omit fields cleanly
- Clear, discoverable contracts: each public property is a documented field
- Less array noise, more intent

## Installation

```bash
composer require aryeo/api-resource-schema
```

## Basic Example

```php
use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas;
use Support\Http\Resources\Schemas\Attributes\CollectedBy;
use Support\Http\Resources\Schemas\Fields\Discarded;

#[CollectedBy(UserSchemaCollection::class)]
class UserSchema extends JsonResource implements Schemas\Contracts\Schema
{
	use Schemas\Provides\AsSchema;

	public string $firstName { get => $this->resource->first_name; }

	public string $lastName  { get => $this->resource->last_name; }

	public string|Discarded $email {
        get => $this->when(request()->boolean('with_email'), fn() => $this->resource->email);
    }
}
```

Controller usage:

```php
return UserSchema::make($user);
return UserSchema::collection($users);
```



## Collections
Create a `SchemaCollection` that extends `ResourceCollection` if you want to customize wrapping or meta:

```php
use Illuminate\Http\Resources\Json\ResourceCollection;
use Support\Http\Resources\Schemas\Attributes\Collects;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;

#[Collects(UserSchema::class)]
class UserSchemaCollection extends ResourceCollection implements SchemaCollection
{
	use AsSchemaCollection;
}
```
