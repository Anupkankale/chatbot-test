<?php
/**
 * Plugin Name: YALLO Talent Chatbot
 * Plugin URI: https://yallo.com
 * Description: An intelligent chatbot for YALLO talent acquisition and consultation services with a sleek dark theme interface.
 * Version: 0.5.02.2026
 * Author: Anup
 * Author URI: https://yallo.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yallo-chatbot
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('YALLO_CHATBOT_VERSION', '1.0.0');
define('YALLO_CHATBOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YALLO_CHATBOT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('YALLO_CHATBOT_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class YALLO_Talent_Chatbot {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // Add chatbot to footer
        add_action('wp_footer', array($this, 'render_chatbot'));
        
        // Register AJAX endpoints
        add_action('wp_ajax_yallo_submit_lead', array($this, 'handle_lead_submission'));
        add_action('wp_ajax_nopriv_yallo_submit_lead', array($this, 'handle_lead_submission'));
        
        // Register admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add settings link on plugins page
        add_filter('plugin_action_links_' . YALLO_CHATBOT_PLUGIN_BASENAME, array($this, 'add_settings_link'));
    }
    
    /**
     * Enqueue CSS and JavaScript
     */
    public function enqueue_assets() {
        // Only load on frontend
        if (is_admin()) {
            return;
        }
        
        // Check if chatbot is enabled
        if (!get_option('yallo_chatbot_enabled', true)) {
            return;
        }
        
        // Enqueue styles
        wp_enqueue_style(
            'yallo-chatbot-styles',
            YALLO_CHATBOT_PLUGIN_URL . 'assets/css/chatbot.css',
            array(),
            YALLO_CHATBOT_VERSION
        );
        
        // Enqueue scripts
        wp_enqueue_script(
            'yallo-chatbot-script',
            YALLO_CHATBOT_PLUGIN_URL . 'assets/js/chatbot.js',
            array('jquery'),
            YALLO_CHATBOT_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('yallo-chatbot-script', 'yalloChatbot', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yallo_chatbot_nonce'),
            'autoOpen' => get_option('yallo_chatbot_auto_open', true),
            'scrollTrigger' => get_option('yallo_chatbot_scroll_trigger', 50),
        ));
    }
    
    /**
     * Render chatbot HTML
     */
    public function render_chatbot() {
        // Check if chatbot is enabled
        if (!get_option('yallo_chatbot_enabled', true)) {
            return;
        }
        
        include YALLO_CHATBOT_PLUGIN_DIR . 'templates/chatbot.php';
    }
    
    /**
     * Handle lead submission via AJAX
     */
    public function handle_lead_submission() {
        // Verify nonce
        check_ajax_referer('yallo_chatbot_nonce', 'nonce');
        
        // Sanitize input data
        $lead_data = array(
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'email' => sanitize_email($_POST['email'] ?? ''),
            'company' => sanitize_text_field($_POST['company'] ?? ''),
            'location' => sanitize_text_field($_POST['location'] ?? ''),
            'industry' => sanitize_text_field($_POST['industry'] ?? ''),
            'platforms' => sanitize_text_field($_POST['platforms'] ?? ''),
            'capabilities' => sanitize_text_field($_POST['capabilities'] ?? ''),
            'service_type' => sanitize_text_field($_POST['service_type'] ?? ''),
            'pain' => sanitize_textarea_field($_POST['pain'] ?? ''),
            'initial_intent' => sanitize_text_field($_POST['initial_intent'] ?? ''),
            'lead_type' => sanitize_text_field($_POST['lead_type'] ?? ''),
            'page_url' => esc_url_raw($_POST['page_url'] ?? ''),
            'created_at' => current_time('mysql'),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'ip_address' => $this->get_client_ip(),
        );
        
        // Validate email
        if (!is_email($lead_data['email'])) {
            wp_send_json_error(array('message' => 'Invalid email address'));
            return;
        }
        
        // Save to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'yallo_chatbot_leads';
        
        $inserted = $wpdb->insert(
            $table_name,
            $lead_data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($inserted) {
            // Send email notification
            $this->send_lead_notification($lead_data);
            
            wp_send_json_success(array('message' => 'Lead submitted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to save lead'));
        }
    }
    
    /**
     * Send email notification for new lead
     */
    private function send_lead_notification($lead_data) {
        $admin_email = get_option('yallo_chatbot_notification_email', get_option('admin_email'));
        
        $subject = sprintf('[YALLO Chatbot] New Lead: %s', $lead_data['name']);
        
        $message = "New lead received from YALLO Chatbot:\n\n";
        $message .= "Name: {$lead_data['name']}\n";
        $message .= "Email: {$lead_data['email']}\n";
        $message .= "Company: {$lead_data['company']}\n";
        $message .= "Location: {$lead_data['location']}\n";
        $message .= "Industry: {$lead_data['industry']}\n";
        $message .= "Platforms: {$lead_data['platforms']}\n";
        $message .= "Capabilities: {$lead_data['capabilities']}\n";
        $message .= "Service Type: {$lead_data['service_type']}\n";
        $message .= "Pain Point: {$lead_data['pain']}\n";
        $message .= "Initial Intent: {$lead_data['initial_intent']}\n";
        $message .= "Lead Type: {$lead_data['lead_type']}\n";
        $message .= "Page URL: {$lead_data['page_url']}\n";
        $message .= "Submitted: {$lead_data['created_at']}\n";
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        wp_mail($admin_email, $subject, $message, $headers);
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'YALLO Chatbot',
            'YALLO Chatbot',
            'manage_options',
            'yallo-chatbot',
            array($this, 'render_admin_page'),
            'dashicons-format-chat',
            30
        );
        
        add_submenu_page(
            'yallo-chatbot',
            'Settings',
            'Settings',
            'manage_options',
            'yallo-chatbot',
            array($this, 'render_admin_page')
        );
        
        add_submenu_page(
            'yallo-chatbot',
            'Leads',
            'Leads',
            'manage_options',
            'yallo-chatbot-leads',
            array($this, 'render_leads_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('yallo_chatbot_settings', 'yallo_chatbot_enabled');
        register_setting('yallo_chatbot_settings', 'yallo_chatbot_auto_open');
        register_setting('yallo_chatbot_settings', 'yallo_chatbot_scroll_trigger');
        register_setting('yallo_chatbot_settings', 'yallo_chatbot_notification_email');
    }
    
    /**
     * Render admin settings page
     */
    public function render_admin_page() {
        include YALLO_CHATBOT_PLUGIN_DIR . 'admin/settings.php';
    }
    
    /**
     * Render leads page
     */
    public function render_leads_page() {
        include YALLO_CHATBOT_PLUGIN_DIR . 'admin/leads.php';
    }
    
    /**
     * Add settings link on plugins page
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=yallo-chatbot') . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

/**
 * Plugin activation
 */
function yallo_chatbot_activate() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yallo_chatbot_leads';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        company varchar(255) DEFAULT '',
        location varchar(255) DEFAULT '',
        industry varchar(255) DEFAULT '',
        platforms varchar(255) DEFAULT '',
        capabilities varchar(255) DEFAULT '',
        service_type varchar(255) DEFAULT '',
        pain text DEFAULT '',
        initial_intent varchar(255) DEFAULT '',
        lead_type varchar(50) DEFAULT '',
        page_url varchar(500) DEFAULT '',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        user_agent varchar(500) DEFAULT '',
        ip_address varchar(100) DEFAULT '',
        PRIMARY KEY  (id),
        KEY email (email),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Set default options
    add_option('yallo_chatbot_enabled', true);
    add_option('yallo_chatbot_auto_open', true);
    add_option('yallo_chatbot_scroll_trigger', 50);
    add_option('yallo_chatbot_notification_email', get_option('admin_email'));
}
register_activation_hook(__FILE__, 'yallo_chatbot_activate');

/**
 * Plugin deactivation
 */
function yallo_chatbot_deactivate() {
    // Clean up if needed
}
register_deactivation_hook(__FILE__, 'yallo_chatbot_deactivate');

/**
 * Initialize the plugin
 */
function yallo_chatbot_init() {
    return YALLO_Talent_Chatbot::get_instance();
}

// Start the plugin
yallo_chatbot_init();
