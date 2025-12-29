# Google Workspace SMTP

A simple WordPress plugin that enables SMTP email delivery through Google Workspace using app passwords.

## Features

- **Easy Configuration**: Simple admin interface for SMTP settings
- **Secure Authentication**: Uses Google App Passwords for authentication
- **Test Email Functionality**: Built-in test email feature to verify configuration
- **WordPress Integration**: Seamlessly integrates with WordPress email system
- **Debug Support**: Optional debug mode when WP_DEBUG is enabled

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- Google Workspace account with 2FA enabled
- Google App Password generated

## Installation

1. Download the plugin files
2. Upload the `google-workspace-smtp` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to Settings → Google Workspace SMTP to configure

## Configuration

### Generating a Google App Password

1. Enable 2-factor authentication on your Google Workspace account
2. Go to your Google Account settings
3. Navigate to Security → 2-Step Verification → App passwords
4. Generate a new app password for "Mail"
5. Copy the 16-character password (without spaces)

### Plugin Settings

In the WordPress admin dashboard:

1. Go to **Settings → Google Workspace SMTP**
2. Enter your configuration:
   - **SMTP Email**: Your Google Workspace email address
   - **App Password**: The 16-character app password
   - **From Name**: The name that appears in sent emails
3. Click **Save Changes**
4. Use the **Test Email** section to verify configuration

## SMTP Details

- **Host**: smtp.gmail.com
- **Port**: 587
- **Encryption**: TLS
- **Authentication**: Required

## Troubleshooting

### Test Email Fails

- Verify 2FA is enabled on your Google account
- Ensure the app password is exactly 16 characters (no spaces)
- Confirm the SMTP email matches the account that generated the app password
- Check WordPress debug.log if WP_DEBUG is enabled

### Emails Not Sending

- Verify settings are saved correctly
- Test with the built-in test email feature
- Enable WP_DEBUG to see detailed SMTP logs
- Check that your hosting allows outbound SMTP connections on port 587

## Security

- Direct file access is prevented
- All inputs are sanitized
- Nonce verification for test emails
- Capability checks require `manage_options` permission
- SSL peer verification enabled
- Passwords stored in WordPress options (standard practice for SMTP plugins)

## Version

Current version: **1.0.0**

## Support

For issues and feature requests, please use the GitHub issue tracker.

## License

This is a custom WordPress plugin. Please refer to WordPress GPL licensing.
