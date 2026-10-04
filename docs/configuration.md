# Configuration

Every key in `config/laranail/fluent-http-ajaxify.php`. See the [Documentation index](../README.md#documentation).

## Keys

| Key | Default | Description |
|-----|---------|-------------|
| `inject_csrf_meta` | `true` | Auto-inject `<meta name="csrf-token">` via middleware |
| `include_csrf_meta` | `true` | Render CSRF meta in the Blade component (set `false` if the layout already has it) |
| `csrf_header` | `X-CSRF-TOKEN` | CSRF header name. Not currently read by the package (see below) |
| `allow_js_eval` | `false` | Enable `runJavascript()` (security-gated) |
| `section_sanitize` | `basic` | Client-side HTML sanitization mode (`strict`, `basic`, `none`). Not currently read by the package (see below) |
| `auto_flash` | `true` | Auto-include session flash messages in responses |
| `flash_keys` | `[...]` | Session keys mapped to toast types |
| `include_axios` | `true` | Load Axios via the Blade component (set `false` if already bundled) |
| `axios_version` | `1.13.6` | Pinned Axios version for CDN + local fallback |
| `axios_sri` | `sha384-...` | SRI hash for CDN integrity verification |
| `assets_path` | `vendor/fluent-http` | Public path for JS assets |
| `ajax_headers` | `[...]` | Headers used to detect AJAX requests |

> `csrf_header` and `section_sanitize` are declared in the config file but nothing in the package reads
> them yet, on the server or in the bundled `FluentHttpAjaxify.js`, so changing either has no effect.

## Keeping Axios up to date

`axios_version` + `axios_sri` are pinned. Use [`ajaxify:update-axios`](tools/commands.md) to upgrade them
to the latest release safely (fetches it, validates the download, recomputes the SRI hash, and updates the
config).

To pin a specific version, set both keys by hand. Compute the hash from that version's `axios.min.js`:

```bash
openssl dgst -sha384 -binary axios.min.js | openssl base64 -A   # prefix the output with "sha384-"
```

---

[← Docs index](../README.md#documentation)
