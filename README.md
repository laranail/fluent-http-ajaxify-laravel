# laranail/fluent-http-ajaxify-laravel

[![Latest tag](https://img.shields.io/github/v/tag/laranail/fluent-http-ajaxify-laravel?style=flat-square&label=version)](https://github.com/laranail/fluent-http-ajaxify-laravel/releases)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

> Laravel integration for [FluentHttpAjaxify](https://github.com/simtabi/fluent-http-ajaxify) — server-driven AJAX responses from one controller return value: dual-mode responses (JSON for AJAX, real redirects/views otherwise), a fluent builder (flash/toast, section redraws, modal/form control, events), a controller trait, form-request validation, middleware, a Blade component, and a CSRF-safe, SRI-pinned Axios loader.

Requires PHP `^8.4.1 || ^8.5` and Laravel `^13.0`.

## Install

```bash
composer require laranail/fluent-http-ajaxify-laravel
php artisan ajaxify:install
```

The service provider + `Ajaxify` facade are auto-discovered.

## Quick start guide and usage

### Getting started

Load the client in your layout, before `</body>`:

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
// ...
return $this->ajaxSuccess('User created', ['user' => $user]);
```

The full walkthrough is in [Getting started](docs/getting-started.md); everything else is in the [documentation index](#documentation).

## <a name="documentation"></a>Documentation

Full documentation is at **[opensource.simtabi.com/documentation/laranail/fluent-http-ajaxify-laravel](https://opensource.simtabi.com/documentation/laranail/fluent-http-ajaxify-laravel/)** — installation, getting started, the fluent builder, the controller trait, validation, middleware, the client loader, and configuration.

## Stability

Pre-1.0, with 31 tests covering the response protocol, security headers,
middleware and validation. Constraints resolve `^0.1`; new SemVer minors begin
at 1.0.

## Contributing & security

Issues and PRs are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Report vulnerabilities per
[SECURITY.md](SECURITY.md) (opensource@simtabi.com); participation follows the [Code of Conduct](CODE_OF_CONDUCT.md).

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
