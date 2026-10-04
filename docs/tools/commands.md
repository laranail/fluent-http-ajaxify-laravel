# Artisan commands

Three commands install the assets and keep the bundled Axios pinned and current: `ajaxify:install`,
`ajaxify:publish` and `ajaxify:update-axios`. See the [Documentation index](../../README.md#documentation).

> These are the names the package registers today. They are not yet vendor-scoped to the family's
> `laranail::fluent-http-ajaxify.<command>` shape; if that changes, it will be a breaking release noted in
> the CHANGELOG.

## `ajaxify:install`

Publishes the config, JS assets, and Blade views in one step:

```bash
php artisan ajaxify:install
php artisan ajaxify:install --force       # overwrite files already published
php artisan ajaxify:install --no-assets   # skip the JS assets
```

Equivalent to publishing the `laranail::fluent-http-ajaxify-config`, `laranail::fluent-http-ajaxify-assets`
and `laranail::fluent-http-ajaxify-views` tags individually.

## `ajaxify:publish`

Publishes the JS assets only (the `laranail::fluent-http-ajaxify-assets` tag) to the `assets_path`
directory under `public/`:

```bash
php artisan ajaxify:publish
php artisan ajaxify:publish --force       # overwrite files already published
```

## `ajaxify:update-axios`

The Blade component loads Axios from a CDN with a local fallback; both the CDN URL and the SRI integrity
hash are pinned to a version. This command upgrades them safely:

```bash
php artisan ajaxify:update-axios             # upgrade to the latest release
php artisan ajaxify:update-axios --publish   # upgrade + re-publish assets to public/
php artisan ajaxify:update-axios --dry-run   # preview without changing anything
```

> The command also declares a `--version=<x.y.z>` option for pinning a specific release, but it does
> not currently work: the name collides with Artisan's own global `--version` flag, so
> `php artisan ajaxify:update-axios --version=1.14.0` prints the Laravel version and exits without
> running the command, and `php artisan ajaxify:update-axios --help` fails with
> *An option named "version" already exists*. Until the option is renamed, pin a version by editing
> `axios_version` and `axios_sri` in the config by hand (see [Configuration](../configuration.md)).

It fetches the latest version from the npm registry, downloads the minified build, validates it
(size + content), computes the SHA-384 SRI hash, and updates `axios_version` + `axios_sri` in your config.

Schedule periodic updates in `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('ajaxify:update-axios --publish')->monthly();
```

---

[← Docs index](../../README.md#documentation)
