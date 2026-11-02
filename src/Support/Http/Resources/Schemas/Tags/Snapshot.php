<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use LogicException;

final class Snapshot
{
    /**
     * @template TResult
     *
     * @param  Closure(): TResult  $reads
     * @return TResult
     */
    public static function read(Closure $reads, null|string|Connection $connection = null): mixed
    {
        $connection = $connection instanceof Connection ? $connection : DB::connection($connection);

        throw_if(
            $connection->transactionLevel() > 0,
            LogicException::class,
            sprintf(
                'A snapshot read must open its own transaction, and the [%s] connection is already in one. PostgreSQL accepts [set transaction] only as the first statement of a top-level transaction, so call Snapshot::read() outside every transaction, including a RefreshDatabase test transaction.',
                $connection->getName(),
            ),
        );

        return $connection->transaction(function () use ($connection, $reads): mixed {
            $connection->statement('set transaction isolation level repeatable read, read only');

            return $reads();
        });
    }
}
