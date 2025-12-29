# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
