# Changelog

All notable changes to `laranail/fluent-http-ajaxify-laravel` are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

### Changed

- **Breaking.** All public names move onto the org shapes: views/components
  `laranail-fluent-http-ajaxify::` (`<x-laranail-fluent-http-ajaxify::scripts />`;
  published overrides under `resources/views/vendor/laranail-fluent-http-ajaxify/`), publish
  tags `laranail::fluent-http-ajaxify-{config,assets,views}`, and the container alias
  `app('laranail-fluent-http-ajaxify')`. The bare forms are gone; a live-registry test pins
  all three.

### Fixed

- Docs: the controller examples called `validated()` on a plain `Request`; they now type-hint a
  form request or call `$request->validate([...])`. The documented publish tags, the
  `ajaxify:publish` command and the scheduler location (`routes/console.php`) now match the code,
  and the docs note that `ajaxify:update-axios --version` is shadowed by Artisan's global
  `--version` flag and that `csrf_header` and `section_sanitize` are not read.
- A test now renders the documented `<x-laranail-fluent-http-ajaxify::scripts />` tag and checks
  the documented command names and publish tags against the booted application.

## [Unreleased]

Nothing yet.

[Unreleased]: https://github.com/laranail/fluent-http-ajaxify-laravel/compare/v0.1.0...HEAD
