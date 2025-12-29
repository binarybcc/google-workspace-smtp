<?php
/**
 * Plugin Name: Google Workspace SMTP
 * Description: Simple SMTP plugin for Google Workspace using app passwords
 * Version: 1.0.0
 * Author: Custom Plugin
 * Text Domain: google-workspace-smtp
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class GoogleWorkspaceSMTP {
    
    private $option_name = 'gws_smtp_settings';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('phpmailer_init', array($this, 'configure_smtp'));
        add_filter('wp_mail_from', array($this, 'set_from_email'));
        add_filter('wp_mail_from_name', array($this, 'set_from_name'));
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Google Workspace SMTP', 'google-workspace-smtp'),
            __('Google Workspace SMTP', 'google-workspace-smtp'),
            'manage_options',
            'google-workspace-smtp',
            array($this, 'options_page')
        );
    }
    
    public function settings_init() {
        register_setting(
            'google_workspace_smtp', 
            $this->option_name,
            array($this, 'sanitize_settings')
        );
        
        add_settings_section(
            'gws_smtp_section',
            __('SMTP Configuration', 'google-workspace-smtp'),
            array($this, 'settings_section_callback'),
            'google_workspace_smtp'
        );
        
        add_settings_field(
            'smtp_email',
            __('Email Address', 'google-workspace-smtp'),
            array($this, 'smtp_email_render'),
            'google_workspace_smtp',
            'gws_smtp_section'
        );
        
        add_settings_field(
            'smtp_password',
            __('App Password', 'google-workspace-smtp'),
            array($this, 'smtp_password_render'),
            'google_workspace_smtp',
            'gws_smtp_section'
        );
        
        add_settings_field(
            'from_name',
            __('From Name', 'google-workspace-smtp'),
            array($this, 'from_name_render'),
            'google_workspace_smtp',
            'gws_smtp_section'
        );
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['smtp_email'])) {
            $sanitized['smtp_email'] = sanitize_email($input['smtp_email']);
        }
        
        if (isset($input['smtp_password'])) {
            // Don't sanitize password as it may contain special characters
            $sanitized['smtp_password'] = $input['smtp_password'];
        }
        
        if (isset($input['from_name'])) {
            $sanitized['from_name'] = sanitize_text_field($input['from_name']);
        }
        
        return $sanitized;
    }
    
    public function smtp_email_render() {
        $options = get_option($this->option_name, array());
        $value = isset($options['smtp_email']) ? esc_attr($options['smtp_email']) : '';
        ?>
        <input type='email' 
               name='<?php echo esc_attr($this->option_name); ?>[smtp_email]' 
               value='<?php echo $value; ?>' 
               style='width: 300px;' 
               required>
        <p class="description"><?php _e('Your Google Workspace email address', 'google-workspace-smtp'); ?></p>
        <?php
    }
    
    public function smtp_password_render() {
        $options = get_option($this->option_name, array());
        $has_password = !empty($options['smtp_password']);
        ?>
        <input type='password' 
               name='<?php echo esc_attr($this->option_name); ?>[smtp_password]' 
               value='<?php echo $has_password ? '••••••••••••••••' : ''; ?>' 
               style='width: 300px;' 
               placeholder="<?php echo $has_password ? __('Password saved', 'google-workspace-smtp') : __('Enter app password', 'google-workspace-smtp'); ?>"
               <?php echo $has_password ? '' : 'required'; ?>>
        <p class="description"><?php _e('Your Google App Password (16 characters, not your regular password)', 'google-workspace-smtp'); ?></p>
        <?php if ($has_password): ?>
        <p class="description"><small><?php _e('Leave blank to keep existing password', 'google-workspace-smtp'); ?></small></p>
        <?php endif; ?>
        <?php
    }
    
    public function from_name_render() {
        $options = get_option($this->option_name, array());
        $value = isset($options['from_name']) ? esc_attr($options['from_name']) : get_bloginfo('name');
        ?>
        <input type='text' 
               name='<?php echo esc_attr($this->option_name); ?>[from_name]' 
               value='<?php echo $value; ?>' 
               style='width: 300px;'>
        <p class="description"><?php _e('Name to appear in the "From" field', 'google-workspace-smtp'); ?></p>
        <?php
    }
    
    public function settings_section_callback() {
        echo '<p>' . __('Configure your Google Workspace SMTP settings below. You\'ll need to create an App Password in your Google Account settings.', 'google-workspace-smtp') . '</p>';
    }
    
    public function configure_smtp($phpmailer) {
        $options = get_option($this->option_name, array());
        
        if (empty($options['smtp_email']) || empty($options['smtp_password'])) {
            return;
        }
        
        $phpmailer->isSMTP();
        $phpmailer->Host = 'smtp.gmail.com';
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = 587;
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->Username = $options['smtp_email'];
        $phpmailer->Password = $options['smtp_password'];
        
        // Set additional security options
        $phpmailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            )
        );
        
        // Enable debugging only for administrators and when WP_DEBUG is on
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
            $phpmailer->SMTPDebug = 2;
        }
    }
    
    public function set_from_email($original_email_address) {
        $options = get_option($this->option_name, array());
        return !empty($options['smtp_email']) ? $options['smtp_email'] : $original_email_address;
    }
    
    public function set_from_name($original_email_from) {
        $options = get_option($this->option_name, array());
        return !empty($options['from_name']) ? $options['from_name'] : $original_email_from;
    }
    
    public function options_page() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Google Workspace SMTP Settings', 'google-workspace-smtp'); ?></h1>
            
            <div class="notice notice-info">
                <p><strong><?php _e('Setup Instructions:', 'google-workspace-smtp'); ?></strong></p>
                <ol>
                    <li><?php _e('Go to your Google Account settings', 'google-workspace-smtp'); ?></li>
                    <li><?php _e('Enable 2-factor authentication if not already enabled', 'google-workspace-smtp'); ?></li>
                    <li><?php _e('Go to Security → App passwords', 'google-workspace-smtp'); ?></li>
                    <li><?php _e('Generate a new app password for "Mail"', 'google-workspace-smtp'); ?></li>
                    <li><?php _e('Use that 16-character password below (not your regular Google password)', 'google-workspace-smtp'); ?></li>
                </ol>
            </div>
            
            <form action='options.php' method='post'>
                <?php
                settings_fields('google_workspace_smtp');
                do_settings_sections('google_workspace_smtp');
                submit_button();
                ?>
            </form>
            
            <div class="card" style="margin-top: 20px;">
                <h3><?php _e('Test Email', 'google-workspace-smtp'); ?></h3>
                <p><?php _e('Send a test email to verify your settings:', 'google-workspace-smtp'); ?></p>
                
                <?php
                if (isset($_POST['send_test_email']) && wp_verify_nonce($_POST['test_email_nonce'], 'send_test_email')) {
                    $test_email = sanitize_email($_POST['test_email']);
                    if ($test_email && is_email($test_email)) {
                        $subject = sprintf(__('Test Email from %s', 'google-workspace-smtp'), get_bloginfo('name'));
                        $message = __('This is a test email sent from your WordPress site using Google Workspace SMTP.', 'google-workspace-smtp');
                        
                        if (wp_mail($test_email, $subject, $message)) {
                            echo '<div class="notice notice-success"><p>' . sprintf(__('Test email sent successfully to %s', 'google-workspace-smtp'), esc_html($test_email)) . '</p></div>';
                        } else {
                            echo '<div class="notice notice-error"><p>' . __('Failed to send test email. Please check your settings and try again.', 'google-workspace-smtp') . '</p></div>';
                        }
                    } else {
                        echo '<div class="notice notice-error"><p>' . __('Please enter a valid email address.', 'google-workspace-smtp') . '</p></div>';
                    }
                }
                ?>
                
                <form method="post">
                    <?php wp_nonce_field('send_test_email', 'test_email_nonce'); ?>
                    <input type="email" 
                           name="test_email" 
                           placeholder="<?php esc_attr_e('Enter email address', 'google-workspace-smtp'); ?>" 
                           required 
                           style="width: 300px;">
                    <input type="submit" 
                           name="send_test_email" 
                           value="<?php esc_attr_e('Send Test Email', 'google-workspace-smtp'); ?>" 
                           class="button">
                </form>
            </div>
        </div>
        <?php
    }
}

// Initialize the plugin
new GoogleWorkspaceSMTP();

// Activation hook
register_activation_hook(__FILE__, 'gws_smtp_activation_notice');

function gws_smtp_activation_notice() {
    add_option('gws_smtp_activation_notice', true);
}

add_action('admin_notices', 'gws_smtp_display_activation_notice');

function gws_smtp_display_activation_notice() {
    if (get_option('gws_smtp_activation_notice')) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php printf(__('Google Workspace SMTP plugin activated! Please configure your settings in <a href="%s">Settings → Google Workspace SMTP</a>', 'google-workspace-smtp'), admin_url('options-general.php?page=google-workspace-smtp')); ?></p>
        </div>
        <?php
        delete_option('gws_smtp_activation_notice');
    }
}
?>