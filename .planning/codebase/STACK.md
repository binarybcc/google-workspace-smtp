---
title: Technology Stack
focus: tech
last_mapped_commit: 62e29ef718631beaccba36396ec8eeb0135ee789
last_mapped_date: 2026-06-11
---

# Technology Stack

## Overview

A single-file WordPress plugin that routes outgoing WordPress email through Google
Workspace's SMTP servers using a Google App Password. No build step, no package
manager, no external libraries beyond what WordPress already ships.

## Languages & Runtime

| Layer | Technology | Notes |
|-------|-----------|-------|
| Language | PHP | Requires PHP **7.0+** (per `README.md`) |
| Host platform | WordPress | Requires WordPress **5.0+** |
| Mail transport | PHPMailer | Bundled with WordPress core — not a separate dependency |

## Frameworks & Platform APIs

The plugin is built entirely on WordPress core APIs — there is no third-party framework.

- **WordPress Settings API** — registers settings, sections, and fields
  (`register_setting`, `add_settings_section`, `add_settings_field`) in
  `google-workspace-smtp/google_workspace_smtp.php:37-74`
- **WordPress Options API** — persists configuration under a single option key
  (`get_option` / `register_setting` with `gws_smtp_settings`)
- **WordPress Plugin API (hooks)** — actions and filters wire the plugin into
  WordPress (`google-workspace-smtp/google_workspace_smtp.php:19-25`)
- **PHPMailer** — configured via the `phpmailer_init` hook
  (`google-workspace-smtp/google_workspace_smtp.php:141-169`)

## Dependencies

**None to install.** There is no `composer.json`, `package.json`, `vendor/`, or
lockfile. Every function used (`add_action`, `get_option`, `wp_mail`,
`sanitize_email`, `wp_verify_nonce`, etc.) is provided by WordPress core at runtime.

`.gitignore` does pre-emptively ignore `/vendor/`, `composer.phar`, and
`node_modules/`, but none of those toolchains are currently in use.

## Configuration

The plugin stores all of its configuration in the WordPress database, not in files.

| Item | Value / Location |
|------|------------------|
| Option key | `gws_smtp_settings` (`...php:17`) |
| Stored fields | `smtp_email`, `smtp_password`, `from_name` |
| SMTP host | `smtp.gmail.com` (hardcoded, `...php:149`) |
| SMTP port | `587` (hardcoded, `...php:151`) |
| Encryption | `tls` (hardcoded, `...php:152`) |
| Debug toggle | Driven by the `WP_DEBUG` constant + `manage_options` capability (`...php:166`) |

There are no environment variables and no `.env` file — WordPress plugins read
config from the database via the Options API rather than from the filesystem.

## Build & Tooling

- **Build step:** none. The PHP file runs directly inside WordPress.
- **Distribution:** a zipped copy of the plugin folder (`google-workspace-smtp.zip`)
  is committed at the repo root for manual upload. `*.zip` is in `.gitignore`,
  so the committed zip was force-added or added before the rule.
- **Linting / formatting:** no config files present (no `.phpcs.xml`, `.editorconfig`,
  etc.).

## Versioning

- Plugin version declared in the file header: `Version: 1.0.0` (`...php:5`)
- Tracked in `CHANGELOG.md` (Keep a Changelog format, SemVer)
- Current release: **1.0.0** (dated 2024-12-29 in the changelog)
