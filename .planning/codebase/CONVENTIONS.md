---
title: Coding Conventions
focus: quality
last_mapped_commit: 62e29ef718631beaccba36396ec8eeb0135ee789
last_mapped_date: 2026-06-11
---

# Coding Conventions

## Code Style

Follows standard **WordPress PHP** conventions throughout:

- **Indentation:** 4 spaces (no tabs).
- **Functions/methods:** `snake_case` (`add_admin_menu`, `configure_smtp`,
  `set_from_email`).
- **Class:** `PascalCase` (`GoogleWorkspaceSMTP`).
- **Properties:** `snake_case` with `private` visibility (`$option_name`,
  `...php:17`).
- **Array syntax:** long form `array(...)` (not `[...]`), consistent with the
  PHP 7.0 minimum and older WordPress style (`...php:20-24`).
- **Hook callbacks:** passed as `array($this, 'method')`.

## Prefixing

To avoid collisions in WordPress's global namespace, everything is prefixed:

- Option key: `gws_smtp_settings`
- Free functions: `gws_smtp_activation_notice`, `gws_smtp_display_activation_notice`
- Setting group / section IDs: `google_workspace_smtp`, `gws_smtp_section`

The class itself provides namespacing for the methods, so only the option key,
free functions, and IDs need manual prefixes.

## Patterns

- **OOP wrapper, procedural hooks.** Logic is grouped in a class, but the bootstrap
  (`new GoogleWorkspaceSMTP()`) and activation helpers are plain functions — the
  idiomatic WordPress mix.
- **Config read on demand.** Methods call `get_option($this->option_name, array())`
  each time rather than caching, with a default empty array to avoid null checks.
- **Early return for the no-op case.** `configure_smtp` bails when credentials are
  missing rather than nesting the whole body in an `if` (`...php:144-146`).
- **Render methods echo markup directly** using PHP open/close tags within the
  method body — standard for WordPress Settings API field callbacks.

## Internationalization (i18n)

Every user-facing string is wrapped in a translation function with the
`google-workspace-smtp` text domain:

- `__()` for returned strings, `_e()` for echoed strings,
  `esc_attr_e()` for escaped-attribute echoes, `printf`/`sprintf` with `__()`
  for interpolated strings (e.g. `...php:217, 221, 265`).

This is consistent and thorough — a good convention already in place.

## Security/Escaping Conventions

The codebase consistently applies WordPress escaping helpers:

- Output escaping: `esc_attr()`, `esc_html()`, `esc_attr_e()`
  (`...php:97, 100, 113, 221, 235`).
- Input sanitization: `sanitize_email()`, `sanitize_text_field()`
  (`...php:80, 89, 215`).
- Validation: `is_email()` before sending (`...php:216`).
- Nonces: `wp_nonce_field()` + `wp_verify_nonce()` for the test form
  (`...php:214, 232`).
- Capability checks: `current_user_can('manage_options')` and the
  `manage_options` requirement on the settings page (`...php:31, 166, 183`).

> One documented deviation: in the settings field renderers, the email/from-name
> `value` attributes are echoed via a `$value` variable that holds an
> `esc_attr()`-encoded string assigned earlier (`...php:97, 127`) — but the
> raw `<?php echo $value; ?>` at `...php:101, 131` relies on that prior escaping.
> See `CONCERNS.md` for the nuance.

## Comments

Comments are sparse and used only for non-obvious intent — matching the project's
own rule ("Comments for non-obvious logic ONLY"). Examples: explaining why the
password is **not** sanitized (`...php:84`), and why debug is gated
(`...php:165`).

## Error Handling

- No exceptions are thrown or caught — the plugin relies on WordPress's own return
  conventions.
- `wp_mail()` returns a boolean; the test feature branches on it and shows a
  success/error notice (`...php:220-224`).
- Failures in the real send path surface through WordPress + PHPMailer
  (`SMTPDebug = 2`) only when `WP_DEBUG` is on and the user is an admin.

## Linting / Enforcement

There is **no automated enforcement** (no PHPCS config, no CI). Conventions are
maintained by hand and are currently consistent.
