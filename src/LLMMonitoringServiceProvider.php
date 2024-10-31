<?php

namespace Borah\LLMMonitoring;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Borah\LLMMonitoring\Commands\LLMMonitoringCommand;

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
            ->hasViews()
            ->hasMigration('create_llm_monitoring_laravel_table')
            ->hasCommand(LLMMonitoringCommand::class);
    }
}
