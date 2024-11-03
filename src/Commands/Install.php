<?php

namespace Borah\LlmMonitoring\Commands;

use Borah\LlmMonitoring\LlmMonitoringServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

class Install extends Command
{
    protected $signature = 'llm-monitoring:install';

    public function handle(): int
    {
        $this->call('vendor:publish', ['--provider' => LlmMonitoringServiceProvider::class]);

        if (confirm('Do you want to run the migrations?', default: true)) {
            $this->call('migrate');
        }

        $modelsPath = app_path('Models');
        $this->info('Copying LlmPortCall model to ' . $modelsPath);
        File::copy(__DIR__ . '/../Models/LlmPortCall.php.stub', $modelsPath . '/LlmPortCall.php');

        $listenerPath = app_path('Listeners/LlmPort');
        $this->info('Creating LlmPort listener directory at ' . $listenerPath);
        File::makeDirectory($listenerPath, recursive: true, force: true);

        if (confirm('Do you want to create a monitoring Filament panel?', default: true)) {
            $this->addFilamentPanel();
        }

        return self::SUCCESS;
    }

    protected function addFilamentPanel(): void
    {
        $panels = Filament::getPanels();

        $allPanels = collect($panels);

        $panelId = select(
            'Which Filament panel do you want to use?',
            $allPanels
                ->mapWithKeys(fn (\Filament\Panel $panel) => [ $panel->getId() => $panel->getId() ])
                ->all()
        );

        $panel = Filament::getPanel($panelId, isStrict: true);
        $panelClassName = str($panel->getId())->title();
        if (File::exists(app_path('Filament/'.$panelClassName))) {
            $namespace = 'App\Filament\\'.$panelClassName;
            $path = app_path('Filament/'.$panelClassName);
        } else {
            $namespace = 'App\Filament';
            $path = app_path('Filament');
        }

        // Copy widgets, resources and pages
        $files[__DIR__.'/../Filament/Widgets/LlmCallsChart.php.stub'] = app_path('Filament').'/LlmMonitoring/Widgets/LlmCallsChart.php';
        $files[__DIR__.'/../Filament/Widgets/LlmStats.php.stub'] = app_path('Filament').'/LlmMonitoring/Widgets/LlmStats.php';
        $files[__DIR__.'/../Filament/Widgets/LlmTokenConsumption.php.stub'] = app_path('Filament').'/LlmMonitoring/Widgets/LlmTokenConsumption.php';
        $files[__DIR__.'/../Filament/Resources/LlmPortCallResource.php.stub'] = $path.'/Resources/LlmPortCallResource.php';
        $files[__DIR__.'/../Filament/Resources/LlmPortCallResource/Pages/ManageLlmPortCalls.php.stub'] = $path.'/Resources/LlmPortCallResource/Pages/ManageLlmPortCalls.php';
        $files[__DIR__.'/../Filament/Pages/LlmDashboard.php.stub'] = $path.'/Pages/LlmDashboard.php';

        foreach ($files as $stub => $target) {
            $contents = File::get($stub);
            $contents = str_replace('{namespace}', $namespace, $contents);
            File::makeDirectory(dirname($target), recursive: true, force: true);
            File::put($target, $contents);
        }

        $this->comment('We have added the LlmDashboard page at '.str($path)->after(base_path('')).'/Pages/LlmDashboard.php . Make sure to register it in your Filament panel.');
        $this->comment('Make sure to add `->discoverWidgets(in: app_path(\'Filament/LlmMonitoring/Widgets\'), for: \'App\\Filament\\LlmMonitoring\\Widgets\')` to your Filament panel configuration.');
    }
}
