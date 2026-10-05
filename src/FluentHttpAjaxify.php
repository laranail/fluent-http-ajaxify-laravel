<?php

namespace Simtabi\Laranail\FluentHttpAjaxify;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Http\RedirectResponse;
use Simtabi\Laranail\FluentHttpAjaxify\Contracts\FluentHttpAjaxifyInterface;

class FluentHttpAjaxify implements FluentHttpAjaxifyInterface
{
    public const VIEW_REDRAW = 'redraw';

    public const VIEW_APPEND = 'append';

    public const VIEW_PREPEND = 'prepend';

    protected Request $request;

    protected array $json = [];

    protected array $flash = [];

    protected array $sections = [];

    protected string $drawMode = self::VIEW_REDRAW;

    protected ?string $viewHtmlId = null;

    protected array $meta = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Static helper to collect flash messages from a request's session.
     * Shared between the main service and middleware to avoid code duplication.
     */
    public static function collectFlashesFromRequest(Request $request): array
    {
        $flashes = [];

        if (! $request->hasSession()) {
            return $flashes;
        }

        $session = $request->session();
        $keys = config('laranail.fluent-http-ajaxify.flash_keys', []);

        foreach ($keys as $sessionKey => $toastType) {
            if ($session->has($sessionKey)) {
                $value = $session->get($sessionKey);

                $flashes[] = [
                    'type'    => $toastType,
                    'message' => is_string($value) ? $value : json_encode($value),
                ];
            }
        }

        return $flashes;
    }

    /**
     * Return self (facade convenience).
     */
    public function instance(): static
    {
        return $this;
    }

    /**
     * Detect if the current request is an AJAX/API request.
     * Checks X-Requested-With, Accept header, and custom headers.
     */
    public function is(): bool
    {
        if ($this->request->ajax()) {
            return true;
        }

        if ($this->request->wantsJson()) {
            return true;
        }

        $headers = config('laranail.fluent-http-ajaxify.ajax_headers', ['X-Requested-With', 'X-Fluent-Http-Ajaxify']);
        foreach ($headers as $header) {
            if ($this->request->hasHeader($header)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dual-mode redirect: JSON for AJAX, RedirectResponse for normal HTTP.
     */
    public function redirect(string $to, int $status = 302, array $headers = [], ?bool $secure = null): JsonResponse|RedirectResponse
    {
        if ($this->is()) {
            $this->json['redirect'] = $to;
            $this->json['success'] = true;

            return $this->jsonResponse($status);
        }

        return redirect($to, $status, $headers, $secure);
    }

    /**
     * Redirect back to previous URL.
     */
    public function redirectBack(int $status = 302, array $headers = []): JsonResponse|RedirectResponse
    {
        return $this->redirect(URL::previous(), $status, $headers);
    }

    /**
     * Redirect with validation errors (422).
     */
    public function redirectWithErrors(string $url, $provider, int $status = 422): JsonResponse|RedirectResponse
    {
        if ($this->is()) {
            $errors = $provider instanceof MessageBag ? $provider : new MessageBag((array) $provider);
            $this->json['success'] = false;
            $this->json['message'] = 'Validation failed';
            $this->json['errors'] = $errors->toArray();

            return $this->jsonResponse($status);
        }

        $errors = $provider instanceof MessageBag ? $provider : new MessageBag((array) $provider);

        return redirect($url)->withErrors($errors);
    }

    /**
     * Dual-mode view: sections JSON for AJAX, normal View for HTTP.
     */
    public function view(string $view, array $data = [], array $mergeData = []): JsonResponse|\Illuminate\Contracts\View\View
    {
        if ($this->is()) {
            $rendered = View::make($view, $data, $mergeData)->render();

            if ($this->viewHtmlId) {
                $mode = $this->drawMode;
                $this->sections[$this->viewHtmlId] = [
                    'html' => $rendered,
                    'mode' => $mode,
                ];
                $this->viewHtmlId = null;
                $this->drawMode = 'redraw';
            }

            $this->json['success'] = true;
            if (! empty($this->sections)) {
                $this->json['sections'] = $this->sections;
            }

            return $this->jsonResponse();
        }

        return View::make($view, $data, $mergeData);
    }

    /**
     * Mark a container for full view redraw.
     */
    public function redrawView(string $htmlId): static
    {
        $this->viewHtmlId = '#' . ltrim($htmlId, '#');
        $this->drawMode = 'redraw';

        return $this;
    }

    /**
     * Mark a container for view append.
     */
    public function appendView(string $htmlId): static
    {
        $this->viewHtmlId = '#' . ltrim($htmlId, '#');
        $this->drawMode = 'append';

        return $this;
    }

    /**
     * Mark a container for view prepend.
     */
    public function prependView(string $htmlId): static
    {
        $this->viewHtmlId = '#' . ltrim($htmlId, '#');
        $this->drawMode = 'prepend';

        return $this;
    }

    /**
     * Mark a Blade @section for redraw.
     */
    public function redrawSection(string $name): static
    {
        $this->sections['#' . ltrim($name, '#')] = [
            'html' => '',
            'mode' => 'redraw',
        ];

        return $this;
    }

    /**
     * Batch mark sections for redraw.
     */
    public function redrawSections(array $names): static
    {
        foreach ($names as $name) {
            $this->redrawSection($name);
        }

        return $this;
    }

    /**
     * Add a flash/toast message to the response.
     */
    public function flash(string $type, string $message, ?string $title = null): static
    {
        $entry = ['type' => $type, 'message' => e($message)];
        if ($title !== null) {
            $entry['title'] = e($title);
        }
        $this->flash[] = $entry;

        return $this;
    }

    public function success(string $message, ?string $title = null): static
    {
        return $this->flash('success', $message, $title);
    }

    public function error(string $message, ?string $title = null): static
    {
        return $this->flash('error', $message, $title);
    }

    public function warning(string $message, ?string $title = null): static
    {
        return $this->flash('warning', $message, $title);
    }

    public function info(string $message, ?string $title = null): static
    {
        return $this->flash('info', $message, $title);
    }

    /**
     * Scroll to an element on the client.
     */
    public function scrollTo(string $htmlId): static
    {
        $this->json['scrollTo'] = '#' . ltrim($htmlId, '#');

        return $this;
    }

    /**
     * Enable dump output on the client (debug mode).
     */
    public function dump($data = true): static
    {
        $this->json['dump'] = $data;

        return $this;
    }

    /**
     * Send JavaScript code for client execution.
     * SECURITY: Gated by config('laranail.fluent-http-ajaxify.allow_js_eval').
     */
    public function runJavascript(string $code): static
    {
        if (config('laranail.fluent-http-ajaxify.allow_js_eval', false)) {
            $this->json['runJavascript'] = $code;
        }

        return $this;
    }

    /**
     * Add an alert message (uses toast on client, falls back to window.alert).
     */
    public function alert(string $message): static
    {
        $this->json['alert'] = e($message);

        return $this;
    }

    /**
     * Redirect by named route.
     */
    public function redirectRoute(string $name, array $params = [], int $status = 302): JsonResponse|RedirectResponse
    {
        return $this->redirect(route($name, $params), $status);
    }

    /**
     * Redirect by controller action.
     */
    public function redirectAction(string $controller, array $params = []): JsonResponse|RedirectResponse
    {
        return $this->redirect(action($controller, $params));
    }

    /**
     * Attach metadata to the response (pagination, timestamps, etc.).
     */
    public function withMeta(array $meta): static
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    /**
     * Add a confirmation prompt directive to the response.
     */
    public function confirm(string $message): static
    {
        $this->json['confirm'] = $message;

        return $this;
    }

    /**
     * Add a closeModal directive for the client.
     */
    public function closeModal($value = true): static
    {
        $this->json['closeModal'] = $value;

        return $this;
    }

    /**
     * Add a resetForm directive for the client.
     */
    public function resetForm($value = true): static
    {
        $this->json['resetForm'] = $value;

        return $this;
    }

    /**
     * Add a custom client event emission directive.
     */
    public function emit(string $event, $data = null): static
    {
        // Validate event name to prevent injection into CustomEvent dispatch
        if (! preg_match('/^[a-zA-Z0-9._:\-]+$/', $event)) {
            return $this;
        }

        $emit = $this->json['emit'] ?? [];
        $emit[$event] = $data;
        $this->json['emit'] = $emit;

        return $this;
    }

    /**
     * Explicitly read flash messages from session and include in response.
     */
    public function flashFromSession(): static
    {
        $sessionFlashes = $this->collectSessionFlashes();
        foreach ($sessionFlashes as $flash) {
            $this->flash[] = $flash;
        }

        return $this;
    }

    /**
     * Set custom JSON data in the response.
     */
    public function setJson(array $data): static
    {
        $this->json['data'] = $data;

        return $this;
    }

    /**
     * Merge additional data into the JSON response.
     */
    public function mergeJson(array $data): static
    {
        $existing = $this->json['data'] ?? [];
        $this->json['data'] = array_merge($existing, $data);

        return $this;
    }

    /**
     * Build the final JSON response with all accumulated directives.
     */
    public function jsonResponse(int $status = 200): JsonResponse
    {
        $response = $this->json;

        if (! isset($response['success'])) {
            $response['success'] = $status >= 200 && $status < 400;
        }

        // Include flash messages
        if (! empty($this->flash)) {
            $response['flash'] = $this->flash;
        }

        // Auto-flash from session (deduplicated against explicit flashes)
        if (config('laranail.fluent-http-ajaxify.auto_flash', true)) {
            $sessionFlashes = $this->collectSessionFlashes();
            if (! empty($sessionFlashes)) {
                $existingFlash = $response['flash'] ?? [];

                // Build a set of existing type+message keys for deduplication
                $existingKeys = [];
                foreach ($existingFlash as $f) {
                    $existingKeys[$f['type'] . '|' . $f['message']] = true;
                }

                // Only add session flashes that don't duplicate explicit ones
                foreach ($sessionFlashes as $sf) {
                    $key = $sf['type'] . '|' . $sf['message'];
                    if (! isset($existingKeys[$key])) {
                        $existingFlash[] = $sf;
                    }
                }

                $response['flash'] = $existingFlash;
            }
        }

        // Include meta
        if (! empty($this->meta)) {
            $response['meta'] = $this->meta;
        }

        // Include sections — per-section drawMode support
        if (! empty($this->sections)) {
            // Collect all modes to detect if they're mixed
            $modes = [];
            foreach ($this->sections as $section) {
                $mode = is_array($section) && isset($section['mode']) ? $section['mode'] : 'redraw';
                $modes[$mode] = true;
            }
            $hasMixedModes = count($modes) > 1;

            $formattedSections = [];
            foreach ($this->sections as $selector => $section) {
                $html = is_array($section) && isset($section['html']) ? $section['html'] : (is_string($section) ? $section : '');
                $mode = is_array($section) && isset($section['mode']) ? $section['mode'] : 'redraw';

                if ($hasMixedModes) {
                    // Mixed modes: emit per-section {html, mode} objects
                    $formattedSections[$selector] = ['html' => $html, 'mode' => $mode];
                } else {
                    // Uniform mode: flat HTML strings (backward compat)
                    $formattedSections[$selector] = $html;
                }
            }
            $response['sections'] = $formattedSections;

            if (! $hasMixedModes) {
                // Single mode for all sections — set top-level drawMode
                $singleMode = array_key_first($modes);
                if ($singleMode !== 'redraw') {
                    $response['drawMode'] = $singleMode;
                }
            }
        }

        // Reset state for next call
        $this->reset();

        $jsonResponse = response()->json($response, $status);

        // Security headers to prevent MIME sniffing and caching of sensitive data
        $jsonResponse->headers->set('X-Content-Type-Options', 'nosniff');
        $jsonResponse->headers->set('Cache-Control', 'no-store');

        return $jsonResponse;
    }

    /**
     * Collect flash messages from the session.
     */
    protected function collectSessionFlashes(): array
    {
        return static::collectFlashesFromRequest($this->request);
    }

    /**
     * Reset internal state.
     */
    protected function reset(): void
    {
        $this->json = [];
        $this->flash = [];
        $this->sections = [];
        $this->meta = [];
        $this->drawMode = self::VIEW_REDRAW;
        $this->viewHtmlId = null;
    }
}
