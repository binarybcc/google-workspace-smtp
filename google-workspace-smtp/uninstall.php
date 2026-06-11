<?php
/**
 * Uninstall handler for Google Workspace SMTP.
 *
 * Runs only when the plugin is deleted from the WordPress admin. Removes all
 * data the plugin stored, including the saved SMTP credentials, so the app
 * password is not left behind in the database after removal.
 */

// Bail unless WordPress is genuinely uninstalling this plugin.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove stored settings (email, app password, from name).
delete_option('gws_smtp_settings');

// Remove the one-time activation notice flag, in case it is still set.
delete_option('gws_smtp_activation_notice');
