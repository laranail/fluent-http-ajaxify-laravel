# Configuration

Every key in `config/fluent-http-ajaxify.php`. See the [Documentation index](../README.md#documentation).

## Keys

| Key | Default | Description |
|-----|---------|-------------|
| `inject_csrf_meta` | `true` | Auto-inject `<meta name="csrf-token">` via middleware |
| `include_csrf_meta` | `true` | Render CSRF meta in the Blade component (set `false` if the layout already has it) |
| `csrf_header` | `X-CSRF-TOKEN` | CSRF header name |
| `allow_js_eval` | `false` | Enable `runJavascript()` (security-gated) |
| `section_sanitize` | `basic` | Client-side HTML sanitization mode |
| `auto_flash` | `true` | Auto-include session flash messages in responses |
| `flash_keys` | `[...]` | Session keys mapped to toast types |
| `include_axios` | `true` | Load Axios via the Blade component (set `false` if already bundled) |
| `axios_version` | `1.13.6` | Pinned Axios version for CDN + local fallback |
| `axios_sri` | `sha384-...` | SRI hash for CDN integrity verification |
| `assets_path` | `vendor/fluent-http` | Public path for JS assets |
| `ajax_headers` | `[...]` | Headers used to detect AJAX requests |

## Keeping Axios up to date

`axios_version` + `axios_sri` are pinned. Use [`ajaxify:update-axios`](tools/commands.md) to upgrade them
safely (fetches the latest release, validates the download, recomputes the SRI hash, and updates the config).

---

[← Docs index](../README.md#documentation)
