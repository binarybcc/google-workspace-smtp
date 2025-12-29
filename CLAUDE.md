# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress plugin that enables SMTP email delivery through Google Workspace using app passwords. The plugin provides an admin interface for configuration and includes test email functionality.

## Architecture

**Single-File Plugin Structure:**
- `google-workspace-smtp/google_workspace_smtp.php` - Complete plugin implementation

**Core Class: `GoogleWorkspaceSMTP`**
- Settings management via WordPress Options API
- PHPMailer configuration through `phpmailer_init` hook
- WordPress Settings API integration for admin interface
- Test email functionality with nonce verification

**SMTP Configuration:**
- Host: smtp.gmail.com
- Port: 587 (TLS)
- Authentication: Google App Passwords (16-character tokens)
- Security: TLS with SSL peer verification enabled

## Key Implementation Details

**Settings Storage:**
- Option name: `gws_smtp_settings`
- Fields: `smtp_email`, `smtp_password`, `from_name`
- Password field uses placeholder display when saved (security measure)

**WordPress Hooks Used:**
- `admin_menu` - Adds settings page to WordPress admin
- `admin_init` - Registers settings and fields
- `phpmailer_init` - Configures SMTP before email sending
- `wp_mail_from` - Sets email from address
- `wp_mail_from_name` - Sets email from name
- `admin_notices` - Displays activation notice

**Security Measures:**
- ABSPATH check prevents direct file access
- Email sanitization via `sanitize_email()`
- Text field sanitization via `sanitize_text_field()`
- Nonce verification for test email form
- Capability checks (`manage_options`, `current_user_can`)
- SSL peer verification in SMTP options
- Debug mode only enabled when `WP_DEBUG` is on AND user is admin

## Development Notes

**Password Handling:**
- Password field shows placeholder when saved
- Empty password field on update preserves existing password
- Raw password stored in database (WordPress convention for SMTP plugins)

**Debugging:**
- SMTPDebug level 2 enabled when WP_DEBUG is true and user has manage_options capability
- Check WordPress debug.log for SMTP connection issues

**Localization:**
- Text domain: `google-workspace-smtp`
- All user-facing strings wrapped in translation functions

## WordPress Installation

Since this is a WordPress plugin, it must be installed in a WordPress environment:

1. Place `google-workspace-smtp/` folder in `wp-content/plugins/`
2. Activate through WordPress admin dashboard
3. Configure at Settings → Google Workspace SMTP

## Testing

Test email functionality is built into the admin interface:
- Located in settings page under "Test Email" card
- Requires saved SMTP configuration
- Uses WordPress `wp_mail()` function
- Success/failure notices displayed immediately

## Common Issues

**"Failed to send test email":**
- Verify 2FA is enabled on Google account
- Ensure app password is 16 characters (no spaces)
- Check that SMTP email matches app password account
- Review WordPress debug log if WP_DEBUG is enabled

**Password not saving:**
- Password field intentionally shows placeholder when saved
- Leave blank to keep existing password on update
- Only fill password field when changing credentials
