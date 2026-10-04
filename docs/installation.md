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
php artisan laranail::fluent-http-ajaxify.install
```

This publishes the config, JS assets, and Blade views. Or publish individually:

```bash
php artisan vendor:publish --tag=laranail::fluent-http-ajaxify-config
php artisan vendor:publish --tag=laranail::fluent-http-ajaxify-assets
php artisan vendor:publish --tag=laranail::fluent-http-ajaxify-views
```

The config lands in `config/laranail/fluent-http-ajaxify.php`, the JS in `public/vendor/fluent-http/`
(the `assets_path` config key), and the views in `resources/views/vendor/laranail/fluent-http-ajaxify/`.

See [Artisan commands](tools/commands.md) for `laranail::fluent-http-ajaxify.install`,
`laranail::fluent-http-ajaxify.publish` and `laranail::fluent-http-ajaxify.update-axios`. The bare
`ajaxify:*` names are deprecated aliases of these.

## Next steps

- [Getting started](getting-started.md) — your first dual-mode response.
- [Configuration](configuration.md) — every config key.

---

[← Docs index](../README.md#documentation)
