<?php

namespace Borah\LLMMonitoring\Commands;

use Borah\LLMMonitoring\LLMMonitoringServiceProvider;
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
        $this->call('vendor:publish', ['--provider' => LLMMonitoringServiceProvider::class]);

        if (confirm('Do you want to run the migrations?', default: true)) {
            $this->call('migrate');
        }

        $modelsPath = app_path('Models');
        $this->info('Copying LLMPortCall model to '.$modelsPath);
        File::copy(__DIR__.'/../Models/LLMPortCall.php.stub', $modelsPath.'/LLMPortCall.php');

        $listenerPath = app_path('Listeners/LLMPort');
        $this->info('Creating LLMPort listener directory at '.$listenerPath);
        File::makeDirectory($listenerPath, recursive: true, force: true);

        if (confirm('Do you want to create a monitoring Filament panel?', default: true)) {
            $this->addFilamentPanel();
        }

        return self::SUCCESS;
    }

    protected function addFilamentPanel(): void
    {
        $panels = Filament::getPanels();

        $allPanels = isset($panels['llm-monitoring']) ? collect($panels) : collect([...$panels, 'llm-monitoring' => 'llm-monitoring (new)']);

        $panelId = select(
            'Which Filament panel do you want to use?',
            $allPanels
                ->mapWithKeys(fn (\Filament\Panel|string $panel, mixed $key) => is_string($panel) ?
                    [$key => $panel] :
                    [$panel->getId() => $panel->getId()]
                )
                ->all()
        );

        if ($panelId === 'llm-monitoring') {
            $this->call('make:filament-panel', ['id' => 'llm-monitoring']);
        }

        $panel = Filament::getPanel($panelId);
        $resourceDirectories = $panel->getResourceDirectories();
        $resourceNamespaces = $panel->getResourceNamespaces();

        foreach ($resourceDirectories as $resourceIndex => $resourceDirectory) {
            if (str($resourceDirectory)->startsWith(base_path('vendor'))) {
                unset($resourceDirectories[$resourceIndex]);
                unset($resourceNamespaces[$resourceIndex]);
            }
        }

        $namespace = (count($resourceNamespaces) > 1) ?
            select(
                label: 'Which namespace would you like to create this in?',
                options: $resourceNamespaces
            ) :
            (Arr::first($resourceNamespaces) ?? 'App\\Filament\\Resources');

        $path = (count($resourceDirectories) > 1) ?
            $resourceDirectories[array_search($namespace, $resourceNamespaces)] :
            (Arr::first($resourceDirectories) ?? app_path('Filament/Resources/'));

        dd($path, $namespace);
    }
}
