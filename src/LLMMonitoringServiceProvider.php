<?php

namespace Borah\LLMMonitoring;

use Borah\LLMMonitoring\Commands\Install;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LLMMonitoringServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('llm-monitoring-laravel')
            ->hasConfigFile()
            ->hasCommands(Install::class)
            ->hasMigration('create_llm_port_calls_table');
    }
}
