<?php

namespace Borah\LlmMonitoring\Commands;

use Borah\LlmMonitoring\LlmMonitoringServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

class Install extends Command
{
    protected $signature = 'llm-monitoring:install';

    public function handle(): int
    {
        $this->call('vendor:publish', ['--provider' => LlmMonitoringServiceProvider::class]);

        $modelsPath = app_path('Models');
        $this->info('Copying LlmPortCall model to '.$modelsPath);
        File::copy(__DIR__.'/../Models/LlmPortCall.php.stub', $modelsPath.'/LlmPortCall.php');

        if (confirm('Do you want to run the migrations?', default: true)) {
            $this->call('migrate');
        }

        $listenerPath = app_path('Listeners/LlmPort');
        $this->info('Creating LlmPort listener directory at '.$listenerPath);
        File::makeDirectory($listenerPath, recursive: true, force: true);
        File::copy(__DIR__.'/../Listeners/CreateLlmPortCall.php.stub', $listenerPath.'/CreateLlmPortCall.php');

        if (confirm('Do you want to create a monitoring Filament panel?', default: true)) {
            $this->addFilamentPanel();
        }

        $this->info('Remember to register the event listener in your EventServiceProvider:');
        $this->newLine();
        $this->line('protected $listen = [');
        $this->line('    \Borah\LlmPort\Events\LlmChatResponseReceived::class => [');
        $this->line('        \App\Listeners\LlmPort\CreateLlmPortCall::class,');
        $this->line('    ],');
        $this->line('];');

        return self::SUCCESS;
    }

    protected function addFilamentPanel(): void
    {
        $panels = Filament::getPanels();

        $allPanels = collect($panels);

        $panelId = select(
            'Which Filament panel do you want to use?',
            $allPanels
                ->mapWithKeys(fn (\Filament\Panel $panel) => [$panel->getId() => $panel->getId()])
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

        // Create directory for LlmMonitoring widgets
        $widgetsPath = app_path('Filament/LlmMonitoring/Widgets');
        File::makeDirectory($widgetsPath, recursive: true, force: true);

        // Copy widgets, resources and pages
        $files[__DIR__.'/../Filament/Widgets/LlmCallsChart.php.stub'] = $widgetsPath.'/LlmCallsChart.php';
        $files[__DIR__.'/../Filament/Widgets/LlmStats.php.stub'] = $widgetsPath.'/LlmStats.php';
        $files[__DIR__.'/../Filament/Widgets/LlmTokenConsumption.php.stub'] = $widgetsPath.'/LlmTokenConsumption.php';
        $files[__DIR__.'/../Filament/Widgets/LlmModelPerformance.php.stub'] = $widgetsPath.'/LlmModelPerformance.php';
        $files[__DIR__.'/../Filament/Resources/LlmPortCallResource.php.stub'] = $path.'/Resources/LlmPortCallResource.php';
        $files[__DIR__.'/../Filament/Resources/LlmPortCallResource/Pages/ManageLlmPortCalls.php.stub'] = $path.'/Resources/LlmPortCallResource/Pages/ManageLlmPortCalls.php';
        $files[__DIR__.'/../Filament/Pages/LlmDashboard.php.stub'] = $path.'/Pages/LlmDashboard.php';

        foreach ($files as $stub => $target) {
            $contents = File::get($stub);
            $contents = str_replace('{namespace}', $namespace, $contents);
            File::makeDirectory(dirname($target), recursive: true, force: true);
            File::put($target, $contents);
        }

        $this->comment('We have added the LlmDashboard page at '.str($path)->after(base_path('')).'/Pages/LlmDashboard.php');
        $this->comment('Make sure to register it in your Filament panel provider with:');
        $this->info('->pages([');
        $this->info('    \App\Filament\Pages\LlmDashboard::class,');
        $this->info('])');
        $this->newLine();
        $this->comment('Make sure to add widget discovery to your Filament panel provider with:');
        $this->info('->discoverWidgets(in: app_path(\'Filament/LlmMonitoring/Widgets\'), for: \'App\\Filament\\LlmMonitoring\\Widgets\')');
        $this->newLine();
        $this->comment('If you want to add your own custom widgets to the LLM dashboard, extend the LlmDashboard class and override the getWidgets() method.');
    }
}
