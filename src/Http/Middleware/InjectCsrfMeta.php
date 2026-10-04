<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that auto-injects a <meta name="csrf-token"> tag into HTML responses.
 * This ensures the FluentHttpAjaxify client can detect the CSRF token automatically.
 *
 * Registered by the service provider as the route middleware alias
 * `laranail-fluent-http-ajaxify-csrf`:
 *   Route::middleware('laranail-fluent-http-ajaxify-csrf')->group(...)
 */
class InjectCsrfMeta
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!config('laranail.fluent-http-ajaxify.inject_csrf_meta', true)) {
            return $response;
        }

        // Only inject into HTML responses
        $contentType = $response->headers->get('Content-Type', '');
        if (strpos($contentType, 'text/html') === false && !$response instanceof \Illuminate\Http\Response) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || empty($content)) {
            return $response;
        }

        // Skip if already has csrf-token meta
        if (strpos($content, 'name="csrf-token"') !== false || strpos($content, "name='csrf-token'") !== false) {
            return $response;
        }

        // Also skip if _token meta exists (laravel-ajax style)
        if (strpos($content, 'name="_token"') !== false || strpos($content, "name='_token'") !== false) {
            return $response;
        }

        // Inject before </head> or at start of <body>
        $token = csrf_token();
        $meta = '<meta name="csrf-token" content="' . e($token) . '">';

        if (preg_match('/<\/head\s*>/i', $content)) {
            $content = preg_replace('/(<\/head\s*>)/i', '    ' . $meta . "\n$1", $content, 1);
        } elseif (strpos($content, '<body') !== false) {
            $content = preg_replace('/<body([^>]*)>/i', '<body$1>' . "\n    " . $meta, $content, 1);
        }

        $response->setContent($content);

        // Recalculate Content-Length after injection
        if ($response->headers->has('Content-Length')) {
            $response->headers->set('Content-Length', strlen($content));
        }

        return $response;
    }
}
