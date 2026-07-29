<?php

namespace Modules\Shared\Providers;

use Illuminate\Support\ServiceProvider;

class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/shared.php', 'shared');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../Config/shared.php' => config_path('shared.php'),
        ], 'shared-config');
    }
}
