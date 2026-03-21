<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Providers;

use Illuminate\Support\ServiceProvider;
use Support\Http\Resources\Schemas\Console\Commands\MakeResource\MakeResource;

class Provider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeResource::class,
            ]);
        }
    }
}
