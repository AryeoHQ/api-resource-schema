# API Resource Schema

A light, typed layer on top of Laravel API Resources that lets you describe your JSON contract with native PHP accessor properties instead of large array structures.

## Benefits
Laravel's Resources are powerful, but big `toArray()` methods grow messy and untyped. This package keeps the same familiar capabilities (include, whenLoaded, counts, aggregates, pivots, request flags) while giving you:

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

## ETags

A schema response carries an `ETag` when its model implements `Revisioned` from [`aryeo/eloquent-concurrency`](https://github.com/AryeoHQ/eloquent-concurrency):

```
ETag: "v2.17.place_assets_order=4.9f2c1a0b7e3d5c4a9f2c1a0b7e3d5c4a"
       │  │  │                    └ digest: xxh128 of the response body
       │  │  └ named revisions: <name>=<revision>
       │  └ own revision: the subject row's concurrency_revision
       └ API version
```

| Part | Changes when | Read by |
|---|---|---|
| API version | The client calls another API version | `If-Match` |
| Own revision | Any column of the subject row changes | `If-Match`, when the target compares the own revision |
| Named revision | Its trigger fires, such as on a move | `If-Match`, when the target compares that name |
| Digest | Any byte of the body changes | `If-None-Match` only |

Named revisions follow the model's declaration order. A schema response gets no `ETag` in these cases:

- The model is not `Revisioned`.
- The model's `concurrencyRevisions()` returns `null`. It does so when the model does not exist, such as before it is saved, and when the model was loaded without every revision column, such as through `select(['id', 'name'])`.
- The API version value is not a valid tag token. A dotted value such as `v2.1` is not. `EntityTag::forNamedRevision()` throws an `InvalidArgumentException` for such a value.

The digest is computed after the body is encoded, so it covers nested schemas too.

A schema collection carries only the tag you give it. A nested index takes the API version and its relation's named revision, with no own revision and no digest, so every page carries the same tag:

```php
use Support\Http\Resources\Schemas\Tags\EntityTag;

return $place->placeAssets()->paginate(15)
    ->toSchemaCollection(ApiVersion::V2)
    ->withTag(EntityTag::forNamedRevision($place, PlaceRevision::PlaceAssetsOrder, ApiVersion::V2));
// ETag: "v2.place_assets_order=4"
```

An `EntityTag` holds the API version, a `Revisions` value from `aryeo/eloquent-concurrency`, and the digest. `EntityTag::parse()` returns `null` for a weak, malformed, or listed tag. It also returns `null` for a named revision whose name is not lowercase letters, digits, and underscores starting with a letter, or is `self`.

`EntityTagList::parse()` reads an `If-Match` or `If-None-Match` field, following RFC 9110. It takes every line of the header and joins them. It returns `null` for a malformed field, so a malformed field matches nothing:

| Field | Result |
|---|---|
| `*` alone | `$any` is `true` |
| A comma-separated list of `"…"` and `W/"…"` tags | `$tags` lists them, `W/` kept |
| Empty | `$tags` is empty, so it matches nothing |
| `*` with a tag, or any malformed element | `null` |

`matchesRevisions()` evaluates `If-Match`. `*` matches. A weak tag never matches. Another tag matches when its API version is the one you give and every revision you name equals the tag's. It ignores the digest and any revision you do not name:

```php
use Support\Database\Eloquent\Concurrency\Revisions;
use Support\Http\Resources\Schemas\Tags\EntityTagList;

EntityTagList::parse($request->headers->all('If-Match'))
    ?->matchesRevisions(ApiVersion::V2, Revisions::of(own: $place->concurrencyRevision()))
    ?? false;
```

`matchesWeakly()` evaluates `If-None-Match` against the response's `ETag`. `*` matches. Another tag matches when it equals the `ETag`, digest included, with any `W/` prefix ignored on both sides.

A schema or schema collection must not define `withResponse()`, because the `AsSchema` and `AsSchemaCollection` traits set the `ETag` there. The `SchemaMustNotDefineWithResponse` PHPStan rule reports one that does.

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

## Static Analysis

This package ships PHPStan rules and Rector auto-fixes to enforce correct wiring at analysis time.
