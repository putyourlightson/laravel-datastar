# Testing

## Static Analysis

To run static analysis on the plugin, install [PHPStan for Craft CMS](https://github.com/craftcms/phpstan) and run the following command from the root of your project.

```shell
php vendor/bin/phpstan analyse -c vendor/putyourlightson/laravel-datastar/phpstan.neon  --memory-limit 1G
```

## Easy Coding Standard

To run the Easy Coding Standard on the plugin, install [ECS for Craft CMS](https://github.com/craftcms/ecs) and run the following command from the root of your project.

```shell
php vendor/bin/ecs check -c vendor/putyourlightson/laravel-datastar/ecs.php
```

## Pest Tests

To run Pest tests, first install [Pest PHP](https://pestphp.com/) as a dev dependency.

```shell
composer require pestphp/pest:^3.0.0 --dev
```

Then run the following command from the root of your project.

```shell
php vendor/bin/pest vendor/putyourlightson/laravel-datastar/tests --test-directory=vendor/putyourlightson/laravel-datastar/tests --colors --display-deprecations
```ightson/laravel-datastar/tests --colors --display-deprecations
