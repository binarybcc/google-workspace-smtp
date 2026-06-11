---
title: External Integrations
focus: tech
last_mapped_commit: 62e29ef718631beaccba36396ec8eeb0135ee789
last_mapped_date: 2026-06-11
---

# External Integrations

## Overview

The plugin has exactly **one** external integration: the Google Workspace / Gmail
SMTP relay. Everything else (settings storage, email dispatch plumbing) is internal
to WordPress.

## Google Workspace SMTP (Gmail)

This is the heart of the plugin — it reroutes WordPress's outgoing mail through
Google's authenticated SMTP servers.

| Property | Value | Source |
|----------|-------|--------|
| Host | `smtp.gmail.com` | `google-workspace-smtp/google_workspace_smtp.php:149` |
| Port | `587` | `...php:151` |
| Encryption | TLS (`SMTPSecure = 'tls'`) | `...php:152` |
| Auth | Required (`SMTPAuth = true`) | `...php:150` |
| Username | The configured `smtp_email` | `...php:153` |
| Password | A Google **App Password** (16 chars) | `...php:154` |

**SSL hardening** (`...php:157-163`): peer verification is explicitly enabled —
`verify_peer = true`, `verify_peer_name = true`, `allow_self_signed = false`.

**How it connects:** the plugin hooks `phpmailer_init`. Right before WordPress
sends any email, `configure_smtp()` (`...php:141-169`) reconfigures the shared
PHPMailer instance to use Gmail's SMTP. If either the email or password is missing,
it returns early and lets WordPress fall back to default mail
(`...php:144-146`).

### Authentication model

- Uses **Google App Passwords**, not OAuth and not the user's normal password.
- Requires 2-Factor Authentication to be enabled on the Google account before an
  app password can be generated (documented in the in-app setup steps,
  `...php:192-198`, and `README.md`).
- The app password is stored **in plaintext** in the WordPress options table
  (standard practice for SMTP plugins — see `CONCERNS.md`).

## WordPress Mail System

The plugin integrates with WordPress's native mail layer rather than replacing it:

- `wp_mail()` is the dispatch entry point (used by the test-email feature,
  `...php:220`).
- `wp_mail_from` filter → `set_from_email()` overrides the From address
  (`...php:171-174`).
- `wp_mail_from_name` filter → `set_from_name()` overrides the From display name
  (`...php:176-179`).

## Data Stores

- **WordPress options table** (via the Options API) is the only persistence layer.
  Option key: `gws_smtp_settings`. No external database, cache, or queue.

## Webhooks / Inbound

None. The plugin is outbound-only (sending mail). It exposes no REST routes,
webhooks, or external callbacks.

## Integration Inventory

| Integration | Direction | Protocol | Auth | Required |
|-------------|-----------|----------|------|----------|
| Gmail SMTP relay | Outbound | SMTP/TLS:587 | App Password | Yes (for sending) |
| WordPress Options API | Internal | PHP/DB | WP capability | Yes |
| WordPress mail (`wp_mail`/PHPMailer) | Internal | PHP | — | Yes |
