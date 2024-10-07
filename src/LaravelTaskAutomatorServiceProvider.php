<?php

namespace JiordiViera\LaravelTaskAutomator;

use Illuminate\Support\ServiceProvider;

class LaravelTaskAutomatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->commands([
            Commands\MakeCrudCommand::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/Commands/stubs' => base_path('stubs/vendor/laravel-task-automator'),
        ], 'stubs');
    }
}