# LLM Monitoring for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/borah/llm-monitoring-laravel.svg?style=flat-square)](https://packagist.org/packages/borah/llm-monitoring-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/borah/llm-monitoring-laravel.svg?style=flat-square)](https://packagist.org/packages/borah/llm-monitoring-laravel)

A comprehensive monitoring solution for Large Language Model usage in Laravel applications using [LLM Port](https://github.com/BorahLabs/LLM-Port-Laravel) and Filament.

## Features

- Track LLM API calls and usage statistics
- Evaluate LLM responses using built-in metrics
- Monitor token usage and costs
- Integrated Filament dashboard
- Extensible architecture for custom metrics

## Installation

You can install the package via composer:

```bash
composer require borah/llm-monitoring-laravel
```

Then run the installation command:

```bash
php artisan llm-monitoring:install
```

This will:
- Publish the config file
- Run migrations to create necessary tables
- Copy the LlmPortCall model to your app
- Set up Filament resources and dashboard components

## Configuration

After installation, you can configure the package in `config/llm-monitoring.php`:

```php
return [
    'llmport' => [
        'driver' => null, // one of the llmport.php drivers
        'model' => null,
    ],
    'probability' => 100, // 0 to 100. Chance of a response being evaluated. 100 is always.
    'evaluations' => [
        \Borah\LlmMonitoring\Evaluations\AnswerRelevance::class,
        \Borah\LlmMonitoring\Evaluations\ContextRelevanceChainOfThought::class,
    ],
];
```

## Dashboard Setup

During installation, a Filament dashboard will be set up. Make sure to register it in your Filament panel provider:

```php
// in app/Providers/Filament/AdminPanelProvider.php

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->pages([
            \App\Filament\Pages\LlmDashboard::class,
        ])
        ->discoverWidgets(in: app_path('Filament/LlmMonitoring/Widgets'), for: 'App\\Filament\\LlmMonitoring\\Widgets');
}
```

## Adding Custom Widgets

You can extend the dashboard with your own custom widgets. Create a new class that extends the LlmDashboard:

```php
namespace App\Filament\Pages;

use App\Filament\LlmMonitoring\Widgets\LlmCallsChart;
use App\Filament\LlmMonitoring\Widgets\LlmStats;
use App\Filament\LlmMonitoring\Widgets\LlmTokenConsumption;
use App\Filament\Widgets\MyCustomWidget;

class CustomLlmDashboard extends \App\Filament\Pages\LlmDashboard
{
    public function getWidgets(): array
    {
        return [
            LlmStats::class,
            LlmCallsChart::class,
            LlmTokenConsumption::class,
            MyCustomWidget::class,
        ];
    }
}
```

Then update your panel configuration to use your custom dashboard:

```php
->pages([
    \App\Filament\Pages\CustomLlmDashboard::class,
])
```

## Creating Custom Evaluations

You can create custom evaluation metrics by extending the `BaseEvaluation` class:

```php
namespace App\Evaluations;

use Borah\LlmMonitoring\Evaluations\BaseEvaluation;
use Borah\LlmMonitoring\ValueObjects\EvaluationData;
use Borah\LlmMonitoring\ValueObjects\EvaluationResult;
use Borah\LLMPort\ValueObjects\ChatResponse;

class MyCustomEvaluation extends BaseEvaluation
{
    public function identifier(): string
    {
        return 'my-custom-evaluation';
    }

    public function description(): string
    {
        return 'Evaluates something custom about the LLM response';
    }

    public function systemPrompt(EvaluationData $data): string
    {
        return 'You are evaluating the quality of an AI response.';
    }

    public function userPrompt(EvaluationData $data): string
    {
        return "User Query: {$data->query}\n\nAI Response: {$data->response}";
    }

    protected function evaluate(EvaluationData $data, mixed $response): EvaluationResult
    {
        if ($response instanceof ChatResponse) {
            // Process the response and return a result
            return new EvaluationResult(
                value: 0.85,
                formattedValue: '85%',
                metadata: ['details' => 'Additional evaluation details']
            );
        }

        return new EvaluationResult(value: 0, formattedValue: '0%');
    }
}
```

Then add your custom evaluation to the config:

```php
'evaluations' => [
    \Borah\LlmMonitoring\Evaluations\AnswerRelevance::class,
    \Borah\LlmMonitoring\Evaluations\ContextRelevanceChainOfThought::class,
    \App\Evaluations\MyCustomEvaluation::class,
],
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Borah](https://github.com/Borah)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.