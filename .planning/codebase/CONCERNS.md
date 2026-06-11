---
title: Concerns & Technical Debt
focus: concerns
last_mapped_commit: 62e29ef718631beaccba36396ec8eeb0135ee789
last_mapped_date: 2026-06-11
---

# Concerns & Technical Debt

This is a small, generally clean plugin. The items below are ordered by impact.
File references are to `google-workspace-smtp/google_workspace_smtp.php`.

## 🔴 HIGH — Re-saving settings can wipe out the saved password

**What happens:** When a password is saved, the field is pre-filled with bullet
characters as its actual value:

```php
value='<?php echo $has_password ? '••••••••••••••••' : ''; ?>'   // line 114
```

But `sanitize_settings()` stores whatever was submitted, with no check for the
placeholder and no "keep existing if blank" logic:

```php
if (isset($input['smtp_password'])) {
    $sanitized['smtp_password'] = $input['smtp_password'];   // lines 83-86
}
```

So if an admin edits the email or From Name and clicks **Save** *without* retyping
the password, the literal string `••••••••••••••••` is saved as the password.
On the next email, `configure_smtp` authenticates with the bullet string → Gmail
rejects it → mail silently fails.

**Why it matters:** This breaks the plugin's one job after any routine settings
edit, and the failure is non-obvious. It also contradicts the documented behavior
in both `README.md` and `CLAUDE.md` ("Leave blank to keep existing password").

**Suggested fix:** In `sanitize_settings`, treat the placeholder (and/or an empty
submission) as "no change" and fall back to the stored password:
```php
$existing = get_option($this->option_name, array());
$pw = isset($input['smtp_password']) ? $input['smtp_password'] : '';
if ($pw === '' || $pw === '••••••••••••••••') {
    $sanitized['smtp_password'] = isset($existing['smtp_password']) ? $existing['smtp_password'] : '';
} else {
    $sanitized['smtp_password'] = $pw;
}
```

## 🟠 MEDIUM — App password stored in plaintext in the database

`sanitize_settings` deliberately does not transform the password (`...php:83-86`),
and `configure_smtp` reads it back as-is (`...php:154`). It lives in plaintext in
the `wp_options` table under `gws_smtp_settings`.

This is the *de facto* standard for WordPress SMTP plugins (SMTP needs the cleartext
secret to authenticate, and WordPress has no built-in secret store), and it is
called out as accepted in `README.md`/`CLAUDE.md`. Still worth knowing:

- Anyone with database read access, a DB backup, or admin-level access to the
  options screen can recover the Google app password.
- **Mitigations to consider:** store the secret in a constant in `wp-config.php`
  instead of the DB, or encrypt at rest with a key kept outside the DB. Either is a
  larger change and arguably out of scope for v1.

## 🟡 LOW — No `uninstall.php`; secrets persist after deletion

Deleting the plugin leaves `gws_smtp_settings` (including the plaintext app
password) in the database. Adding an `uninstall.php` that calls
`delete_option('gws_smtp_settings')` would clean up on uninstall.

## 🟡 LOW — Hardcoded transport; Gmail-only

Host, port, and encryption are hardcoded to `smtp.gmail.com` / `587` / `tls`
(`...php:149-152`). That's fine for the stated purpose, but it means no support for
port 465 (implicit SSL), no other providers, and no per-site override. Acceptable
as long as the plugin stays Gmail-specific.

## 🟢 INFO — Repo hygiene

- `google-workspace-smtp.zip` exists at the repo root as a loose, gitignored
  build artifact (it is **not** tracked in git). It can still drift from source
  and be distributed stale, so it should be regenerated from the current code at
  release time rather than left lying around. (Verified 2026-06-11: never
  committed to history.)
- `.DS_Store` is present in the working tree (matched by `.gitignore`, so not
  tracked — just noting it appears locally).

## 🟢 INFO — No automated tests / CI

Verification is the manual in-app "Test Email" feature only (see `TESTING.md`).
Reasonable for the size; flagged so it's a conscious choice, not an oversight.

## Things that are actually fine (verified)

To avoid false alarms in future reviews, these were checked and are **not** issues:

- **Output escaping** in field renderers: the `value` echoes at `...php:101, 131`
  print variables that were `esc_attr()`-encoded a line earlier
  (`...php:97, 127`), so they are properly escaped.
- **CSRF on the test form:** nonce-protected via `wp_nonce_field` /
  `wp_verify_nonce` (`...php:214, 232`).
- **Capability checks:** settings page and menu require `manage_options`
  (`...php:31, 183`); debug output is additionally gated on the capability
  (`...php:166`).
- **Direct-access guard:** `ABSPATH` check at the top of the file (`...php:11-13`).
- **SSL verification:** peer verification is explicitly enabled, not disabled
  (`...php:157-163`).
