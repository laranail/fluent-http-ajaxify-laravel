# Artisan commands

Install assets and keep the bundled Axios pinned + current. See the
[Documentation index](../../README.md#documentation).

## `ajaxify:install`

Publishes the config, JS assets, and Blade views in one step:

```bash
php artisan ajaxify:install
```

Equivalent to publishing the `ajaxify-config`, `ajaxify-assets`, and `ajaxify-views` tags individually.

## `ajaxify:update-axios`

The Blade component loads Axios from a CDN with a local fallback; both the CDN URL and the SRI integrity
hash are pinned to a version. This command upgrades them safely:

```bash
php artisan ajaxify:update-axios                 # upgrade to the latest release
php artisan ajaxify:update-axios --version=1.14.0 # pin a specific version
php artisan ajaxify:update-axios --publish        # upgrade + re-publish assets to public/
php artisan ajaxify:update-axios --dry-run        # preview without changing anything
```

It fetches the latest version from the npm registry, downloads the minified build, validates it
(size + content), computes the SHA-384 SRI hash, and updates `axios_version` + `axios_sri` in your config.

Schedule periodic updates in `app/Console/Kernel.php`:

```php
$schedule->command('ajaxify:update-axios --publish')->monthly();
```

---

[← Docs index](../../README.md#documentation)
