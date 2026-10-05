# Installation

Install `laranail/fluent-http-ajaxify-laravel` and publish its assets. See the
[Documentation index](../README.md#documentation).

## Requirements

- PHP `^8.4.1 || ^8.5`
- Laravel `^13.0`

## Install

The package is not on Packagist. Add its VCS repository to your application's `composer.json`, and
keep Packagist from answering for `laranail/*` names, so a stale or squatted copy there can never win:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/laranail/fluent-http-ajaxify-laravel" },
    { "type": "composer", "url": "https://repo.packagist.org", "exclude": ["laranail/*"] },
    { "packagist.org": false }
]
```

Then require it:

```bash
composer require laranail/fluent-http-ajaxify-laravel:^0.1
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

## The JavaScript client

The browser side is [`laranail/fluent-http-ajaxify-js`](https://github.com/laranail/fluent-http-ajaxify-js).
This package ships its built files in `resources/js` — `FluentHttpAjaxify.js`, `FluentToast.js` and
`FluentHttpWrapper.js` — and the assets tag above publishes them with the bundled Axios fallback. The
first line of each file names the source commit, for example:

```js
// Synced from laranail/fluent-http-ajaxify-js@<commit> (v3.0.0) by bin/sync-client -- edit the source repository, not this file.
```

Maintainers refresh them with `bin/sync-client` (or `composer sync-client`) in a clone of this
repository, pointed at a checkout of the client repository; it defaults to `../fluent-http-ajaxify-js`
and refuses a checkout with uncommitted changes under `assets/js`. The script is not in the dist
archive.

After upgrading the package, re-publish with `--force`, because `vendor:publish` never overwrites a
file that is already there:

```bash
php artisan laranail::fluent-http-ajaxify.publish --force
```

> Installs made before the client was shipped published a 232-byte placeholder as
> `public/vendor/fluent-http/FluentHttpAjaxify.js`, which does nothing. Re-publishing with `--force`
> replaces it.

## Next steps

- [Getting started](getting-started.md) — your first dual-mode response.
- [Configuration](configuration.md) — every config key.

---

[← Docs index](../README.md#documentation)
