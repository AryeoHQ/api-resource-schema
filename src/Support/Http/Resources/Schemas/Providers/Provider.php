<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Providers;

use Illuminate\Support\ServiceProvider;
use Support\Http\Resources\Schemas\Console\Commands\MakeResource\MakeResource;

class Provider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootConfig();
        $this->bootPublishables();
        $this->bootCommands();
    }

    private function bootConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../../../../../config/api-resource-schema.php', 'api-resource-schema'
        );
    }

    private function bootPublishables(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../../../../../config/api-resource-schema.php' => config_path('api-resource-schema.php'),
            ], ['config', 'api-resource-schema', 'api-resource-schema:config']);
        }
    }

    private function bootCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeResource::class,
            ]);
        }
    }
}
