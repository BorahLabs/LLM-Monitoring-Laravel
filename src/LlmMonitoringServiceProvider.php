<?php

namespace Borah\LlmMonitoring;

use Borah\LlmMonitoring\Commands\Install;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LlmMonitoringServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('llm-monitoring')
            ->hasConfigFile()
            ->hasCommands(Install::class)
            ->hasViews('llm-monitoring')
            ->hasMigrations('create_llm_port_calls_table', 'create_llm_call_evaluations_table');
    }

    public function packageBooted(): void
    {
        // Register the main monitoring service
        $this->app->singleton(LlmMonitoring::class, function ($app) {
            return new LlmMonitoring();
        });
        
        $this->app->alias(LlmMonitoring::class, 'llm-monitoring');
    }
}