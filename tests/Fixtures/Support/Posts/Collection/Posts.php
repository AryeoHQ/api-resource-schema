<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Posts\Collection;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Fixtures\Support\Posts\Schemas\Posts as SchemaCollectionPosts;

/**
 * @extends EloquentCollection<int, Post>
 */
#[UseSchemaCollection(SchemaCollectionPosts::class)]
class Posts extends EloquentCollection implements SchemableCollection
{
    use TransformsToSchemaCollection;
}
