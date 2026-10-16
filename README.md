# API Resource Schema

A light, typed layer on top of Laravel API Resources that lets you describe your JSON contract with native PHP accessor properties instead of large array structures.

## Benefits
Laravel's Resources are powerful, but big `toArray()` methods grow messy and untyped. This package keeps the same familiar capabilities (include, whenLoaded, counts, aggregates, pivots, request flags) while giving you the following. A field that depends on a loaded relation or on the caller must also declare how the schema's ETag covers it; see [ETags](#etags).

- Strong typing per field (IDE + static analysis)
- A simple sentinel type (`Discarded`) for conditional fields
- Clear, discoverable contracts: each public property is a documented field
- First-class versioning: multiple schema versions per model, resolved explicitly
- Less array noise, more intent

## Installation

```bash
composer require aryeo/api-resource-schema
```

## Versioning

Every schema must declare its version via a backed enum implementing the `Version` contract:

```php

namespace App\Http\Api;

enum Version: string implements \Support\Http\Resources\Schemas\Contracts\Version
{
    case V1 = 'v1';
    case V2 = 'v2';
}
```

Publish the configuration file and set the `version` key to your enum FQCN:

```bash
php artisan vendor:publish --tag=api-resource-schema:config
```

```php
// config/api-resource-schema.php
'version' => \App\Http\Api\Version::class,
```

The `#[Version]` attribute on each schema class ties it to a specific enum case:

```php
use Support\Http\Resources\Schemas\Attributes\Version\Version;

#[Version(ApiVersion::V2)]
class UserSchema extends JsonResource implements Schema { /* ... */ }
```

## Schemas

A schema is a `JsonResource` implementing `Schema` with the `AsSchema` trait. Each schema declares its version and its parent collection:

```php
use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Fields\Discarded;

#[Version(ApiVersion::V2)]
#[CollectedBy(UserSchemaCollection::class)]
class UserSchema extends JsonResource implements Schemas\Contracts\Schema
{
    use Schemas\Provides\AsSchema;

    public string $firstName { get => $this->resource->first_name; }

    public string $lastName { get => $this->resource->last_name; }

    public string|Discarded $email {
        get => $this->when(request()->boolean('with_email'), fn () => $this->resource->email);
    }
}
```

Each schema exposes a `$schemaVersion` property automatically resolved from the `#[Version]` attribute.

## Schema Collections

Create a `SchemaCollection` that extends `ResourceCollection` to customize wrapping or meta. Each collection declares which schema it collects:

```php
use Illuminate\Http\Resources\Json\ResourceCollection;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;

#[Collects(UserSchema::class)]
class UserSchemaCollection extends ResourceCollection implements SchemaCollection
{
    use AsSchemaCollection;
}
```

The `$schemaVersion` property is derived automatically from the collected schema's version.

## Models

Models implement the `Schemable` contract and use the `TransformsToSchema` trait. The `#[UseSchema]` attribute (repeatable) declares which schema versions are available:

```php
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Model;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;

#[UseSchema(Schemas\V1\User::class)]
#[UseSchema(Schemas\V2\User::class)]
#[CollectedBy(Users::class)]
class User extends Model implements Schemable
{
    use TransformsToSchema;
}
```

This gives your models:

```php
$user->schemaVersions;          // Collection [ApiVersion::V2, ApiVersion::V1]
$user->toSchema(ApiVersion::V2); // V2 UserSchema instance
$user->toSchema(ApiVersion::V1); // V1 UserSchema instance
```

Version priority follows attribute declaration order (first declared = first listed).

## Eloquent Collections

Custom Eloquent collections implement the `SchemableCollection` contract and use the `TransformsToSchemaCollection` trait. The `#[UseSchemaCollection]` attribute (repeatable) declares available schema collection versions:

```php
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;

#[UseSchemaCollection(Schemas\V1\Users::class)]
#[UseSchemaCollection(Schemas\V2\Users::class)]
class Users extends EloquentCollection implements SchemableCollection
{
    use TransformsToSchemaCollection;
}
```

This gives your collections:

```php
$users->schemaVersions;                              // Collection [ApiVersion::V2, ApiVersion::V1]
$users->toSchemaCollection(ApiVersion::V2);           // V2 UserSchemaCollection
User::paginate(15)->toSchemaCollection(ApiVersion::V2); // Pagination metadata preserved
```

## Controller Usage

```php
return $user->toSchema(ApiVersion::V2);
return $users->toSchemaCollection(ApiVersion::V2);
return User::paginate(15)->toSchemaCollection(ApiVersion::V2);
```

## Generator

The `make:resource` command scaffolds a Schema and SchemaCollection pair:

```bash
php artisan make:resource UserSchema --namespace=App\Schemas\V1 --schema-version=V1
```

If `--schema-version` is omitted, the command prompts you to choose from the available enum cases.

## Hidden Properties

The `#[Hidden]` attribute excludes internal properties (like `schemaVersion` and `collectedBy`) from the schema's field output:

```php
use Support\Http\Resources\Schemas\Attributes\Hidden\Hidden;

#[Hidden]
public Version $schemaVersion;
```

## ETags

Every schema has a strong ETag. The tag is a hash of the schema rendered in tag mode, plus the contributions the schema declares:

```php
$user->toTag(ApiVersion::V2); // Tag, cast to string: "1b4f0e9851971998e732078544c96b36"
```

The hash covers the schema class, the version, the rendered fields as canonical JSON, and each contribution in declaration order. The tag changes when a rendered field or a contribution changes. It stays the same for two requests that differ only in their flags or their caller.

### Tag mode

Tag mode renders each field as follows:

| Field | In tag mode | What to declare |
|---|---|---|
| `when()` or `unless()`, such as a request flag | renders the value, whatever the condition | nothing |
| `whenLoaded()`, `whenCounted()`, `whenAggregated()`, `whenExistsLoaded()` | discarded | `#[ContributesTag(..., relation: 'posts')]`, naming the relation |
| `whenPivotLoaded()`, `whenPivotLoadedAs()` | discarded | `#[ContributesTag(..., relation: 'team_user')]`, naming the pivot table |
| A field that reads the caller or the request directly, such as `auth()` or `request()->user()` | omitted | `#[Untagged]` |
| Every other field | renders as usual | nothing |

Tag mode ignores a `when()` condition. A field whose condition is model state can therefore appear or disappear without a new tag. Render that state in a field of its own, or contribute it.

### Contributors

A contributor returns a string for state that the render does not include, such as a relation's version or a child order. The container resolves it, and `#[ContributesTag]` is repeatable:

```php
use Illuminate\Database\Eloquent\Model;
use Support\Http\Resources\Schemas\Attributes\ContributesTag\ContributesTag;
use Support\Http\Resources\Schemas\Attributes\Untagged\Untagged;
use Support\Http\Resources\Schemas\Tags\Contracts\Contributor;

/** @implements Contributor<User> */
class PostsVersion implements Contributor
{
    public function contribute(Model $resource): string
    {
        return (string) $resource->posts()->max('updated_at');
    }
}

#[CollectedBy(UserSchemaCollection::class)]
#[ContributesTag(PostsVersion::class, relation: 'posts')]
#[Version(ApiVersion::V2)]
class UserSchema extends JsonResource implements Schema
{
    use AsSchema;

    public ResourceCollection|Discarded $posts {
        get => $this->whenLoaded('posts', fn () => PostSchemaCollection::make($this->resource->posts));
    }

    #[Untagged]
    public bool $canEdit { get => request()->user()?->can('update', $this->resource) ?? false; }
}
```

The `relation` argument is optional. Omit it for state that no field loads, such as a child order.

### Responses

A single schema response sets `ETag` to the model's `toTag()` for the schema's version. A schema collection sets `ETag` only when you give it a tag, such as the tag of a collection's order:

```php
return $users->toSchemaCollection(ApiVersion::V2)->withETag($orderTag);
```

### If-Match

`Tag::matches()` evaluates an `If-Match` value with strong comparison. It matches `*` and any listed strong tag. It never matches a weak `W/` tag or a malformed list:

```php
$user->toTag(ApiVersion::V2)->matches($request->header('If-Match', ''));
```

## Static Analysis

This package ships PHPStan rules and Rector auto-fixes to enforce correct wiring at analysis time.

The `ConditionalFieldMustBeCoveredByTag` rule fails a load-dependent field whose relation has no `#[ContributesTag]`. It also fails a field that reads `auth()`, `Auth::`, or `->user()` without `#[Untagged]`. Pass each relation as a string literal, so that the rule can match it.
