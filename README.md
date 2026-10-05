# laranail/fluent-http-ajaxify-laravel

[![Tests](https://github.com/laranail/fluent-http-ajaxify-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/laranail/fluent-http-ajaxify-laravel/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Two of the usual four badges are absent because their claims would be false: the package is not published to Packagist (no registry-version badge), and the repository runs no static-analysis workflow.

> Laravel integration for [FluentHttpAjaxify](https://github.com/laranail/fluent-http-ajaxify-js) — server-driven AJAX responses from one controller return value: dual-mode responses (JSON for AJAX, real redirects/views otherwise), a fluent builder (flash/toast, section redraws, modal/form control, events), a controller trait, form-request validation, middleware, a Blade component, and a CSRF-safe, SRI-pinned Axios loader.

Requires PHP `^8.4.1 || ^8.5` and Laravel `^13.0`.

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
php artisan laranail::fluent-http-ajaxify.install
```

The service provider + `Ajaxify` facade are auto-discovered. The JavaScript client ships in `resources/js`,
synced from [`laranail/fluent-http-ajaxify-js`](https://github.com/laranail/fluent-http-ajaxify-js); the install command publishes it.

## Quick start guide and usage

### Getting started

`laranail::fluent-http-ajaxify.install` (above; the old `ajaxify:install` still works as a deprecated alias) publishes the config to `config/laranail/fluent-http-ajaxify.php` and the JS
the component loads to `public/vendor/fluent-http/`. Then load the client in your layout, before `</body>`:

```blade
<x-laranail-fluent-http-ajaxify::scripts />
```

### Usage

```php
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Simtabi\Laranail\FluentHttpAjaxify\Facade\Ajaxify;

public function store(StoreUserRequest $request)
{
    User::create($request->validated());

    // JSON with a toast for an AJAX request; a real 302 redirect otherwise
    return Ajaxify::success('User created')->redirect('/users');
}
```

Or via the controller trait:

```php
use Simtabi\Laranail\FluentHttpAjaxify\Traits\HasFluentHttpAjaxify;

// in a controller that declares `use HasFluentHttpAjaxify;`
return $this->ajaxSuccess('User created', ['user' => $user]);
```

The full walkthrough is in [Getting started](docs/getting-started.md); everything else is in the [documentation index](#documentation).

## <a name="documentation"></a>Documentation

Full documentation is at **[opensource.simtabi.com/documentation/laranail/fluent-http-ajaxify-laravel](https://opensource.simtabi.com/documentation/laranail/fluent-http-ajaxify-laravel/)** — installation, getting started, the fluent builder, the controller trait, validation, middleware, the client loader, and configuration.

## Stability

Pre-1.0, with a test suite covering the response protocol, security headers,
middleware, validation, and the Blade tag, commands and publish tags this README names. Constraints resolve `^0.1`; new SemVer minors begin
at 1.0.

## Contributing & security

Issues and PRs are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Report vulnerabilities per
[SECURITY.md](SECURITY.md) (security@simtabi.com); participation follows the [Code of Conduct](CODE_OF_CONDUCT.md).

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
