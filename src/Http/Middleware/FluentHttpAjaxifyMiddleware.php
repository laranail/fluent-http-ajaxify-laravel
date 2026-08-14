<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Simtabi\Laranail\FluentHttpAjaxify\FluentHttpAjaxify;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that auto-detects AJAX requests and formats responses
 * according to the FluentHttpAjaxify protocol.
 *
 * When an AJAX request receives a redirect, the middleware converts it
 * to a JSON response containing the target URL, any session flash
 * messages (when auto_flash is enabled), and validation errors.
 *
 * Register in your kernel or route group:
 *   'ajaxify.ajax' => \Simtabi\Laranail\FluentHttpAjaxify\Http\Middleware\FluentHttpAjaxifyMiddleware::class
 */
class FluentHttpAjaxifyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->isAjaxRequest($request)) {
            return $response;
        }

        // Auto-convert redirect responses to protocol-compatible JSON
        if ($response instanceof RedirectResponse) {
            return $this->convertRedirectToJson($request, $response);
        }

        return $response;
    }

    protected function convertRedirectToJson(Request $request, RedirectResponse $response): JsonResponse
    {
        $targetUrl = $response->getTargetUrl();

        // Reject dangerous URL schemes (XSS vectors)
        if (preg_match('/^\s*(javascript|data|vbscript)\s*:/i', $targetUrl)) {
            $targetUrl = '/';
        }

        $payload = [
            'success'  => !$response->isClientError() && !$response->isServerError(),
            'message'  => 'Redirecting',
            'redirect' => $targetUrl,
        ];

        // Session data is only available when a session store is bound
        // (not available on stateless API routes without session middleware).
        if ($request->hasSession()) {
            // Carry over session flash messages as toast directives
            if (config('laranail.fluent-http-ajaxify.auto_flash', true)) {
                $flash = FluentHttpAjaxify::collectFlashesFromRequest($request);

                if (!empty($flash)) {
                    $payload['flash'] = $flash;
                }
            }

            // Carry over validation errors (from redirectWithErrors)
            $session = $request->session();

            if ($session->has('errors')) {
                $errors = $session->get('errors');

                if ($errors instanceof \Illuminate\Support\ViewErrorBag && $errors->any()) {
                    $payload['errors']  = $errors->getBag('default')->toArray();
                    $payload['success'] = false;
                }
            }
        }

        return response()->json($payload, $response->getStatusCode());
    }


    protected function isAjaxRequest(Request $request): bool
    {
        if ($request->ajax() || $request->wantsJson()) {
            return true;
        }

        $headers = config('laranail.fluent-http-ajaxify.ajax_headers', ['X-Requested-With', 'X-Fluent-Http-Ajaxify']);

        foreach ($headers as $header) {
            if ($request->hasHeader($header)) {
                return true;
            }
        }

        return false;
    }
}
