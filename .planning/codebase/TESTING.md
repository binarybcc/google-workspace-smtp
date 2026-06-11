---
title: Testing
focus: quality
last_mapped_commit: 62e29ef718631beaccba36396ec8eeb0135ee789
last_mapped_date: 2026-06-11
---

# Testing

## Current State

**There is no automated test suite.** No PHPUnit, no WP test harness, no CI
workflow, no test files, and no `composer.json` test scripts exist in the repo.

What exists instead is **one manual, in-product verification feature**.

## Manual Test: Built-in Test Email

The only testing mechanism is the "Test Email" card on the plugin's settings page
(`google-workspace-smtp/google_workspace_smtp.php:209-243`).

How it works:
1. Admin enters a recipient address and clicks **Send Test Email**.
2. The handler verifies a nonce (`...php:214`), sanitizes and validates the
   address (`...php:215-216`), then calls `wp_mail()` (`...php:220`).
3. A green success notice or red error notice is rendered inline based on the
   boolean return of `wp_mail()` (`...php:221-226`).

This exercises the real send path end-to-end (sender filters → `phpmailer_init`
→ Gmail SMTP), which is why it's an effective smoke test despite being manual.

## Debugging Aids

- **SMTP debug logging:** when `WP_DEBUG` is enabled **and** the current user has
  `manage_options`, PHPMailer's `SMTPDebug` is set to `2`, surfacing the SMTP
  conversation in the WordPress debug log (`...php:166-168`).
- **Troubleshooting guidance:** both `README.md` and `CLAUDE.md` document the
  common failure modes (2FA not enabled, wrong app-password length, account
  mismatch, blocked port 587) and point to `debug.log`.

## Coverage

| Path | Covered by |
|------|-----------|
| Send pipeline (happy path) | Manual test-email feature |
| Send pipeline (bad config) | Not explicitly tested; `configure_smtp` early-returns |
| Settings save / sanitization | Not tested |
| Field rendering | Not tested |
| Activation notice | Not tested |

No coverage metrics are produced.

## Gaps / Opportunities (if testing is ever added)

- **WordPress lacks a way to unit-test `wp_mail` without sending.** A real suite
  would mock the `wp_mail`/`phpmailer_init` path (e.g. WP_Mock or Brain Monkey) to
  assert that `configure_smtp` sets the right Host/Port/Auth on PHPMailer.
- `sanitize_settings` is pure-ish and would be the easiest unit to test (input
  array → sanitized array), including the deliberate "password not sanitized" case.
- No environment exists in-repo to run such tests; one would need to be added
  (Composer + PHPUnit + a WP test bootstrap).

> For a plugin of this size, the manual test-email feature is a reasonable
> pragmatic choice. Automated tests would mostly pay off if the SMTP-config logic
> grows (multiple providers, OAuth, retry logic, etc.).
