# Usage patterns

The five ways to produce Ajaxify responses — Blade component, facade, controller trait, form-request
validation, and middleware. See the [Documentation index](../../README.md#documentation).

## Blade component

Add to your layout before `</body>`:

```blade
<x-laranail-fluent-http-ajaxify::scripts />
```

This loads Axios (CDN with local fallback), FluentHttpAjaxify, FluentToast, and optionally
FluentHttpWrapper.

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

The package ships two middleware classes and registers no alias for either; register them in your
application's `bootstrap/app.php` under whatever names you choose:

```php
use Simtabi\Laranail\FluentHttpAjaxify\Http\Middleware\FluentHttpAjaxifyMiddleware;
use Simtabi\Laranail\FluentHttpAjaxify\Http\Middleware\InjectCsrfMeta;

->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'ajaxify.ajax' => FluentHttpAjaxifyMiddleware::class, // convert redirects to JSON for AJAX requests
        'ajaxify.csrf' => InjectCsrfMeta::class,              // inject the CSRF meta tag into HTML responses
    ]);
})
```

---

[← Docs index](../../README.md#documentation)
