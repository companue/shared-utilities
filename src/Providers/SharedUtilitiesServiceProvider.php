<?php

namespace Companue\SharedUtilities\Providers;

use Illuminate\Support\ServiceProvider;

class SharedUtilitiesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/shared-utilities.php', 'shared-utilities');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/shared-utilities.php' => config_path('shared-utilities.php'),
            ], 'config');
        }
    }
}
