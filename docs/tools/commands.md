# Artisan commands

Three commands install the assets and keep the bundled Axios pinned and current:
`laranail::fluent-http-ajaxify.install`, `laranail::fluent-http-ajaxify.publish` and
`laranail::fluent-http-ajaxify.update-axios`. See the [Documentation index](../../README.md#documentation).

> The earlier bare names `ajaxify:install`, `ajaxify:publish` and `ajaxify:update-axios` are deprecated
> aliases. They still run the same command and print a deprecation warning naming the replacement; they
> will be removed in the next minor after 0.1.

## `laranail::fluent-http-ajaxify.install`

Publishes the config, JS assets, and Blade views in one step:

```bash
php artisan laranail::fluent-http-ajaxify.install
php artisan laranail::fluent-http-ajaxify.install --force       # overwrite files already published
php artisan laranail::fluent-http-ajaxify.install --no-assets   # skip the JS assets
```

Equivalent to publishing the `laranail::fluent-http-ajaxify-config`, `laranail::fluent-http-ajaxify-assets`
and `laranail::fluent-http-ajaxify-views` tags individually.

## `laranail::fluent-http-ajaxify.publish`

Publishes the JS assets only (the `laranail::fluent-http-ajaxify-assets` tag) to the `assets_path`
directory under `public/`:

```bash
php artisan laranail::fluent-http-ajaxify.publish
php artisan laranail::fluent-http-ajaxify.publish --force       # overwrite files already published
```

## `laranail::fluent-http-ajaxify.update-axios`

The Blade component loads Axios from a CDN with a local fallback; both the CDN URL and the SRI integrity
hash are pinned to a version. This command upgrades them safely:

```bash
php artisan laranail::fluent-http-ajaxify.update-axios                        # upgrade to the latest release
php artisan laranail::fluent-http-ajaxify.update-axios --axios-version=1.14.0 # pin a specific release
php artisan laranail::fluent-http-ajaxify.update-axios --publish              # (re-)publish the assets first
php artisan laranail::fluent-http-ajaxify.update-axios --dry-run              # preview without changing anything
```

It fetches the latest version from the npm registry (or takes `--axios-version`), downloads the minified
build, validates it (size + content), and computes the SHA-384 SRI hash. It then writes the build to
`public/<assets_path>/axios.min.js` and updates `axios_version` + `axios_sri` in
`config/laranail/fluent-http-ajaxify.php`.

It only writes those **published** copies, never the package under `vendor/`. If the config or the assets
are not published it lists what is missing with the `vendor:publish` tag for each, and exits non-zero.
`--publish` publishes the assets (with `--force`) before writing the new build, so it covers a missing
assets directory; the config must be published either way.

> The pinning option was `--version` until it was renamed `--axios-version`. Artisan reserves `--version`
> for every command, so the old option never worked: it printed the Laravel version, and `--help` failed
> with *An option named "version" already exists*.

Schedule periodic updates in `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('laranail::fluent-http-ajaxify.update-axios')->monthly();
```

---

[← Docs index](../../README.md#documentation)
