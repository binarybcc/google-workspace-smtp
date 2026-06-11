---
title: Architecture
focus: arch
last_mapped_commit: 62e29ef718631beaccba36396ec8eeb0135ee789
last_mapped_date: 2026-06-11
---

# Architecture

## Pattern

**Single-file, single-class WordPress plugin.** The entire implementation lives in
one `GoogleWorkspaceSMTP` class plus two small free functions, all in
`google-workspace-smtp/google_workspace_smtp.php` (271 lines). This is the
conventional shape for a small, focused WordPress plugin.

The plugin is **event-driven**: it registers callbacks against WordPress hooks in
its constructor and then does nothing until WordPress fires one of those hooks.

## Entry Points

| Entry point | Location | Triggered by |
|-------------|----------|--------------|
| `new GoogleWorkspaceSMTP()` | `...php:250` | WordPress loading the plugin file |
| `register_activation_hook(...)` | `...php:253` | Plugin activation |
| `add_action('admin_notices', ...)` | `...php:259` | Admin page render |

On load, the file instantiates the class once (`...php:250`). The constructor
(`...php:19-25`) registers all hooks — nothing executes until WordPress fires them.

## Hook Wiring (the real control flow)

All behavior is attached in the constructor:

| Hook | Type | Callback | Purpose |
|------|------|----------|---------|
| `admin_menu` | action | `add_admin_menu` | Adds the settings page under Settings |
| `admin_init` | action | `settings_init` | Registers settings/section/fields |
| `phpmailer_init` | action | `configure_smtp` | **Core feature** — point PHPMailer at Gmail |
| `wp_mail_from` | filter | `set_from_email` | Override the From address |
| `wp_mail_from_name` | filter | `set_from_name` | Override the From display name |
| `admin_notices` | action | `gws_smtp_display_activation_notice` | One-time activation banner |

## Layers

For a file this small the "layers" are logical groupings of methods within the one
class:

1. **Bootstrap** — constructor registers hooks (`...php:19-25`).
2. **Admin UI** — settings page + field renderers
   (`add_admin_menu`, `options_page`, `*_render` methods, `...php:27-135, 181-246`).
3. **Settings registration & sanitization** — Settings API glue
   (`settings_init`, `sanitize_settings`, `...php:37-93`).
4. **Mail configuration** — the actual SMTP behavior
   (`configure_smtp`, `set_from_email`, `set_from_name`, `...php:141-179`).
5. **Activation** — free functions for the activation notice
   (`...php:253-270`).

## Data Flow

### Saving settings
```
Admin submits Settings form (options.php)
  → WordPress Settings API validates against registered setting
  → sanitize_settings()  [...php:76-93]   (sanitize email/name; password passed through)
  → stored in wp_options under gws_smtp_settings
```

### Sending an email (the main path)
```
Any code calls wp_mail(...)
  → WordPress fires wp_mail_from / wp_mail_from_name
      → set_from_email() / set_from_name() override sender   [...php:171-179]
  → WordPress fires phpmailer_init
      → configure_smtp($phpmailer)                           [...php:141-169]
          → if email+password present: switch PHPMailer to Gmail SMTP/TLS
          → else: return early, WordPress uses default mail
  → PHPMailer delivers via smtp.gmail.com:587
```

### Test email
```
Admin submits Test Email form (options_page)
  → nonce verified (wp_verify_nonce)                         [...php:214]
  → email sanitized + validated (sanitize_email / is_email)  [...php:215-216]
  → wp_mail() called → runs the full send path above         [...php:220]
  → success/error notice rendered inline                     [...php:221-226]
```

## Key Abstractions

- **The shared PHPMailer instance.** The plugin never constructs a mailer; it
  mutates the one WordPress hands it via `phpmailer_init`. This is what makes the
  plugin "transparent" — every `wp_mail()` call site benefits without changes.
- **Single option blob.** All three settings live in one associative array under
  one option key, simplifying load/save.
- **Graceful degradation.** Missing credentials → `configure_smtp` no-ops, so the
  site keeps sending mail (via the default transport) instead of breaking.

## State

The plugin is effectively stateless in PHP memory — every method re-reads
`get_option($this->option_name)` when it needs config (`...php:96, 109, 126, 142,
172, 177`). The single source of truth is the `gws_smtp_settings` row in the
database.
