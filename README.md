# LLM Monitoring for Laravel

Advanced LLM monitoring using [LLM Port](https://github.com/BorahLabs/LLM-Port-Laravel) and Filament

## Installation

You can install the package via composer:

```bash
composer require borah/llm-monitoring-laravel
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="llm-monitoring-laravel-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="llm-monitoring-laravel-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="llm-monitoring-laravel-views"
```

## Usage

```php
$lLMMonitoring = new Borah\LLMMonitoring();
echo $lLMMonitoring->echoPhrase('Hello, Borah!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Borah](https://github.com/Borah)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
