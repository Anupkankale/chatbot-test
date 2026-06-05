<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package           DevXpert_Chatbot
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clear scheduled actions if any (none currently used, but good for future)
// wp_clear_scheduled_hook( 'devxpert_chatbot_cleanup_event' );

// Delete options
$options = array(
    'devxpert_chatbot_enabled',
    'devxpert_brand_name',
    'devxpert_chatbot_title',
    'devxpert_chatbot_subtitle',
    'devxpert_brand_accent_color',
    'devxpert_chatbot_auto_open',
    'devxpert_chatbot_scroll_trigger',
    'devxpert_chatbot_notification_email',
    'devxpert_chatbot_display_mode',
    'devxpert_chatbot_specific_pages',
    'devxpert_newsletter_enabled',
    'devxpert_newsletter_delay',
    'devxpert_newsletter_title',
    'devxpert_newsletter_description',
    'devxpert_newsletter_button_text',
    'devxpert_newsletter_show_once',
    'devxpert_smtp_enabled',
    'devxpert_smtp_host',
    'devxpert_smtp_port',
    'devxpert_smtp_username',
    'devxpert_smtp_password',
    'devxpert_smtp_encryption',
    'devxpert_smtp_from_email',
    'devxpert_smtp_from_name',
    'devxpert_claude_api_key',
    'devxpert_ai_enabled',
    'devxpert_ai_fallback_message',
    'devxpert_telegram_enabled',
    'devxpert_telegram_bot_token',
    'devxpert_telegram_secret_token',
    'devxpert_chatbot_version',
);

foreach ( $options as $option ) {
    delete_option( $option );
}

// Delete database tables
global $wpdb;

$tables = array(
    $wpdb->prefix . 'devxpert_chatbot_leads',
    $wpdb->prefix . 'devxpert_newsletter_subscribers',
    $wpdb->prefix . 'devxpert_knowledge_base',
);

foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS $table" );
}
