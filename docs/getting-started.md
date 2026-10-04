# Getting started

Return your first dual-mode response — JSON for AJAX requests, a normal redirect/view otherwise. See the
[Documentation index](../README.md#documentation).

## 1. Install + publish

```bash
composer require laranail/fluent-http-ajaxify-laravel
php artisan ajaxify:install
```

See [Installation](installation.md).

## 2. Load the client

Add the component to your layout before `</body>`:

```blade
<x-laranail-fluent-http-ajaxify::scripts />
```

## 3. Return a response from a controller

`validated()` lives on a form request, so type-hint one (or call `$request->validate([...])` on a plain
`Request`):

```php
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Simtabi\Laranail\FluentHttpAjaxify\Facade\Ajaxify;

public function store(StoreUserRequest $request)
{
    User::create($request->validated());

    // JSON (with a toast) for AJAX; a real redirect for a normal request
    return Ajaxify::success('User created')->redirect('/users');
}
```

Or via the controller trait:

```php
use Simtabi\Laranail\FluentHttpAjaxify\Traits\HasFluentHttpAjaxify;

// in a controller that declares `use HasFluentHttpAjaxify;`
return $this->ajaxSuccess('User created', ['user' => $user]);
```

## Next steps

- [Usage patterns](tools/usage.md) — Blade / facade / trait / form-request / middleware.
- [Facade & service API](tools/facade.md) — every response method.
- [Configuration](configuration.md) — CSRF, Axios, flash, sanitization.

---

[← Docs index](../README.md#documentation)
