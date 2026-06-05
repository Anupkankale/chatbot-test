<?php
/**
 * Plugin Name: DevXpert Chatbot
 * Plugin URI: https://devxpert.com
 * Update URI: https://devxpert.com/plugins/devxpert-chatbot
 * Description: An intelligent chatbot for DevXpert talent acquisition and consultation services with a sleek dark theme interface and Claude AI integration.
 * Version: 1.0.6
 * Author: Anup
 * Author URI: https://anupkankale.com
   
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: devxpert-chatbot
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DEVXPERT_CHATBOT_VERSION', '1.1.0');
define('DEVXPERT_CHATBOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DEVXPERT_CHATBOT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DEVXPERT_CHATBOT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load RAG AI handler
if (file_exists(DEVXPERT_CHATBOT_PLUGIN_DIR . 'devxpert-chatbot-rag-ai.php')) {
    require_once DEVXPERT_CHATBOT_PLUGIN_DIR . 'devxpert-chatbot-rag-ai.php';
}

/**
 * Main Plugin Class
 */
class DEVXPERT_Talent_Chatbot {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Return the configured brand name.
     */
    private function get_brand_name() {
        return get_option('devxpert_brand_name', 'DevXpert');
    }

    /**
     * Return the configured chatbot title.
     */
    private function get_chatbot_title() {
        return get_option('devxpert_chatbot_title', $this->get_brand_name() . ' Digital Project Assistant');
    }

    /**
     * Return the configured chatbot subtitle.
     */
    private function get_chatbot_subtitle() {
        return get_option('devxpert_chatbot_subtitle', 'Ask about custom websites, e-commerce, or digital optimization.');
    }

    /**
     * Return the configured brand accent color.
     */
    private function get_brand_accent_color() {
        $color = get_option('devxpert_brand_accent_color', '#BFA25E');
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#BFA25E';
    }

    /**
     * Return inline brand CSS variables for plugin frontends.
     */
    private function get_frontend_brand_css() {
        $accent = $this->get_brand_accent_color();

        return "
        .devxpert-chatbot-wrapper,
        .devxpert-newsletter-overlay {
            --color-brand-blue: {$accent};
            --devxpert-accent: {$accent};
            font-family: var(--font-body);
        }";
    }

    /**
     * Return inline brand CSS variables for plugin admin pages.
     */
    private function get_admin_brand_css() {
        $accent = $this->get_brand_accent_color();

        return "
        .devxpert-admin {
            --color-brand-blue: {$accent};
            --dx-accent: {$accent};
            font-family: var(--font-body);
        }";
    }

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
        // Migrate legacy YALLO keys/tables before settings/pages read them.
        add_action('admin_init', array($this, 'maybe_migrate_yallo_data'), 1);

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

        // REST endpoints for external chat channels
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // Add chatbot to footer
        add_action('wp_footer', array($this, 'render_chatbot'));
        
        // Register AJAX endpoints
        add_action('wp_ajax_devxpert_submit_lead', array($this, 'handle_lead_submission'));
        add_action('wp_ajax_nopriv_devxpert_submit_lead', array($this, 'handle_lead_submission'));
        
        // Register lead update AJAX endpoint (for early lead capture)
        add_action('wp_ajax_devxpert_update_lead', array($this, 'handle_lead_update'));
        add_action('wp_ajax_nopriv_devxpert_update_lead', array($this, 'handle_lead_update'));
        
        // Load chatbot questions dynamically
        add_action('wp_ajax_devxpert_get_questions', array($this, 'get_chatbot_questions'));
        add_action('wp_ajax_nopriv_devxpert_get_questions', array($this, 'get_chatbot_questions'));
        
        // Register newsletter AJAX endpoints
        add_action('wp_ajax_devxpert_newsletter_subscribe', array($this, 'handle_newsletter_subscription'));
        add_action('wp_ajax_nopriv_devxpert_newsletter_subscribe', array($this, 'handle_newsletter_subscription'));
        
        // AI AJAX endpoints
        add_action('wp_ajax_devxpert_ai_chat', array($this, 'handle_ai_chat'));
        add_action('wp_ajax_nopriv_devxpert_ai_chat', array($this, 'handle_ai_chat'));
        add_action('wp_ajax_devxpert_test_ai', array($this, 'handle_test_ai'));
        
        // Register admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // Admin assets for plugin pages
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add settings link on plugins page
        add_filter('plugin_action_links_' . DEVXPERT_CHATBOT_PLUGIN_BASENAME, array($this, 'add_settings_link'));

        // Hook into WordPress mailer to apply SMTP settings
        add_action('phpmailer_init', array($this, 'configure_smtp'));

        // Simple API test
        add_action('wp_ajax_devxpert_test_api_simple', array($this, 'handle_test_api_simple'));

        // SMTP test email
        add_action('wp_ajax_devxpert_smtp_test', array($this, 'handle_smtp_test'));

        // Lead status update
        add_action('wp_ajax_devxpert_update_lead_status', array($this, 'handle_update_lead_status'));

        // CSV export (admin_post: non-AJAX file download)
        add_action('admin_post_devxpert_export_leads', array($this, 'handle_export_leads'));

        // DB upgrade check
        add_action('admin_init', array($this, 'maybe_upgrade_db'));
    }
    
    /**
     * Enqueue admin-only assets for this plugin's pages.
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'devxpert-chatbot') === false) {
            return;
        }

        wp_enqueue_style(
            'devxpert-google-fonts',
            'https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Sora:wght@100..800&display=swap',
            array(),
            null
        );

        wp_enqueue_style(
            'devxpert-chatbot-admin',
            DEVXPERT_CHATBOT_PLUGIN_URL . 'assets/css/admin.css',
            array('devxpert-google-fonts'),
            DEVXPERT_CHATBOT_VERSION
        );

        wp_add_inline_style('devxpert-chatbot-admin', $this->get_admin_brand_css());
    }

    /**
     * Enqueue CSS and JavaScript
     */
    public function enqueue_assets() {
        // Only load on frontend
        if (is_admin()) {
            return;
        }

        $version = DEVXPERT_CHATBOT_VERSION;

        // Load Fonts for both Newsletter and Chatbot
        wp_enqueue_style(
            'devxpert-google-fonts',
            'https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Sora:wght@100..800&display=swap',
            array(),
            null
        );

        // ─────────────────────────────────────────────────────────
        // NEWSLETTER: Loads independently — NOT affected by chatbot toggle
        // ─────────────────────────────────────────────────────────
        if (get_option('devxpert_newsletter_enabled', false)) {
            wp_enqueue_style(
                'devxpert-newsletter-styles',
                DEVXPERT_CHATBOT_PLUGIN_URL . 'assets/css/newsletter.css',
                array('devxpert-google-fonts'),
                $version
            );

            wp_enqueue_script(
                'devxpert-newsletter-script',
                DEVXPERT_CHATBOT_PLUGIN_URL . 'assets/js/newsletter.js',
                array('jquery'),
                $version,
                true
            );

            wp_localize_script('devxpert-newsletter-script', 'devxpertNewsletter', array(
                'ajaxUrl'   => admin_url('admin-ajax.php'),
                'nonce'     => wp_create_nonce('devxpert_chatbot_nonce'),
                'delay'     => get_option('devxpert_newsletter_delay', 5000),
                'showOnce'  => get_option('devxpert_newsletter_show_once', true),
                'brandName' => $this->get_brand_name(),
            ));

            wp_add_inline_style('devxpert-newsletter-styles', $this->get_frontend_brand_css());
        }

        // ─────────────────────────────────────────────────────────
        // CHATBOT: Has its own enable/page restriction checks
        // ─────────────────────────────────────────────────────────
        if (!get_option('devxpert_chatbot_enabled', true)) {
            return;
        }

        if (!$this->should_display_on_current_page()) {
            return;
        }

        // Enqueue chatbot styles
        wp_enqueue_style(
            'devxpert-chatbot-styles',
            DEVXPERT_CHATBOT_PLUGIN_URL . 'assets/css/chatbot.css',
            array(),
            $version
        );

        // Enqueue chatbot scripts
        wp_enqueue_script(
            'devxpert-chatbot-script',
            DEVXPERT_CHATBOT_PLUGIN_URL . 'assets/js/chatbot.js',
            array('jquery'),
            $version,
            true
        );

        // Localize chatbot script
        wp_localize_script('devxpert-chatbot-script', 'devxpertChatbot', array(
            'ajaxUrl'       => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('devxpert_chatbot_nonce'),
            'autoOpen'      => get_option('devxpert_chatbot_auto_open', true),
            'scrollTrigger' => get_option('devxpert_chatbot_scroll_trigger', 50),
            'displayMode'   => get_option('devxpert_chatbot_display_mode', 'all_pages'),
            'currentUrl'    => home_url($_SERVER['REQUEST_URI']),
            'debug'         => defined('WP_DEBUG') && WP_DEBUG,
            'aiEnabled'     => get_option('devxpert_claude_api_key') ? true : false,
            'brandName'     => $this->get_brand_name(),
            'chatTitle'     => $this->get_chatbot_title(),
            'chatSubtitle'  => $this->get_chatbot_subtitle(),
        ));

        wp_add_inline_style('devxpert-chatbot-styles', $this->get_frontend_brand_css());
    }
    
    /**
     * Check if chatbot should display on current page
     */
    private function should_display_on_current_page() {
        $display_mode = get_option('devxpert_chatbot_display_mode', 'all_pages');
        
        // Display on all pages
        if ($display_mode === 'all_pages') {
            return true;
        }
        
        // Display on homepage only
        if ($display_mode === 'homepage_only') {
            return is_front_page() || is_home();
        }
        
        // Display on specific pages only
        if ($display_mode === 'specific_pages') {
            $specific_pages = get_option('devxpert_chatbot_specific_pages', '');
            
            if (empty($specific_pages)) {
                return false;
            }
            
            // Get current page information
            global $post;
            $current_url = home_url($_SERVER['REQUEST_URI']);
            $current_path = parse_url($current_url, PHP_URL_PATH);
            
            // Clean and parse the specific pages
            $pages_array = array_filter(array_map('trim', explode("\n", $specific_pages)));
            
            foreach ($pages_array as $page_rule) {
                if (empty($page_rule)) {
                    continue;
                }
                
                // Check for page ID (format: id:123)
                if (strpos($page_rule, 'id:') === 0) {
                    $page_id = intval(str_replace('id:', '', $page_rule));
                    if ($post && ($post->ID == $page_id || is_page($page_id) || is_single($page_id))) {
                        return true;
                    }
                }
                // Check for slug (format: slug:contact-us)
                elseif (strpos($page_rule, 'slug:') === 0) {
                    $slug = str_replace('slug:', '', $page_rule);
                    if ($post && $post->post_name == $slug) {
                        return true;
                    }
                    if (is_page($slug) || is_single($slug)) {
                        return true;
                    }
                }
                // Check for URL match (full or partial)
                else {
                    // Clean the rule for comparison
                    $clean_rule = preg_replace('#^https?://(www\.)?#', '', trim($page_rule, '/'));
                    $clean_rule = '/' . ltrim($clean_rule, '/');
                    
                    // Clean current path
                    $clean_current = rtrim($current_path, '/');
                    if (empty($clean_current)) {
                        $clean_current = '/';
                    }
                    
                    // Exact match
                    if ($clean_current === $clean_rule) {
                        return true;
                    }
                    
                    // Contains match (for wildcards)
                    if (strpos($clean_current, $clean_rule) !== false) {
                        return true;
                    }
                    
                    // Match without leading slash
                    if (strpos($clean_current, '/' . trim($clean_rule, '/')) !== false) {
                        return true;
                    }
                }
            }
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Render chatbot HTML
     */
    public function render_chatbot() {

        // ─────────────────────────────────────────────────────────
        // NEWSLETTER: Renders independently — NOT affected by chatbot toggle
        // ─────────────────────────────────────────────────────────
        if (get_option('devxpert_newsletter_enabled', false)) {
            echo '<!-- DevXpert Newsletter: Active -->';
            include DEVXPERT_CHATBOT_PLUGIN_DIR . 'templates/newsletter-popup.php';
        }

        // ─────────────────────────────────────────────────────────
        // CHATBOT: Has its own enable/page restriction checks
        // ─────────────────────────────────────────────────────────
        if (!get_option('devxpert_chatbot_enabled', true)) {
            echo '<!-- DevXpert Chatbot: Disabled in settings -->';
            return;
        }

        if (!$this->should_display_on_current_page()) {
            $display_mode = get_option('devxpert_chatbot_display_mode', 'all_pages');
            $current_url  = home_url($_SERVER['REQUEST_URI']);
            echo '<!-- DevXpert Chatbot: Not displayed on this page (Mode: ' . esc_html($display_mode) . ', URL: ' . esc_html($current_url) . ') -->';
            return;
        }

        echo '<!-- DevXpert Chatbot: Active -->';
        include DEVXPERT_CHATBOT_PLUGIN_DIR . 'templates/chatbot.php';
    }
    
    /**
     * Handle lead submission via AJAX
     */
    public function handle_lead_submission() {
        // Verify nonce
        check_ajax_referer('devxpert_chatbot_nonce', 'nonce');
        
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
        $table_name = $wpdb->prefix . 'devxpert_chatbot_leads';

        // Duplicate detection: return existing lead ID without re-inserting
        $existing_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE email = %s LIMIT 1",
            $lead_data['email']
        ));

        if ($existing_id) {
            wp_send_json_success(array('message' => 'Lead already exists', 'lead_id' => (int) $existing_id));
            return;
        }

        $inserted = $wpdb->insert(
            $table_name,
            $lead_data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($inserted) {
            // Send email notification
            $this->send_lead_notification($lead_data);

            wp_send_json_success(array('message' => 'Lead submitted successfully', 'lead_id' => (int) $wpdb->insert_id));
        } else {
            wp_send_json_error(array('message' => 'Failed to save lead'));
        }
    }
    
    /**
     * Configure SMTP via phpmailer_init hook
     */
    public function configure_smtp( $phpmailer ) {
        // Only apply if SMTP is enabled
        if ( ! get_option( 'devxpert_smtp_enabled', false ) ) {
            return;
        }

        $host       = get_option( 'devxpert_smtp_host', '' );
        $port       = (int) get_option( 'devxpert_smtp_port', 587 );
        $username   = get_option( 'devxpert_smtp_username', '' );
        $password   = get_option( 'devxpert_smtp_password', '' );
        $encryption = get_option( 'devxpert_smtp_encryption', 'tls' );
        $from_email = get_option( 'devxpert_smtp_from_email', get_option( 'admin_email' ) );
        $from_name  = get_option( 'devxpert_smtp_from_name', get_bloginfo( 'name' ) );

        if ( empty( $host ) || empty( $username ) || empty( $password ) ) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host        = $host;
        $phpmailer->SMTPAuth    = true;
        $phpmailer->Username    = $username;
        $phpmailer->Password    = $password;
        $phpmailer->SMTPSecure  = $encryption;
        $phpmailer->Port        = $port;
        $phpmailer->From        = $from_email;
        $phpmailer->FromName    = $from_name;
        $phpmailer->CharSet     = 'UTF-8';
    }

    /**
     * Central method to send all plugin emails
     */
    private function send_email( $to, $subject, $html_body ) {
        // Support comma-separated multiple recipients
        $recipients = array_map( 'trim', explode( ',', $to ) );

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
        );

        $from_name  = get_option( 'devxpert_smtp_from_name', get_bloginfo( 'name' ) );
        $from_email = get_option( 'devxpert_smtp_from_email', get_option( 'admin_email' ) );
        $headers[]  = "From: {$from_name} <{$from_email}>";

        foreach ( $recipients as $recipient ) {
            if ( is_email( $recipient ) ) {
                wp_mail( $recipient, $subject, $html_body, $headers );
            }
        }
    }

    /**
     * Build a branded HTML email template
     */
    private function build_email_html( $title, $rows, $footer_note = '' ) {
        $logo_color  = $this->get_brand_accent_color();
        $brand_name  = esc_html($this->get_brand_name());
        $chat_title  = esc_html($this->get_chatbot_title());
        $rows_html   = '';

        foreach ( $rows as $label => $value ) {
            $value      = esc_html( $value ?: '—' );
            $label      = esc_html( $label );
            $rows_html .= "
            <tr>
                <td style='padding:10px 15px;font-weight:600;color:#555;width:35%;border-bottom:1px solid #f0f0f0;'>{$label}</td>
                <td style='padding:10px 15px;color:#222;border-bottom:1px solid #f0f0f0;'>{$value}</td>
            </tr>";
        }

        $footer_html = $footer_note
            ? "<p style='font-size:12px;color:#999;margin-top:30px;'>{$footer_note}</p>"
            : '';

        return "<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
<body style='margin:0;padding:0;background:#f4f4f4;font-family:Inter,Arial,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4;padding:30px 0;'>
    <tr><td align='center'>
      <table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.1);max-width:96%;'>

        <!-- Header -->
        <tr>
          <td style='background:{$logo_color};padding:25px 30px;'>
            <h1 style='margin:0;color:#000;font-size:22px;font-weight:700;'>{$brand_name}</h1>
            <p style='margin:5px 0 0;color:#000;font-size:13px;opacity:0.8;'>{$chat_title}</p>
          </td>
        </tr>

        <!-- Title -->
        <tr>
          <td style='padding:25px 30px 10px;'>
            <h2 style='margin:0;font-size:18px;color:#111;'>{$title}</h2>
          </td>
        </tr>

        <!-- Data Table -->
        <tr>
          <td style='padding:0 30px 20px;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #f0f0f0;border-radius:6px;overflow:hidden;'>
              {$rows_html}
            </table>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style='padding:15px 30px 30px;border-top:2px solid {$logo_color};'>
            <p style='font-size:13px;color:#666;margin:10px 0 0;'>
              This notification was sent by <strong>{$chat_title}</strong>.<br>
              Received at: " . current_time('d M Y, H:i') . "
            </p>
            {$footer_html}
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>";
    }

    /**
     * Send email notification for new lead
     */
    private function send_lead_notification( $lead_data ) {
        $to      = get_option( 'devxpert_chatbot_notification_email', get_option( 'admin_email' ) );
        $subject = '🔔 [' . $this->get_brand_name() . '] New Lead Captured: ' . $lead_data['name'];

        $rows = array(
            'Name'           => $lead_data['name'],
            'Email'          => $lead_data['email'],
            'Company'        => $lead_data['company'],
            'Location'       => $lead_data['location'],
            'Industry'       => $lead_data['industry'],
            'Platforms'      => $lead_data['platforms'],
            'Capabilities'   => $lead_data['capabilities'],
            'Service Type'   => $lead_data['service_type'],
            'Pain Point'     => $lead_data['pain'],
            'Initial Intent' => $lead_data['initial_intent'],
            'Lead Type'      => $lead_data['lead_type'],
            'Page URL'       => $lead_data['page_url'],
            'Submitted'      => $lead_data['created_at'],
        );

        $html = $this->build_email_html( 'New Lead Captured', $rows, 'Log in to your WordPress dashboard to view all leads.' );
        $this->send_email( $to, $subject, $html );
    }

    /**
     * Send email notification when a lead updates additional info
     */
    private function send_lead_update_notification( $email, $update_data ) {
        $to      = get_option( 'devxpert_chatbot_notification_email', get_option( 'admin_email' ) );
        $subject = '✏️ [' . $this->get_brand_name() . '] Lead Updated: ' . $email;

        $rows = array(
            'Email'        => $email,
            'Company'      => $update_data['company'],
            'Location'     => $update_data['location'],
            'Industry'     => $update_data['industry'],
            'Platforms'    => $update_data['platforms'],
            'Capabilities' => $update_data['capabilities'],
            'Service Type' => $update_data['service_type'],
            'Pain Point'   => $update_data['pain'],
        );

        $html = $this->build_email_html( 'Lead Updated With Full Details', $rows, 'The lead has completed the full consultation form.' );
        $this->send_email( $to, $subject, $html );
    }

    /**
     * Send email notification for new newsletter subscriber
     */
    private function send_newsletter_notification( $email, $name ) {
        $to      = get_option( 'devxpert_chatbot_notification_email', get_option( 'admin_email' ) );
        $subject = '📧 [' . $this->get_brand_name() . '] New Newsletter Subscriber: ' . $email;

        $rows = array(
            'Name'       => $name,
            'Email'      => $email,
            'Subscribed' => current_time( 'mysql' ),
        );

        $html = $this->build_email_html( 'New Newsletter Subscriber', $rows, 'Manage subscribers in ' . $this->get_brand_name() . ' Chatbot → Newsletter.' );
        $this->send_email( $to, $subject, $html );
    }

    /**
     * Handle lead update with additional information
     */
    public function handle_lead_update() {
        check_ajax_referer( 'devxpert_chatbot_nonce', 'nonce' );

        $email = sanitize_email( $_POST['email'] ?? '' );
        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'Invalid email address' ) );
            return;
        }

        $update_data = array(
            'company'      => sanitize_text_field( $_POST['company'] ?? '' ),
            'location'     => sanitize_text_field( $_POST['location'] ?? '' ),
            'industry'     => sanitize_text_field( $_POST['industry'] ?? '' ),
            'platforms'    => sanitize_text_field( $_POST['platforms'] ?? '' ),
            'capabilities' => sanitize_text_field( $_POST['capabilities'] ?? '' ),
            'service_type' => sanitize_text_field( $_POST['service_type'] ?? '' ),
            'pain'         => sanitize_textarea_field( $_POST['pain'] ?? '' ),
        );

        global $wpdb;
        $table_name = $wpdb->prefix . 'devxpert_chatbot_leads';

        $updated = $wpdb->update(
            $table_name,
            $update_data,
            array( 'email' => $email ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
            array( '%s' )
        );

        if ( $updated !== false ) {
            $this->send_lead_update_notification( $email, $update_data );
            wp_send_json_success( array( 'message' => 'Lead updated successfully', 'email' => $email ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to update lead' ) );
        }
    }

    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        return sanitize_text_field( $ip );
    }

    /**
     * Handle newsletter subscription via AJAX
     */
    public function handle_newsletter_subscription() {
        check_ajax_referer('devxpert_chatbot_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email'] ?? '');
        $name = sanitize_text_field($_POST['name'] ?? '');
        
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Invalid email address'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'devxpert_newsletter_subscribers';
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE email = %s",
            $email
        ));
        
        if ($existing) {
            wp_send_json_error(array('message' => 'This email is already subscribed'));
            return;
        }
        
        $inserted = $wpdb->insert(
            $table_name,
            array(
                'email' => $email,
                'name' => $name,
                'subscribed_at' => current_time('mysql'),
                'ip_address' => $this->get_client_ip(),
                'page_url' => esc_url_raw($_POST['page_url'] ?? ''),
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
        
        if ($inserted) {
            $this->send_newsletter_notification($email, $name);
            wp_send_json_success(array('message' => 'Successfully subscribed!'));
        } else {
            wp_send_json_error(array('message' => 'Failed to subscribe'));
        }
    }

    /**
     * Migrate legacy YALLO option keys and tables to DevXpert names.
     */
    public function maybe_migrate_yallo_data() {
        if (get_option('devxpert_chatbot_migrated_from_yallo', false)) {
            return;
        }

        global $wpdb;

        $option_map = array(
            'yallo_chatbot_enabled'            => 'devxpert_chatbot_enabled',
            'yallo_chatbot_auto_open'          => 'devxpert_chatbot_auto_open',
            'yallo_chatbot_scroll_trigger'     => 'devxpert_chatbot_scroll_trigger',
            'yallo_chatbot_notification_email' => 'devxpert_chatbot_notification_email',
            'yallo_chatbot_display_mode'       => 'devxpert_chatbot_display_mode',
            'yallo_chatbot_specific_pages'     => 'devxpert_chatbot_specific_pages',
            'yallo_chatbot_questions_json'     => 'devxpert_chatbot_questions_json',
            'yallo_chatbot_questions'          => 'devxpert_chatbot_questions',
            'yallo_chatbot_bypass_cache'       => 'devxpert_chatbot_bypass_cache',
            'yallo_chatbot_db_version'         => 'devxpert_chatbot_db_version',
            'yallo_newsletter_enabled'         => 'devxpert_newsletter_enabled',
            'yallo_newsletter_delay'           => 'devxpert_newsletter_delay',
            'yallo_newsletter_title'           => 'devxpert_newsletter_title',
            'yallo_newsletter_description'     => 'devxpert_newsletter_description',
            'yallo_newsletter_button_text'     => 'devxpert_newsletter_button_text',
            'yallo_newsletter_show_once'       => 'devxpert_newsletter_show_once',
            'yallo_smtp_enabled'               => 'devxpert_smtp_enabled',
            'yallo_smtp_host'                  => 'devxpert_smtp_host',
            'yallo_smtp_port'                  => 'devxpert_smtp_port',
            'yallo_smtp_username'              => 'devxpert_smtp_username',
            'yallo_smtp_password'              => 'devxpert_smtp_password',
            'yallo_smtp_encryption'            => 'devxpert_smtp_encryption',
            'yallo_smtp_from_email'            => 'devxpert_smtp_from_email',
            'yallo_smtp_from_name'             => 'devxpert_smtp_from_name',
            'yallo_claude_api_key'             => 'devxpert_claude_api_key',
            'yallo_ai_enabled'                 => 'devxpert_ai_enabled',
            'yallo_ai_fallback_message'        => 'devxpert_ai_fallback_message',
        );

        foreach ($option_map as $old_key => $new_key) {
            if (get_option($new_key, null) !== null) {
                continue;
            }

            $legacy_value = get_option($old_key, null);
            if ($legacy_value !== null) {
                add_option($new_key, $legacy_value);
            }
        }

        $table_map = array(
            $wpdb->prefix . 'yallo_chatbot_leads'           => $wpdb->prefix . 'devxpert_chatbot_leads',
            $wpdb->prefix . 'yallo_newsletter_subscribers'  => $wpdb->prefix . 'devxpert_newsletter_subscribers',
            $wpdb->prefix . 'yallo_knowledge_base'          => $wpdb->prefix . 'devxpert_knowledge_base',
        );

        foreach ($table_map as $old_table => $new_table) {
            $old_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $old_table));
            $new_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $new_table));

            if ($old_exists && !$new_exists) {
                $wpdb->query("RENAME TABLE `{$old_table}` TO `{$new_table}`");
            }
        }

        update_option('devxpert_chatbot_migrated_from_yallo', 1);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            $this->get_brand_name() . ' Chatbot',
            $this->get_brand_name() . ' Chatbot',
            'manage_options',
            'devxpert-chatbot',
            array($this, 'render_admin_page'),
            'dashicons-format-chat',
            30
        );
        
        add_submenu_page(
            'devxpert-chatbot',
            'Settings',
            'Settings',
            'manage_options',
            'devxpert-chatbot',
            array($this, 'render_admin_page')
        );
        
        add_submenu_page(
            'devxpert-chatbot',
            'Edit Questions',
            'Questions',
            'manage_options',
            'devxpert-chatbot-questions',
            array($this, 'render_questions_page')
        );
        
        add_submenu_page(
            'devxpert-chatbot',
            'AI Settings',
            '🤖 AI Settings',
            'manage_options',
            'devxpert-chatbot-ai',
            array($this, 'render_ai_settings_page')
        );
        
        add_submenu_page(
            'devxpert-chatbot',
            'Leads',
            'Leads',
            'manage_options',
            'devxpert-chatbot-leads',
            array($this, 'render_leads_page')
        );
        
        add_submenu_page(
            'devxpert-chatbot',
            'Newsletter Subscribers',
            'Newsletter',
            'manage_options',
            'devxpert-chatbot-newsletter',
            array($this, 'render_newsletter_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('devxpert_chatbot_settings', 'devxpert_chatbot_enabled');
        register_setting('devxpert_chatbot_settings', 'devxpert_brand_name');
        register_setting('devxpert_chatbot_settings', 'devxpert_chatbot_title');
        register_setting('devxpert_chatbot_settings', 'devxpert_chatbot_subtitle');
        register_setting('devxpert_chatbot_settings', 'devxpert_brand_accent_color');
        register_setting('devxpert_chatbot_settings', 'devxpert_chatbot_auto_open');
        register_setting('devxpert_chatbot_settings', 'devxpert_chatbot_scroll_trigger');
        register_setting('devxpert_chatbot_settings', 'devxpert_chatbot_notification_email');
        register_setting('devxpert_chatbot_settings', 'devxpert_chatbot_display_mode');
        register_setting('devxpert_chatbot_settings', 'devxpert_chatbot_specific_pages');
        
        // Chatbot questions
        register_setting('devxpert_chatbot_settings', 'devxpert_chatbot_questions_json');
        register_setting('devxpert_chatbot_settings', 'devxpert_chatbot_bypass_cache');

        // Newsletter settings
        register_setting('devxpert_chatbot_settings', 'devxpert_newsletter_enabled');
        register_setting('devxpert_chatbot_settings', 'devxpert_newsletter_delay');
        register_setting('devxpert_chatbot_settings', 'devxpert_newsletter_title');
        register_setting('devxpert_chatbot_settings', 'devxpert_newsletter_description');
        register_setting('devxpert_chatbot_settings', 'devxpert_newsletter_button_text');
        register_setting('devxpert_chatbot_settings', 'devxpert_newsletter_show_once');

        // SMTP settings
        register_setting('devxpert_chatbot_settings', 'devxpert_smtp_enabled');
        register_setting('devxpert_chatbot_settings', 'devxpert_smtp_host');
        register_setting('devxpert_chatbot_settings', 'devxpert_smtp_port');
        register_setting('devxpert_chatbot_settings', 'devxpert_smtp_username');
        register_setting('devxpert_chatbot_settings', 'devxpert_smtp_password');
        register_setting('devxpert_chatbot_settings', 'devxpert_smtp_encryption');
        register_setting('devxpert_chatbot_settings', 'devxpert_smtp_from_email');
        register_setting('devxpert_chatbot_settings', 'devxpert_smtp_from_name');
        
        // AI settings
        register_setting('devxpert_chatbot_settings', 'devxpert_claude_api_key');
        register_setting('devxpert_chatbot_settings', 'devxpert_ai_enabled');
        register_setting('devxpert_chatbot_settings', 'devxpert_ai_fallback_message');

        // Telegram settings
        register_setting('devxpert_chatbot_settings', 'devxpert_telegram_enabled');
        register_setting('devxpert_chatbot_settings', 'devxpert_telegram_bot_token');
        register_setting('devxpert_chatbot_settings', 'devxpert_telegram_secret_token');
    }

    /**
     * Register REST routes used by external chat channels.
     */
    public function register_rest_routes() {
        register_rest_route('devxpert/v1', '/telegram/webhook', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_telegram_webhook'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Handle SMTP test email via AJAX
     */
    public function handle_smtp_test() {
        check_ajax_referer( 'devxpert_chatbot_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
            return;
        }

        $to = get_option( 'devxpert_chatbot_notification_email', get_option( 'admin_email' ) );

        $rows = array(
            'Status'  => 'SMTP connection successful ✅',
            'Host'    => get_option( 'devxpert_smtp_host' ),
            'Port'    => get_option( 'devxpert_smtp_port' ),
            'From'    => get_option( 'devxpert_smtp_from_email' ),
            'Sent to' => $to,
            'Time'    => current_time( 'mysql' ),
        );

        $html   = $this->build_email_html( 'SMTP Test Email', $rows, 'Your ' . $this->get_brand_name() . ' SMTP settings are working correctly.' );
        $result = wp_mail( $to, '✅ [' . $this->get_brand_name() . '] SMTP Test Email', $html, array( 'Content-Type: text/html; charset=UTF-8' ) );

        if ( $result ) {
            wp_send_json_success( array( 'message' => 'Test email sent to ' . $to ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to send. Check your SMTP credentials.' ) );
        }
    }
    
    /**
     * Render admin settings page
     */
    public function render_admin_page() {
        include DEVXPERT_CHATBOT_PLUGIN_DIR . 'admin/settings.php';
    }
    
    /**
     * Render questions editor page
     */
    public function render_questions_page() {
        include DEVXPERT_CHATBOT_PLUGIN_DIR . 'admin/questions.php';
    }
    
    /**
     * Render AI settings page
     */
    public function render_ai_settings_page() {
        include DEVXPERT_CHATBOT_PLUGIN_DIR . 'admin/devxpert-chatbot-rag-settings.php';
    }
    
    /**
     * Render leads page
     */
    public function render_leads_page() {
        include DEVXPERT_CHATBOT_PLUGIN_DIR . 'admin/leads.php';
    }
    
    /**
     * Render newsletter subscribers page
     */
    public function render_newsletter_page() {
        include DEVXPERT_CHATBOT_PLUGIN_DIR . 'admin/newsletter.php';
    }

    /**
     * Admin-only AI test handler used by the AI settings page.
     */
    public function handle_test_ai() {
        check_ajax_referer('devxpert_ai_test', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }

        $message = sanitize_text_field($_POST['message'] ?? '');
        if ($message === '') {
            wp_send_json_error(array('message' => 'Please enter a test question.'));
            return;
        }

        if (!class_exists('DEVXPERT_Chatbot_RAG')) {
            require_once DEVXPERT_CHATBOT_PLUGIN_DIR . 'devxpert-chatbot-rag-ai.php';
        }

        $rag      = new DEVXPERT_Chatbot_RAG();
        $response = $rag->get_ai_response($message, array());

        if ($response['success']) {
            wp_send_json_success($response);
            return;
        }

        wp_send_json_error($response);
    }

    /**
     * Handle AI chat via AJAX
     */
    public function handle_ai_chat() {
        check_ajax_referer('devxpert_chatbot_nonce', 'nonce');

        // Rate limiting: max 20 AI requests per IP per hour
        $rate_key   = 'devxpert_ai_rate_' . md5($this->get_client_ip());
        $rate_count = (int) get_transient($rate_key);
        if ($rate_count >= 20) {
            wp_send_json_error(array('message' => 'Too many requests. Please try again later.'));
            return;
        }
        set_transient($rate_key, $rate_count + 1, HOUR_IN_SECONDS);

        $message = sanitize_text_field($_POST['message'] ?? '');

        if (!get_option('devxpert_claude_api_key')) {
            wp_send_json_error(array('message' => 'AI not configured. Please add your Claude API key in AI Settings.'));
            return;
        }

        // Parse and sanitize conversation history from frontend
        $history_raw = isset($_POST['history']) ? wp_unslash($_POST['history']) : '[]';
        $history     = json_decode($history_raw, true);
        if (!is_array($history)) {
            $history = array();
        }
        $history = array_slice($history, -10); // keep last 10 turns max
        $history = array_map(function($turn) {
            return array(
                'role'    => in_array($turn['role'] ?? '', array('user', 'assistant'), true) ? $turn['role'] : 'user',
                'content' => sanitize_textarea_field($turn['content'] ?? ''),
            );
        }, $history);

        if (!class_exists('DEVXPERT_Chatbot_RAG')) {
            require_once DEVXPERT_CHATBOT_PLUGIN_DIR . 'devxpert-chatbot-rag-ai.php';
        }

        $rag      = new DEVXPERT_Chatbot_RAG();
        $response = $rag->get_ai_response($message, $history);

        if ($response['success']) {
            wp_send_json_success($response);
        } else {
            wp_send_json_error($response);
        }
    }

    /**
     * Simple API key test
     */
    public function handle_test_api_simple() {
        check_ajax_referer('devxpert_chatbot_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        $api_key = get_option('devxpert_claude_api_key', '');
        
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'No API key configured. Please add your Claude API key and save settings.'));
            return;
        }
        
        // Test API with minimal request
        $api_url = 'https://api.anthropic.com/v1/messages';
        
        $headers = array(
            'Content-Type' => 'application/json',
            'x-api-key' => $api_key,
            'anthropic-version' => '2023-06-01'
        );
        
        $body = array(
            'model' => 'claude-3-5-haiku-20241022',
            'max_tokens' => 20,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => 'Say hello'
                )
            )
        );
        
        $response = wp_remote_post($api_url, array(
            'headers' => $headers,
            'body' => wp_json_encode($body),
            'timeout' => 60
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => 'Connection failed: ' . $response->get_error_message() . '. Your server might be blocking external API calls.'
            ));
            return;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        
        if ($response_code !== 200) {
            $error_msg = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error';
            
            if ($response_code === 401) {
                $error_msg = 'Invalid API key. Please check your key at console.anthropic.com/settings/keys';
            } elseif ($response_code === 429) {
                $error_msg = 'Rate limit exceeded. Wait a few minutes or add payment method to your Anthropic account.';
            } elseif ($response_code === 400) {
                $error_msg = 'Bad request: ' . $error_msg;
            }
            
            wp_send_json_error(array(
                'message' => $error_msg,
                'code' => $response_code
            ));
            return;
        }
        
        if (isset($data['content'][0]['text'])) {
            wp_send_json_success(array(
                'model' => 'Claude 3.5 Haiku',
                'response' => $data['content'][0]['text'],
                'tokens' => isset($data['usage']['input_tokens']) ? $data['usage']['input_tokens'] + $data['usage']['output_tokens'] : 'N/A'
            ));
        } else {
            wp_send_json_error(array('message' => 'Invalid response from API'));
        }
    }
    
    /**
     * Add settings link on plugins page
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=devxpert-chatbot') . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Get chatbot questions via AJAX
     */
    public function get_chatbot_questions() {
        wp_send_json_success($this->get_chatbot_questions_data());
    }

    /**
     * Return chatbot question config as a reusable PHP array.
     */
    private function get_chatbot_questions_data() {
        $questions_json = get_option('devxpert_chatbot_questions_json', '');
        $questions      = get_option('devxpert_chatbot_questions', null);

        if (!empty($questions_json)) {
            $decoded_questions = json_decode($questions_json, true);
            if (is_array($decoded_questions)) {
                $questions = $decoded_questions;
            }
        }

        if ($questions) {
            return $questions;
        }

        return array(
            'welcome' => array(
                'text' => "Hi, we're " . $this->get_brand_name() . " 👋\n\nWe build high-performance websites and digital solutions that help businesses grow. How can we help you today?",
            ),
            'services' => array(
                array(
                    'text' => '🌐 Custom Website Development',
                    'message' => "Excellent.\n\nWe specialize in high-converting custom websites built with modern frameworks like WordPress, React, or Next.js. We handle everything from UI/UX design to deployment.",
                    'intent' => 'new_site',
                    'lead_type' => 'details',
                    'cta_primary' => '📋 Request a project quote',
                    'cta_secondary' => '📞 Book a discovery call',
                ),
                array(
                    'text' => '🛒 E-commerce Solutions',
                    'message' => "Got it.\n\nWe build robust E-commerce stores using WooCommerce or Shopify, optimized for speed and sales. Whether you're starting fresh or scaling up, we've got you covered.",
                    'intent' => 'ecommerce',
                    'lead_type' => 'details',
                    'cta_primary' => '📋 Get an e-commerce estimate',
                    'cta_secondary' => '📞 Talk to a retail expert',
                ),
                array(
                    'text' => '🚀 Web App & SaaS Development',
                    'message' => "Great.\n\nOur team builds scalable web applications and SaaS products using cutting-edge tech stacks. We focus on performance, security, and a seamless user experience.",
                    'intent' => 'webapp',
                    'lead_type' => 'call',
                    'cta_primary' => '📞 Discuss my web app idea',
                    'cta_secondary' => '📋 Send my project brief',
                ),
                array(
                    'text' => '🛠️ Website Maintenance',
                    'message' => "Makes sense.\n\nWe provide ongoing maintenance, security updates, and performance optimization to keep your website running smoothly 24/7.",
                    'intent' => 'maintenance',
                    'lead_type' => 'call',
                    'cta_primary' => '📞 Discuss a support plan',
                    'cta_secondary' => '📋 Share my site details',
                ),
            ),
            'consultation' => array(
                array('key' => 'name', 'text' => "Let's get started.\n\nWhat's your **full name?**"),
                array('key' => 'email', 'text' => "Thanks **{name}**. What's your **work email** so we can send over the right details?"),
                array('key' => 'company', 'text' => "Which **company** or project is this for?"),
                array('key' => 'location', 'text' => "Where are you **located**?"),
                array('key' => 'industry', 'text' => "What is your **business industry**?\n\n- Retail / E-commerce\n- Professional Services\n- Tech / SaaS\n- Healthcare\n- Education\n- Real Estate\n- Other"),
                array('key' => 'platforms', 'text' => "Which **platform** are you interested in?\n\n- WordPress\n- React / Next.js\n- Shopify\n- Custom App\n- Not sure yet"),
                array('key' => 'capabilities', 'text' => "What is the **priority** for this project?\n\n- New Development\n- Redesign / UI Refresh\n- Speed & Optimization\n- New Features\n- Ongoing Maintenance"),
                array('key' => 'service_type', 'text' => "What is your **estimated budget** range?\n\n- Under $5k\n- $5k - $15k\n- $15k - $50k\n- $50k+\n- Not sure"),
                array('key' => 'pain', 'text' => "Briefly describe your **project goals** or the problem you're solving.")
            ),
            'final' => array(
                'text' => "Thanks, **{name}**. We've received your project details.\n\nA senior strategist will review your requirements and reach out to **{email}** within 24 hours."
            )
        );
    }

    /**
     * Telegram webhook handler.
     */
    public function handle_telegram_webhook($request) {
        if (!get_option('devxpert_telegram_enabled', false)) {
            return new WP_REST_Response(array('ok' => false, 'message' => 'Telegram disabled'), 200);
        }

        $bot_token = trim((string) get_option('devxpert_telegram_bot_token', ''));
        if ($bot_token === '') {
            return new WP_REST_Response(array('ok' => false, 'message' => 'Telegram bot token missing'), 200);
        }

        $expected_secret = trim((string) get_option('devxpert_telegram_secret_token', ''));
        if ($expected_secret !== '') {
            $received_secret = (string) $request->get_header('x-telegram-bot-api-secret-token');
            if (!hash_equals($expected_secret, $received_secret)) {
                return new WP_REST_Response(array('ok' => false, 'message' => 'Invalid secret token'), 403);
            }
        }

        $update = $request->get_json_params();
        if (!is_array($update) || empty($update['message']['chat']['id'])) {
            return new WP_REST_Response(array('ok' => true), 200);
        }

        $message = $update['message'];
        $chat_id = (string) $message['chat']['id'];
        $text    = trim((string) ($message['text'] ?? ''));

        if ($text === '') {
            $this->send_telegram_message($chat_id, "I can currently process text messages only. Send /start to begin.", $this->get_telegram_main_keyboard());
            return new WP_REST_Response(array('ok' => true), 200);
        }

        $reply = $this->build_telegram_reply($chat_id, $text, $message);
        if (!empty($reply['text'])) {
            $this->send_telegram_message($chat_id, $reply['text'], $reply['keyboard'] ?? null);
        }

        return new WP_REST_Response(array('ok' => true), 200);
    }

    /**
     * Build a Telegram reply while preserving lightweight conversation state.
     */
    private function build_telegram_reply($chat_id, $text, $message) {
        $state           = $this->get_telegram_state($chat_id);
        $normalized_text = $this->normalize_telegram_input($text);
        $questions       = $this->get_chatbot_questions_data();
        $services        = $questions['services'] ?? array();

        if (in_array($normalized_text, array('/start', '/menu', 'menu', 'start'), true)) {
            $state = $this->reset_telegram_state($chat_id);
            return array(
                'text'     => $this->format_telegram_text($questions['welcome']['text'] ?? "Hi, we're " . $this->get_brand_name() . '.'),
                'keyboard' => $this->get_telegram_main_keyboard(),
            );
        }

        if (($state['mode'] ?? '') === 'consultation') {
            return $this->handle_telegram_consultation_reply($chat_id, $state, $text, $message);
        }

        foreach ($services as $service) {
            if ($this->normalize_telegram_input($service['text'] ?? '') === $normalized_text) {
                $state['selected_service'] = array(
                    'intent'    => $service['intent'] ?? '',
                    'lead_type' => $service['lead_type'] ?? '',
                );
                $this->set_telegram_state($chat_id, $state);

                $reply_text = $this->format_telegram_text($service['message'] ?? '');
                $reply_text .= "\n\nReply with:\n- Share details\n- Book a call\n- Menu";

                return array(
                    'text'     => $reply_text,
                    'keyboard' => $this->get_telegram_service_keyboard(),
                );
            }
        }

        if (in_array($normalized_text, array('share details', 'book a call'), true)) {
            $lead_type = $normalized_text === 'book a call' ? 'call' : 'details';
            return $this->start_telegram_consultation($chat_id, $state, $lead_type);
        }

        if (get_option('devxpert_claude_api_key')) {
            $ai_message = $this->get_telegram_ai_response($text, $state);
            if ($ai_message !== '') {
                $state = $this->append_telegram_history($state, 'user', $text);
                $state = $this->append_telegram_history($state, 'assistant', $ai_message);
                $this->set_telegram_state($chat_id, $state);

                return array(
                    'text'     => $ai_message,
                    'keyboard' => $this->get_telegram_main_keyboard(),
                );
            }
        }

        return array(
            'text'     => "Please choose one of the options below or send /start to reset the chat.",
            'keyboard' => $this->get_telegram_main_keyboard(),
        );
    }

    /**
     * Start the Telegram consultation flow.
     */
    private function start_telegram_consultation($chat_id, $state, $lead_type) {
        $questions = $this->get_chatbot_questions_data();
        $state['mode'] = 'consultation';
        $state['consultation_step'] = 0;
        $state['consultation_data'] = array(
            'initial_intent' => $state['selected_service']['intent'] ?? '',
            'lead_type'      => $lead_type ?: ($state['selected_service']['lead_type'] ?? ''),
        );
        $state['lead_saved_early'] = false;
        $state['history'] = array();
        $this->set_telegram_state($chat_id, $state);

        $question = $questions['consultation'][0]['text'] ?? "What's your full name?";

        return array(
            'text'     => $this->format_telegram_text($question),
            'keyboard' => $this->get_telegram_cancel_keyboard(),
        );
    }

    /**
     * Handle Telegram consultation answers and lead persistence.
     */
    private function handle_telegram_consultation_reply($chat_id, $state, $text, $message) {
        $normalized = $this->normalize_telegram_input($text);
        if (in_array($normalized, array('menu', '/start', '/menu', 'cancel'), true)) {
            $state = $this->reset_telegram_state($chat_id);
            $questions = $this->get_chatbot_questions_data();

            return array(
                'text'     => $this->format_telegram_text($questions['welcome']['text'] ?? "Hi, we're " . $this->get_brand_name() . '.'),
                'keyboard' => $this->get_telegram_main_keyboard(),
            );
        }

        $questions = $this->get_chatbot_questions_data();
        $flow      = $questions['consultation'] ?? array();
        $step      = (int) ($state['consultation_step'] ?? 0);
        $current   = $flow[$step] ?? null;

        if (!$current) {
            $state = $this->reset_telegram_state($chat_id);
            return array(
                'text'     => 'The consultation state expired. Send /start to begin again.',
                'keyboard' => $this->get_telegram_main_keyboard(),
            );
        }

        if (($current['key'] ?? '') === 'email' && !is_email($text)) {
            return array(
                'text'     => 'That email does not look valid. Please send a work email address.',
                'keyboard' => $this->get_telegram_cancel_keyboard(),
            );
        }

        $state['consultation_data'][$current['key']] = sanitize_text_field($text);
        $state['consultation_step'] = $step + 1;

        if ((int) $state['consultation_step'] === 2 && empty($state['lead_saved_early'])) {
            $this->save_telegram_lead($state['consultation_data'], $message);
            $state['lead_saved_early'] = true;
        }

        if ((int) $state['consultation_step'] >= count($flow)) {
            if (!empty($state['lead_saved_early'])) {
                $this->update_telegram_lead($state['consultation_data']);
            } else {
                $this->save_telegram_lead($state['consultation_data'], $message);
            }

            $email = $state['consultation_data']['email'] ?? '';
            $name  = $state['consultation_data']['name'] ?? 'there';
            $this->reset_telegram_state($chat_id);

            return array(
                'text'     => "Thanks, {$name}. We'll be in touch at {$email} within 24 hours.",
                'keyboard' => $this->get_telegram_main_keyboard(),
            );
        }

        $next_question = $flow[$state['consultation_step']]['text'] ?? '';
        $next_question = str_replace('{name}', $state['consultation_data']['name'] ?? '', $next_question);
        $this->set_telegram_state($chat_id, $state);

        return array(
            'text'     => $this->format_telegram_text($next_question),
            'keyboard' => $this->get_telegram_cancel_keyboard(),
        );
    }

    /**
     * Send a Telegram message using the configured bot token.
     */
    private function send_telegram_message($chat_id, $text, $keyboard = null) {
        $bot_token = trim((string) get_option('devxpert_telegram_bot_token', ''));
        if ($bot_token === '') {
            return false;
        }

        $body = array(
            'chat_id'                  => $chat_id,
            'text'                     => $text,
            'disable_web_page_preview' => true,
        );

        if (is_array($keyboard)) {
            $body['reply_markup'] = wp_json_encode($keyboard);
        }

        $response = wp_remote_post('https://api.telegram.org/bot' . rawurlencode($bot_token) . '/sendMessage', array(
            'timeout' => 20,
            'body'    => $body,
        ));

        return !is_wp_error($response);
    }

    /**
     * Fetch an AI response for Telegram using the existing RAG handler.
     */
    private function get_telegram_ai_response($text, $state) {
        if (!class_exists('DEVXPERT_Chatbot_RAG')) {
            require_once DEVXPERT_CHATBOT_PLUGIN_DIR . 'devxpert-chatbot-rag-ai.php';
        }

        $history = array_slice($state['history'] ?? array(), -10);
        $rag     = new DEVXPERT_Chatbot_RAG();
        $result  = $rag->get_ai_response(sanitize_text_field($text), $history);

        if (!empty($result['success']) && !empty($result['message'])) {
            return $result['message'];
        }

        return '';
    }

    /**
     * Persist a new lead from Telegram.
     */
    private function save_telegram_lead($lead_data, $message) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'devxpert_chatbot_leads';

        $telegram_user = $message['from']['username'] ?? ($message['from']['first_name'] ?? 'telegram-user');
        $lead = array(
            'name'           => sanitize_text_field($lead_data['name'] ?? $telegram_user),
            'email'          => sanitize_email($lead_data['email'] ?? ''),
            'company'        => sanitize_text_field($lead_data['company'] ?? ''),
            'location'       => sanitize_text_field($lead_data['location'] ?? ''),
            'industry'       => sanitize_text_field($lead_data['industry'] ?? ''),
            'platforms'      => sanitize_text_field($lead_data['platforms'] ?? ''),
            'capabilities'   => sanitize_text_field($lead_data['capabilities'] ?? ''),
            'service_type'   => sanitize_text_field($lead_data['service_type'] ?? ''),
            'pain'           => sanitize_textarea_field($lead_data['pain'] ?? ''),
            'initial_intent' => sanitize_text_field($lead_data['initial_intent'] ?? ''),
            'lead_type'      => sanitize_text_field($lead_data['lead_type'] ?? ''),
            'page_url'       => 'telegram://chat/' . sanitize_text_field((string) ($message['chat']['id'] ?? '')),
            'created_at'     => current_time('mysql'),
            'user_agent'     => 'Telegram Bot API',
            'ip_address'     => 'telegram',
        );

        if (!is_email($lead['email'])) {
            return false;
        }

        $existing_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE email = %s LIMIT 1",
            $lead['email']
        ));

        if ($existing_id) {
            return (int) $existing_id;
        }

        $inserted = $wpdb->insert(
            $table_name,
            $lead,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($inserted) {
            $this->send_lead_notification($lead);
            return (int) $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update an existing lead with the rest of the Telegram consultation answers.
     */
    private function update_telegram_lead($lead_data) {
        $email = sanitize_email($lead_data['email'] ?? '');
        if (!is_email($email)) {
            return false;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'devxpert_chatbot_leads';
        $update_data = array(
            'company'      => sanitize_text_field($lead_data['company'] ?? ''),
            'location'     => sanitize_text_field($lead_data['location'] ?? ''),
            'industry'     => sanitize_text_field($lead_data['industry'] ?? ''),
            'platforms'    => sanitize_text_field($lead_data['platforms'] ?? ''),
            'capabilities' => sanitize_text_field($lead_data['capabilities'] ?? ''),
            'service_type' => sanitize_text_field($lead_data['service_type'] ?? ''),
            'pain'         => sanitize_textarea_field($lead_data['pain'] ?? ''),
        );

        $updated = $wpdb->update(
            $table_name,
            $update_data,
            array('email' => $email),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%s')
        );

        if ($updated !== false) {
            $this->send_lead_update_notification($email, $update_data);
            return true;
        }

        return false;
    }

    /**
     * Normalize user input for simple command matching.
     */
    private function normalize_telegram_input($text) {
        $text = strtolower(trim(wp_strip_all_tags((string) $text)));
        $text = preg_replace('/\s+/', ' ', $text);
        return $text;
    }

    /**
     * Strip lightweight website formatting for Telegram delivery.
     */
    private function format_telegram_text($text) {
        $text = str_replace('**', '', (string) $text);
        return trim(wp_strip_all_tags($text));
    }

    /**
     * Load Telegram conversation state.
     */
    private function get_telegram_state($chat_id) {
        $state = get_transient('devxpert_tg_state_' . md5((string) $chat_id));
        return is_array($state) ? $state : array(
            'mode'              => 'idle',
            'consultation_step' => 0,
            'consultation_data' => array(),
            'selected_service'  => array(),
            'lead_saved_early'  => false,
            'history'           => array(),
        );
    }

    /**
     * Save Telegram conversation state for 24 hours.
     */
    private function set_telegram_state($chat_id, $state) {
        set_transient('devxpert_tg_state_' . md5((string) $chat_id), $state, DAY_IN_SECONDS);
    }

    /**
     * Reset Telegram conversation state.
     */
    private function reset_telegram_state($chat_id) {
        delete_transient('devxpert_tg_state_' . md5((string) $chat_id));
        return $this->get_telegram_state($chat_id);
    }

    /**
     * Append a chat turn to Telegram AI history.
     */
    private function append_telegram_history($state, $role, $content) {
        $state['history'][] = array(
            'role'    => $role,
            'content' => sanitize_textarea_field($content),
        );
        $state['history'] = array_slice($state['history'], -10);
        return $state;
    }

    /**
     * Build the primary Telegram reply keyboard.
     */
    private function get_telegram_main_keyboard() {
        $questions = $this->get_chatbot_questions_data();
        $services  = $questions['services'] ?? array();
        $keyboard  = array();

        foreach ($services as $service) {
            if (!empty($service['text'])) {
                $keyboard[] = array(array('text' => $service['text']));
            }
        }

        return array(
            'keyboard'          => $keyboard,
            'resize_keyboard'   => true,
            'one_time_keyboard' => false,
        );
    }

    /**
     * Build Telegram keyboard for service follow-up actions.
     */
    private function get_telegram_service_keyboard() {
        return array(
            'keyboard' => array(
                array(
                    array('text' => 'Share details'),
                    array('text' => 'Book a call'),
                ),
                array(
                    array('text' => 'Menu'),
                ),
            ),
            'resize_keyboard'   => true,
            'one_time_keyboard' => false,
        );
    }

    /**
     * Build Telegram keyboard for consultation mode.
     */
    private function get_telegram_cancel_keyboard() {
        return array(
            'keyboard' => array(
                array(
                    array('text' => 'Menu'),
                ),
            ),
            'resize_keyboard'   => true,
            'one_time_keyboard' => false,
        );
    }

    /**
     * DB upgrade: add columns introduced after initial release
     */
    public function maybe_upgrade_db() {
        $db_version = get_option('devxpert_chatbot_db_version', '1.0');
        if (version_compare($db_version, '1.1', '>=')) {
            return;
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'devxpert_chatbot_leads';
        $col = $wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE 'status'");
        if (!$col) {
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN status varchar(20) NOT NULL DEFAULT 'new' AFTER lead_type");
            $wpdb->query("ALTER TABLE $table_name ADD KEY status (status)");
        }
        update_option('devxpert_chatbot_db_version', '1.1');
    }

    /**
     * AJAX: update a lead's status (admin only)
     */
    public function handle_update_lead_status() {
        check_ajax_referer('devxpert_chatbot_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }

        $lead_id         = intval($_POST['lead_id'] ?? 0);
        $status          = sanitize_text_field($_POST['status'] ?? '');
        $allowed_statuses = array('new', 'contacted', 'converted', 'lost');

        if (!$lead_id || !in_array($status, $allowed_statuses, true)) {
            wp_send_json_error(array('message' => 'Invalid data'));
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'devxpert_chatbot_leads';
        $updated = $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $lead_id),
            array('%s'),
            array('%d')
        );

        if ($updated !== false) {
            wp_send_json_success(array('message' => 'Status updated'));
        } else {
            wp_send_json_error(array('message' => 'Update failed'));
        }
    }

    /**
     * Admin POST: export all leads as CSV download
     */
    public function handle_export_leads() {
        check_admin_referer('devxpert_export_leads');
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'devxpert_chatbot_leads';
        $leads = $wpdb->get_results(
            "SELECT id, name, email, company, location, industry, platforms, capabilities,
                    service_type, pain, initial_intent, lead_type, status, page_url, created_at
             FROM $table_name ORDER BY created_at DESC",
            ARRAY_A
        );

        $filename = 'devxpert-leads-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

        if (!empty($leads)) {
            fputcsv($output, array_keys($leads[0]));
            foreach ($leads as $lead) {
                fputcsv($output, $lead);
            }
        }

        fclose($output);
        exit;
    }
}

/**
 * Plugin activation
 */
function devxpert_chatbot_activate() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'devxpert_chatbot_leads';
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
        status varchar(20) NOT NULL DEFAULT 'new',
        page_url varchar(500) DEFAULT '',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        user_agent varchar(500) DEFAULT '',
        ip_address varchar(100) DEFAULT '',
        PRIMARY KEY  (id),
        KEY email (email),
        KEY created_at (created_at),
        KEY status (status)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Create newsletter subscribers table
    $newsletter_table = $wpdb->prefix . 'devxpert_newsletter_subscribers';
    
    $newsletter_sql = "CREATE TABLE IF NOT EXISTS $newsletter_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        name varchar(255) DEFAULT '',
        subscribed_at datetime DEFAULT CURRENT_TIMESTAMP,
        ip_address varchar(100) DEFAULT '',
        page_url varchar(500) DEFAULT '',
        PRIMARY KEY  (id),
        UNIQUE KEY email (email),
        KEY subscribed_at (subscribed_at)
    ) $charset_collate;";
    
    dbDelta($newsletter_sql);
    
    // Create AI knowledge base table
    if (class_exists('DEVXPERT_Chatbot_RAG')) {
        DEVXPERT_Chatbot_RAG::create_table();
    }
    
    // Set default options
    add_option('devxpert_chatbot_enabled', true);
    add_option('devxpert_brand_name', 'DevXpert');
    add_option('devxpert_chatbot_title', 'Digital Project Assistant');
    add_option('devxpert_chatbot_subtitle', 'Ask about custom websites, e-commerce, or performance optimization.');
    add_option('devxpert_brand_accent_color', '#2563EB');
    add_option('devxpert_chatbot_auto_open', true);
    add_option('devxpert_chatbot_scroll_trigger', 50);
    add_option('devxpert_chatbot_notification_email', get_option('admin_email'));
    add_option('devxpert_chatbot_display_mode', 'all_pages');
    add_option('devxpert_chatbot_specific_pages', '');
    
    // Newsletter options
    add_option('devxpert_newsletter_enabled', false);
    add_option('devxpert_newsletter_delay', 5000);
    add_option('devxpert_newsletter_title', 'Stay Updated with ' . get_option('devxpert_brand_name', 'DevXpert'));
    add_option('devxpert_newsletter_description', 'Get the latest insights on web development and digital optimization delivered to your inbox.');
    add_option('devxpert_newsletter_button_text', 'Subscribe Now');
    add_option('devxpert_newsletter_show_once', true);

    // SMTP options
    add_option('devxpert_smtp_enabled', false);
    add_option('devxpert_smtp_host', '');
    add_option('devxpert_smtp_port', 587);
    add_option('devxpert_smtp_username', '');
    add_option('devxpert_smtp_password', '');
    add_option('devxpert_smtp_encryption', 'tls');
    add_option('devxpert_smtp_from_email', get_option('admin_email'));
    add_option('devxpert_smtp_from_name', get_bloginfo('name'));
    
    // AI options
    add_option('devxpert_claude_api_key', '');
    add_option('devxpert_ai_enabled', false);
    add_option('devxpert_ai_fallback_message', "I don't have specific information about that. Would you like to speak with our team?");

    // Telegram options
    add_option('devxpert_telegram_enabled', false);
    add_option('devxpert_telegram_bot_token', '');
    add_option('devxpert_telegram_secret_token', wp_generate_password(32, false, false));
}
register_activation_hook(__FILE__, 'devxpert_chatbot_activate');

/**
 * Plugin deactivation
 */
function devxpert_chatbot_deactivate() {
    // Clean up if needed
}
register_deactivation_hook(__FILE__, 'devxpert_chatbot_deactivate');

/**
 * Initialize the plugin
 */
function devxpert_chatbot_init() {
    return DEVXPERT_Talent_Chatbot::get_instance();
}

// Start the plugin
devxpert_chatbot_init();
