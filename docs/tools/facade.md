# Facade & service API

The `Ajaxify` facade (`Simtabi\Laranail\FluentHttpAjaxify\Facade\Ajaxify`, resolving the `FluentHttpAjaxify` service)
— every method for building a server-driven AJAX response. See the [Documentation index](../../README.md#documentation).

## Methods

| Method | Description |
|--------|-------------|
| `is()` | Detect if the current request is AJAX |
| `redirect($to, $status)` | Dual-mode redirect (JSON for AJAX, HTTP redirect otherwise) |
| `redirectBack()` | Redirect to the previous URL |
| `redirectRoute($name, $params)` | Redirect by named route |
| `redirectAction($controller, $params)` | Redirect by controller action |
| `redirectWithErrors($url, $errors)` | Redirect with validation errors (422) |
| `view($view, $data)` | Dual-mode view rendering |
| `redrawView($htmlId)` | Mark a container for full redraw |
| `appendView($htmlId)` | Mark a container for append |
| `prependView($htmlId)` | Mark a container for prepend |
| `redrawSection($name)` | Mark a section for redraw |
| `redrawSections($names)` | Batch-mark sections |
| `flash($type, $message, $title)` | Add a toast message |
| `success($msg)` / `error($msg)` / `warning($msg)` / `info($msg)` | Shorthand flash |
| `flashFromSession()` | Pull flash from the session |
| `alert($message)` | Alert directive |
| `scrollTo($htmlId)` | Scroll directive |
| `dump($data)` | Debug dump |
| `runJavascript($code)` | JS eval (gated by `allow_js_eval` config) |
| `withMeta($meta)` | Attach metadata |
| `confirm($message)` | Confirmation prompt |
| `closeModal()` | Close-modal directive |
| `resetForm()` | Reset-form directive |
| `emit($event, $data)` | Emit a custom event |
| `setJson($data)` / `mergeJson($data)` | Set/merge response data |
| `jsonResponse($status)` | Build the final JSON response |

## Example

```php
use Simtabi\Laranail\FluentHttpAjaxify\Facade\Ajaxify;

return Ajaxify::success('Done')
    ->withMeta(['total' => 42])
    ->closeModal()
    ->resetForm()
    ->emit('user.created', $user->id)
    ->jsonResponse();
```

---

[← Docs index](../../README.md#documentation)
