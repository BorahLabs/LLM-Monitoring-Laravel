<?php

namespace Borah\LLMMonitoring\Commands;

use Illuminate\Console\Command;

class LLMMonitoringCommand extends Command
{
    public $signature = 'llm-monitoring-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
