<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap devxpert-admin">
    <div class="devxpert-admin-header">
        <div>
            <p class="devxpert-admin-eyebrow">Engagement</p>
            <h1 class="devxpert-admin-title">Newsletter Popup Settings</h1>
            <p class="devxpert-admin-subtitle">Configure when the popup appears and how the signup offer is presented to visitors.</p>
        </div>
        <div class="devxpert-admin-actions">
            <a href="<?php echo admin_url('admin.php?page=devxpert-chatbot-newsletter'); ?>" class="button">View Subscribers</a>
        </div>
    </div>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('devxpert_chatbot_settings');
        $brand_name = get_option('devxpert_brand_name', 'DevXpert');
        ?>

        <div class="devxpert-admin-panel">
            <div class="devxpert-admin-panel-heading">
                <div>
                    <span class="devxpert-admin-kicker">Activation</span>
                    <h2 class="devxpert-admin-panel-title">Visibility Controls</h2>
                </div>
            </div>
            <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="devxpert_newsletter_enabled">Enable Newsletter Popup</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="devxpert_newsletter_enabled" id="devxpert_newsletter_enabled" value="1"
                                <?php checked(get_option('devxpert_newsletter_enabled', false), true); ?> />
                            Show newsletter subscription popup to visitors
                        </label>
                        <p class="description">Works independently — shows even if the chatbot is disabled.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="devxpert_newsletter_delay">Popup Delay (ms)</label></th>
                    <td>
                        <input type="number" name="devxpert_newsletter_delay" id="devxpert_newsletter_delay"
                            value="<?php echo esc_attr(get_option('devxpert_newsletter_delay', 5000)); ?>"
                            min="0" max="60000" step="1000" class="regular-text" />
                        <p class="description">Default: 5000ms (5 seconds)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="devxpert_newsletter_show_once">Show Once Per Visitor</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="devxpert_newsletter_show_once" id="devxpert_newsletter_show_once" value="1"
                                <?php checked(get_option('devxpert_newsletter_show_once', true), true); ?> />
                            Only show popup once every 30 days per visitor
                        </label>
                    </td>
                </tr>
            </tbody>
            </table>
        </div>

        <div class="devxpert-admin-panel">
            <div class="devxpert-admin-panel-heading">
                <div>
                    <span class="devxpert-admin-kicker">Content</span>
                    <h2 class="devxpert-admin-panel-title">Copy & Branding</h2>
                </div>
            </div>
            <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="devxpert_newsletter_title">Popup Title</label></th>
                    <td>
                        <input type="text" name="devxpert_newsletter_title" id="devxpert_newsletter_title"
                            value="<?php echo esc_attr(get_option('devxpert_newsletter_title', 'Stay Updated with ' . $brand_name)); ?>"
                            class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="devxpert_newsletter_description">Popup Description</label></th>
                    <td>
                        <textarea name="devxpert_newsletter_description" id="devxpert_newsletter_description"
                            rows="3" class="large-text"><?php echo esc_textarea(get_option('devxpert_newsletter_description', 'Get the latest insights on web development and digital optimization delivered to your inbox.')); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="devxpert_newsletter_button_text">Button Text</label></th>
                    <td>
                        <input type="text" name="devxpert_newsletter_button_text" id="devxpert_newsletter_button_text"
                            value="<?php echo esc_attr(get_option('devxpert_newsletter_button_text', 'Subscribe Now')); ?>"
                            class="regular-text" />
                    </td>
                </tr>
            </tbody>
            </table>
        </div>

        <?php submit_button('💾 Save Newsletter Settings'); ?>
    </form>
</div>
