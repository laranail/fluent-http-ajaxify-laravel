# Architecture

How a single controller return value serves both AJAX and normal requests. See the
[Documentation index](../README.md#documentation).

## The moving parts

- **`FluentHttpAjaxify` service** (+ the `Ajaxify` facade) — the fluent builder. It accumulates directives
  (flash, redraws, redirects, events) and, at `jsonResponse()`/`redirect()`/`view()`, emits either a JSON
  payload (AJAX) or a normal Laravel redirect/view. See [Facade & service API](tools/facade.md).
- **Traits** — `HasFluentHttpAjaxify` (controller shorthands) and `FluentHttpAjaxifyValidation`
  (protocol-compatible 422 responses from form requests).
- **Middleware** — `FluentHttpAjaxifyMiddleware` (converts redirects to JSON for AJAX) and `InjectCsrfMeta`
  (injects the CSRF meta tag), aliased `laranail-fluent-http-ajaxify-ajax` and
  `laranail-fluent-http-ajaxify-csrf`. See [Usage patterns](tools/usage.md).
- **Blade component + JS assets** — `<x-laranail-fluent-http-ajaxify::scripts />` (the view
  `resources/views/components/scripts.blade.php`, namespace `laranail/fluent-http-ajaxify::`) loads Axios
  (CDN + local fallback, SRI-pinned), FluentHttpAjaxify, and FluentToast when published; the client applies the server's directives (section redraws, toasts,
  modal/form control, event emission).
- **Commands** — `laranail::fluent-http-ajaxify.install`, `.publish` and `.update-axios` (the bare
  `ajaxify:*` names are deprecated aliases). See [Artisan commands](tools/commands.md).

## Request flow

1. The client sends a request with an AJAX header (`ajax_headers`).
2. The controller builds a response with `Ajaxify` (or the trait).
3. If the request is AJAX, a JSON directive payload is returned; otherwise a normal redirect/view.
4. The client library applies the directives — redraw sections, show toasts, close modals, emit events.

---

[← Docs index](../README.md#documentation)
