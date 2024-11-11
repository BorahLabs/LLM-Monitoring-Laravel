<?php

namespace Borah\LlmMonitoring\Tests;

use Borah\LlmMonitoring\LlmMonitoringServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Borah\\LlmMonitoring\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );


        config([
            'llmport.default' => 'openai',
            'llmport.drivers.openai.default_model' => 'gpt-4o-mini',
            'llmport.drivers.openai.key' => env('OPENAI_API_KEY'),
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            LlmMonitoringServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_llm-monitoring-laravel_table.php.stub';
        $migration->up();
        */
    }
}
