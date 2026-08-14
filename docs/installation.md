# Installation

Install `laranail/fluent-http-ajaxify-laravel` and publish its assets. See the
[Documentation index](../README.md#documentation).

## Requirements

- PHP `^8.4.1 || ^8.5`
- Laravel `^13.0`

## Install

```bash
composer require laranail/fluent-http-ajaxify-laravel
```

The service provider and `Ajaxify` facade are auto-discovered.

## Publish assets

```bash
php artisan ajaxify:install
```

This publishes the config, JS assets, and Blade views. Or publish individually:

```bash
php artisan vendor:publish --tag=ajaxify-config
php artisan vendor:publish --tag=ajaxify-assets
php artisan vendor:publish --tag=ajaxify-views
```

See [Artisan commands](tools/commands.md) for `ajaxify:install` + `ajaxify:update-axios`.

## Next steps

- [Getting started](getting-started.md) — your first dual-mode response.
- [Configuration](configuration.md) — every config key.

---

[← Docs index](../README.md#documentation)
