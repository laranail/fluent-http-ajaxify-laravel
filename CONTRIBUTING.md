# Contributing to `laranail/fluent-http-ajaxify-laravel`

## Getting set up

```bash
composer install
vendor/bin/phpunit
```

No lock file is committed. This is a library, so a lock records a resolution
consumers never use, and it goes stale invisibly because CI resolves fresh.

## Conventions

- PHP `^8.4.1 || ^8.5`, Laravel `^13.0`.
- PSR-4 under `Simtabi\Laranail\FluentHttpAjaxify\`.
- **Every public name carries the vendor and the package slug.** Config keys,
  view and translation namespaces, publish tags, middleware aliases and Artisan
  command names all live in flat, framework-owned maps: a second package
  claiming one does not conflict, it silently replaces the first. Config here is
  `config('laranail.<slug>.*')`.
- Dependencies use range constraints (`^1.2`), never an exact pin.

## Pull requests

Branch from `main`, keep the subject line under 72 characters and in the
imperative, and explain *why* in the body rather than restating the diff. Add a
`CHANGELOG.md` entry under `## [Unreleased]` for anything a consumer would
notice.

## Reporting problems

Bugs and features: GitHub Issues. Vulnerabilities: see
[SECURITY.md](SECURITY.md) — **opensource@simtabi.com**, never a public issue.
