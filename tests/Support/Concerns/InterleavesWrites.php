<?php

declare(strict_types=1);

namespace Tests\Support\Concerns;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

trait InterleavesWrites
{
    /** @var list<string> */
    protected array $connectionsToTransact = [];

    protected function afterRefreshingDatabase(): void
    {
        foreach (['writer', 'secondary'] as $name) {
            DB::purge($name);
            config()->set('database.connections.'.$name, config('database.connections.'.DB::getDefaultConnection()));
        }

        $this->beforeApplicationDestroyed(function (): void {
            DB::statement('truncate users, posts, teams, team_user restart identity');
        });
    }

    protected function afterTheFirstRead(Closure $write): void
    {
        $written = false;
        DB::listen(function (QueryExecuted $query) use (&$written, $write): void {
            if (! $written && $query->connectionName !== 'writer' && stripos($query->sql, 'select') === 0) {
                $written = true;
                $write(DB::connection('writer'));
            }
        });
    }
}
