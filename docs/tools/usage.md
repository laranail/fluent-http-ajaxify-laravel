# Usage patterns

The five ways to produce Ajaxify responses — Blade component, facade, controller trait, form-request
validation, and middleware. See the [Documentation index](../../README.md#documentation).

## Blade component

Add to your layout before `</body>`:

```blade
<x-laranail-fluent-http-ajaxify::scripts />
```

This loads Axios (CDN with local fallback) and FluentHttpAjaxify, plus FluentToast and FluentHttpWrapper
once they are in the assets path. The package ships all three in `resources/js`, synced from
[`laranail/fluent-http-ajaxify-js`](https://github.com/laranail/fluent-http-ajaxify-js), and publishing
the assets puts them there. If you delete FluentToast from the assets path, the client falls back to its
built-in console notifier for flash messages.

The view is also reachable directly as `laranail/fluent-http-ajaxify::components.scripts`.
Overrides publish to `resources/views/vendor/laranail/fluent-http-ajaxify/`. Overrides published by
earlier releases to `resources/views/vendor/laranail-fluent-http-ajaxify/` are still read.

## Facade

```php
use Simtabi\Laranail\FluentHttpAjaxify\Facade\Ajaxify;

return Ajaxify::redirect('/dashboard');                              // dual-mode redirect
return Ajaxify::success('Saved!')->redirect('/users');              // flash + redirect
return Ajaxify::redrawView('user-list')->view('users.index', compact('users'));
return Ajaxify::redirectWithErrors('/form', $validator->errors());  // validation errors
```

See the full method list in [Facade & service API](facade.md).

## Controller trait

```php
use App\Models\User;
use Illuminate\Http\Request;
use Simtabi\Laranail\FluentHttpAjaxify\Traits\HasFluentHttpAjaxify;

class UserController extends Controller
{
    use HasFluentHttpAjaxify;

    public function store(Request $request)
    {
        $user = User::create($request->validate([
            'email' => 'required|email',
            'name'  => 'required|min:2',
        ]));
        return $this->ajaxSuccess('User created', ['user' => $user]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return $this->ajaxRedirect('/users', 'User deleted');
    }
}
```

Trait methods: `ajaxSuccess()`, `ajaxError()`, `ajaxRedirect()`, `ajaxValidationError()`, `ajaxSections()`.

## Form-request validation

```php
use Simtabi\Laranail\FluentHttpAjaxify\Traits\FluentHttpAjaxifyValidation;

class StoreUserRequest extends FormRequest
{
    use FluentHttpAjaxifyValidation;

    public function rules(): array
    {
        return ['email' => 'required|email', 'name' => 'required|min:2'];
    }
}
```

Returns protocol-compatible 422 JSON with `flash` messages for automatic toast display.

## Middleware

The package ships two middleware classes and registers a vendor-scoped alias for each:

| Alias | Class | Does |
|---|---|---|
| `laranail-fluent-http-ajaxify-ajax` | `FluentHttpAjaxifyMiddleware` | converts redirects to JSON for AJAX requests |
| `laranail-fluent-http-ajaxify-csrf` | `InjectCsrfMeta` | injects the CSRF meta tag into HTML responses |

```php
Route::middleware(['web', 'laranail-fluent-http-ajaxify-ajax', 'laranail-fluent-http-ajaxify-csrf'])
    ->group(function () {
        // ...
    });
```

The aliases use hyphens only: Laravel splits a middleware name on `:` to read parameters. The class
names work as well, for example in `bootstrap/app.php` with `$middleware->web(append: [InjectCsrfMeta::class])`.

---

[← Docs index](../../README.md#documentation)
