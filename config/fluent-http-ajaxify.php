<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CSRF Meta Tag — Middleware Injection
    |--------------------------------------------------------------------------
    |
    | When true, the InjectCsrfMeta middleware automatically adds
    | <meta name="csrf-token"> to HTML responses that lack one.
    | Disable if your layout already includes this tag.
    |
    */
    'inject_csrf_meta' => true,

    /*
    |--------------------------------------------------------------------------
    | CSRF Meta Tag — Blade Component
    |--------------------------------------------------------------------------
    |
    | When true, the <x-fluent-http-ajaxify::components.scripts /> Blade
    | component renders a <meta name="csrf-token"> tag. Set to false if
    | your layout already includes it to avoid duplicates.
    |
    */
    'include_csrf_meta' => true,

    /*
    |--------------------------------------------------------------------------
    | CSRF Header Name
    |--------------------------------------------------------------------------
    |
    | The header name used to send the CSRF token from the client.
    | Laravel expects X-CSRF-TOKEN by default.
    |
    */
    'csrf_header' => 'X-CSRF-TOKEN',

    /*
    |--------------------------------------------------------------------------
    | Allow Server-Driven JavaScript Execution
    |--------------------------------------------------------------------------
    |
    | SECURITY: When true, the `runJavascript()` method is enabled and its
    | content is sent to the client for execution via `new Function()`.
    | This is disabled by default for security reasons.
    |
    */
    'allow_js_eval' => false,

    /*
    |--------------------------------------------------------------------------
    | Section Sanitization Mode
    |--------------------------------------------------------------------------
    |
    | How the client sanitizes HTML in `sections` before DOM insertion.
    | Options: 'strict', 'basic', 'none'
    | - strict: whitelist-only tags (safest)
    | - basic: strips <script>, event handlers, javascript: URLs
    | - none: no sanitization (only for trusted content)
    |
    */
    'section_sanitize' => 'basic',

    /*
    |--------------------------------------------------------------------------
    | Auto-Flash Session Messages
    |--------------------------------------------------------------------------
    |
    | When true, session flash messages (success, error, warning, info)
    | are automatically included in AJAX responses as `flash` directives.
    |
    */
    'auto_flash' => true,

    /*
    |--------------------------------------------------------------------------
    | Flash Session Keys
    |--------------------------------------------------------------------------
    |
    | Session keys to check for flash messages. Each maps to a toast type.
    |
    */
    'flash_keys' => [
        'success' => 'success',
        'error'   => 'error',
        'warning' => 'warning',
        'info'    => 'info',
        'status'  => 'info',
        'message' => 'info',
    ],

    /*
    |--------------------------------------------------------------------------
    | Include Axios
    |--------------------------------------------------------------------------
    |
    | When true, the Blade component loads Axios (CDN with local fallback).
    | Set to false if you already load Axios elsewhere in your layout, or
    | if you use a bundler that includes it.
    |
    */
    'include_axios' => true,

    /*
    |--------------------------------------------------------------------------
    | Axios Version (pinned)
    |--------------------------------------------------------------------------
    |
    | The pinned Axios version used for the CDN URL. A matching local copy
    | is bundled as a fallback for offline/blocked CDN scenarios.
    |
    | To upgrade to the latest version automatically:
    |   php artisan ajaxify:update-axios
    |
    | To pin a specific version:
    |   php artisan ajaxify:update-axios --version=1.14.0
    |
    | The command downloads the new build, regenerates the SRI hash below,
    | and updates both values in this file. Add --publish to also push
    | the new file to public/.
    |
    */
    'axios_version' => '1.13.6',

    /*
    |--------------------------------------------------------------------------
    | Axios SRI Hash (Subresource Integrity)
    |--------------------------------------------------------------------------
    |
    | SHA-384 hash of the pinned axios.min.js for CDN integrity verification.
    | Prevents execution of tampered or MITM'd CDN scripts. This value is
    | managed automatically by the update command:
    |
    |   php artisan ajaxify:update-axios
    |
    | To regenerate manually:
    |   cat resources/js/axios.min.js | openssl dgst -sha384 -binary | openssl base64 -A
    |   # prefix the output with "sha384-"
    |
    */
    'axios_sri' => 'sha384-EtqfExzDvAOmLLdnOsa5Dy174/rTmPzv9OnQXw8NQOXnTypob284TIsp6Gt3yEyL',

    /*
    |--------------------------------------------------------------------------
    | Publishable Assets Path
    |--------------------------------------------------------------------------
    |
    | Where to publish the JS assets when running the publish command.
    |
    */
    'assets_path' => 'vendor/fluent-http',

    /*
    |--------------------------------------------------------------------------
    | Request Detection Headers
    |--------------------------------------------------------------------------
    |
    | Additional headers to check when detecting AJAX/API requests.
    | These are checked in addition to X-Requested-With.
    |
    */
    'ajax_headers' => [
        'X-Requested-With',
        'X-Fluent-Http-Ajaxify',
    ],

];
