# Changelog

All notable changes to `laranail/fluent-http-ajaxify-laravel` are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Route middleware aliases `laranail-fluent-http-ajaxify-ajax` (`FluentHttpAjaxifyMiddleware`) and
  `laranail-fluent-http-ajaxify-csrf` (`InjectCsrfMeta`), registered by the provider. The docs and the
  install command's output used to suggest `ajaxify.ajax` / `ajaxify.csrf`, which nothing registered.
- The views also register under `laranail/fluent-http-ajaxify::` (the composer package name). The
  hyphenated `laranail-fluent-http-ajaxify::` namespace and the `<x-laranail-fluent-http-ajaxify::scripts />`
  tag keep working over the same paths.
- Tests: every script the Blade component requests must be a file the package ships; `update-axios` is
  exercised against a throwaway base path and must leave the package itself untouched; the naming test
  reads commands, aliases, middleware and view hints from the live registries.

- The real JavaScript client. `resources/js/FluentHttpAjaxify.js` was a 232-byte placeholder, so every
  install published a client that did nothing. It, `FluentToast.js` and `FluentHttpWrapper.js` are now
  the v3.0.0 build from [`laranail/fluent-http-ajaxify-js`](https://github.com/laranail/fluent-http-ajaxify-js),
  each stamped with its source commit. Existing installs must re-publish with
  `php artisan laranail::fluent-http-ajaxify.publish --force` to replace the placeholder in `public/`.
- `bin/sync-client` (`composer sync-client`) copies those files from a client checkout and records the
  commit; it refuses a checkout with uncommitted changes under `assets/js`.
- Tests: the shipped client must exceed 100 KB, export `FluentHttpAjaxify` and carry the source-commit
  header; the toast module must be the real build; every asset the scripts view can reference, guarded
  or not, must exist in `resources/js`.
- Pint (`laravel/pint`, `pint.json`, `composer lint` / `composer format`) and a CI job running
  `vendor/bin/pint --test`. `pint.json` is package-tools' with `declare_strict_types` off, since that
  rule changes runtime coercion and belongs in its own change.

### Changed

- `illuminate/contracts` is now declared in `require` at `^13.0`. `src/` imports it, and it was only arriving transitively.
- `composer.json` declares `illuminate/console`, `illuminate/view` and `illuminate/validation`
  (`^13.0`), which the commands, the Blade component and the validation trait use; testbench had been
  supplying them.
- Install docs: the package is not on Packagist, so the README and `docs/installation.md` give the VCS
  repository block (with Packagist excluded for `laranail/*`) instead of a bare `composer require`.
- Source formatted with Pint (formatting only).
- **Breaking.** All public names move onto the org shapes: views/components
  `laranail-fluent-http-ajaxify::` (`<x-laranail-fluent-http-ajaxify::scripts />`;
  published overrides under `resources/views/vendor/laranail-fluent-http-ajaxify/`), publish
  tags `laranail::fluent-http-ajaxify-{config,assets,views}`, and the container alias
  `app('laranail-fluent-http-ajaxify')`. The bare forms are gone; a live-registry test pins
  all three.
- Commands are now `laranail::fluent-http-ajaxify.install`, `laranail::fluent-http-ajaxify.publish` and
  `laranail::fluent-http-ajaxify.update-axios`. The `ajaxify:*` names still run as deprecated aliases.
- **Breaking.** `update-axios`'s `--version` option is now `--axios-version`; it pins a release exactly as
  `--version` was meant to. `--version` could not be kept: Artisan reserves it on every command, so it
  printed the framework version, and `--help` threw *An option named "version" already exists*.
- **Breaking.** `update-axios` writes only the application's published copies: the build goes to
  `public/<assets_path>/axios.min.js` and the version + SRI to `config/laranail/fluent-http-ajaxify.php`.
  It used to write `axios.min.js` into the package's own `resources/js` (inside `vendor/`, lost on the
  next `composer update`) and could rewrite the package's unpublished config. With either copy
  unpublished it now names the `vendor:publish` tag to run and exits non-zero. `--publish` publishes the
  assets before writing rather than after, which used to overwrite the new build with the bundled one.
- The views publish tag now copies into `resources/views/vendor/laranail/fluent-http-ajaxify/`. Overrides
  already in `resources/views/vendor/laranail-fluent-http-ajaxify/` are still read, under both namespaces.

### Deprecated

- `ajaxify:install`, `ajaxify:publish` and `ajaxify:update-axios`, in favour of the
  `laranail::fluent-http-ajaxify.*` names. Each prints a warning naming its replacement; removal is
  planned for the next minor after 0.1.

### Fixed

- The Blade component requested `FluentToast.js` unconditionally, but the package does not ship it, so
  every page using the tag made a request that 404'd. It is now loaded only when present in the assets
  path, like `FluentHttpWrapper.js`. Without it the client falls back to its built-in console notifier.
- `HasFluentHttpAjaxify::ajaxRedirect()` and `ajaxSections()` declared `string $message = null`, an
  implicitly nullable parameter that PHP 8.4 deprecates; now `?string`.
- README badges: the tag badge is replaced by the Tests badge, with a line saying why there is no
  Packagist version badge (the package is not on Packagist) and no static-analysis badge (no such workflow).

- Docs: the controller examples called `validated()` on a plain `Request`; they now type-hint a
  form request or call `$request->validate([...])`. The documented publish tags, the
  `ajaxify:publish` command and the scheduler location (`routes/console.php`) now match the code,
  and the docs note that `ajaxify:update-axios --version` is shadowed by Artisan's global
  `--version` flag and that `csrf_header` and `section_sanitize` are not read.
- A test now renders the documented `<x-laranail-fluent-http-ajaxify::scripts />` tag and checks
  the documented command names and publish tags against the booted application.

[Unreleased]: https://github.com/laranail/fluent-http-ajaxify-laravel/compare/v0.1.0...HEAD
