<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Support\Concerns\InterleavesWrites;
use Tests\TestCase;

#[CoversClass(Snapshot::class)]
class SnapshotTest extends TestCase
{
    use InterleavesWrites;

    #[Test]
    public function its_reads_see_one_snapshot_and_it_returns_what_they_return(): void
    {
        Post::factory()->count(2)->create();
        $this->afterTheFirstRead(fn (Connection $writer) => $writer->table('posts')->delete());

        $counts = Snapshot::read(fn (): array => [Post::query()->count(), Post::query()->count()]);

        $this->assertSame([2, 2], $counts);
        $this->assertSame(0, Post::query()->count());
    }

    #[Test]
    public function its_reads_on_the_connection_it_is_given_see_one_snapshot(): void
    {
        Post::factory()->count(2)->create();
        $this->afterTheFirstRead(fn (Connection $writer) => $writer->table('posts')->delete());
        $owner = (new Post)->setConnection('secondary');

        $counts = Snapshot::read(
            fn (): array => [$owner->newQuery()->count(), $owner->newQuery()->count()],
            $owner->getConnection(),
        );

        $this->assertSame([2, 2], $counts);
        $this->assertSame(0, Post::query()->count());
    }

    #[Test]
    public function a_transaction_on_another_connection_does_not_block_it(): void
    {
        Post::factory()->count(2)->create();

        $count = DB::transaction(fn (): int => Snapshot::read(
            fn (): int => DB::connection('secondary')->table('posts')->count(),
            'secondary',
        ));

        $this->assertSame(2, $count);
    }

    #[Test]
    public function it_refuses_to_read_inside_a_transaction_on_the_connection_it_is_given(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('the [secondary] connection is already in one');

        DB::connection('secondary')->transaction(fn (): null => Snapshot::read(fn (): null => null, 'secondary'));
    }

    #[Test]
    public function its_reads_cannot_write(): void
    {
        Post::factory()->create();

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('read-only transaction');

        Snapshot::read(fn (): int => DB::table('posts')->delete());
    }

    #[Test]
    public function it_refuses_to_read_inside_a_transaction(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('the [pgsql] connection is already in one');

        DB::transaction(fn (): null => Snapshot::read(fn (): null => null));
    }
}
