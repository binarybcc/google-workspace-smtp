# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2026-06-11

### Added
- `uninstall.php` cleanup handler — deleting the plugin now removes its stored
  settings (including the saved SMTP credentials) from the database instead of
  leaving the app password behind.

### Fixed
- Re-saving the settings page without retyping the App Password no longer
  overwrites the saved password with the masked placeholder (which silently
  broke SMTP authentication). A blank field or the unchanged mask now preserves
  the stored password, matching the documented "leave blank to keep existing
  password" behavior.

### Changed
- The masked password placeholder is now a single shared constant
  (`PASSWORD_MASK`) referenced by both the field renderer and the save logic, so
  the displayed mask and the save-time detection can no longer drift apart.

## [1.0.0] - 2024-12-29

### Added
- Initial release of Google Workspace SMTP plugin
- SMTP configuration through Google Workspace using app passwords
- Admin settings page in WordPress dashboard
- Test email functionality to verify configuration
- Security measures (nonce verification, capability checks, input sanitization)
- Debug mode support when WP_DEBUG is enabled
- From name customization
- TLS encryption on port 587
- SSL peer verification
- WordPress Settings API integration
- Localization support (text domain: google-workspace-smtp)
- CLAUDE.md documentation for AI-assisted development
- Comprehensive README.md with setup instructions

### Security
- Implemented ABSPATH check to prevent direct file access
- Added email and text field sanitization
- Nonce verification for test email submissions
- Capability checks requiring manage_options permission
- SSL peer verification enabled in SMTP options
