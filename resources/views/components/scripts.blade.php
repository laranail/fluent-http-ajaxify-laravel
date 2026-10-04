{{-- FluentHttpAjaxify Scripts Blade Component --}}
{{--
    Usage:
      <x-laranail-fluent-http-ajaxify::scripts />

    Per-instance prop overrides (take precedence over config):
      <x-laranail-fluent-http-ajaxify::scripts
          :include-csrf="false"
          :include-axios="false"
          :nonce="$myNonce"
      />

    Config-level toggles in config/laranail/fluent-http-ajaxify.php:
      - include_csrf_meta : render <meta name="csrf-token"> (default: true)
      - include_axios     : load Axios CDN + local fallback  (default: true)
      - axios_version     : pinned CDN version               (default: 1.13.6)
      - axios_sri         : SRI hash for CDN integrity check
      - assets_path       : public path for published JS     (default: vendor/fluent-http)
--}}

@props([
    'includeCsrf'  => null,
    'includeAxios' => null,
    'nonce'        => null,
])

@once
@php
    $assetsPath     = config('laranail.fluent-http-ajaxify.assets_path', 'vendor/fluent-http');

    // CSP nonce: prop > csp_nonce() helper > empty string
    $nonce          = $nonce ?? (function_exists('csp_nonce') ? (csp_nonce() ?? '') : '');
    $nonceAttr      = $nonce ? ' nonce="' . e($nonce) . '"' : '';

    // Toggles: prop (explicit bool) > config > default true
    $includeCsrf    = $includeCsrf  ?? config('laranail.fluent-http-ajaxify.include_csrf_meta', true);
    $includeAxios   = $includeAxios ?? config('laranail.fluent-http-ajaxify.include_axios', true);

    // Axios CDN + SRI
    $axiosVer       = config('laranail.fluent-http-ajaxify.axios_version', '1.13.6');
    $axiosSri       = config('laranail.fluent-http-ajaxify.axios_sri', '');
    $axiosCdn       = "https://cdn.jsdelivr.net/npm/axios@{$axiosVer}/dist/axios.min.js";
    $axiosLocal     = asset($assetsPath . '/axios.min.js');
    $sriAttr        = $axiosSri ? ' integrity="' . e($axiosSri) . '" crossorigin="anonymous"' : '';
@endphp

{{-- CSRF Meta Tag (skip if already in layout — set include_csrf_meta to false or pass :include-csrf="false") --}}
@if($includeCsrf)
<meta name="csrf-token" content="{{ csrf_token() }}">
@endif

{{-- Axios: CDN with SRI + local fallback (skip if already loaded — set include_axios to false or pass :include-axios="false") --}}
@if($includeAxios)
<script src="{{ $axiosCdn }}"{!! $sriAttr !!}{!! $nonceAttr !!}></script>
<script{!! $nonceAttr !!}>
    if (typeof axios === 'undefined') {
        document.write('<script src="{!! $axiosLocal !!}"{!! $nonceAttr !!}><\/script>');
    }
</script>
@endif

{{-- FluentHttpAjaxify --}}
<script src="{{ asset($assetsPath . '/FluentHttpAjaxify.js') }}"{!! $nonceAttr !!}></script>

{{-- FluentToast (optional — only if published). The package does not ship it; copy
     assets/js/FluentToast.js from the fluent-http-ajaxify npm package into the assets
     path to enable rich toasts. Without it the client uses its built-in console
     notifier, as FluentHttpAjaxify.js documents. --}}
@if(file_exists(public_path($assetsPath . '/FluentToast.js')))
<script src="{{ asset($assetsPath . '/FluentToast.js') }}"{!! $nonceAttr !!}></script>
@endif

{{-- FluentHttpWrapper (optional — only if published) --}}
@if(file_exists(public_path($assetsPath . '/FluentHttpWrapper.js')))
<script src="{{ asset($assetsPath . '/FluentHttpWrapper.js') }}"{!! $nonceAttr !!}></script>
@endif

{{-- Auto-configure CSP nonce for FluentToast --}}
@if($nonce)
<script{!! $nonceAttr !!}>
    if (typeof FluentToast !== 'undefined') {
        FluentToast.configure({ nonce: @json($nonce) });
    }
</script>
@endif
@endonce
