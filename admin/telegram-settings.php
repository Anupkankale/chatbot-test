<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap devxpert-admin">
    <div class="devxpert-admin-header">
        <div>
            <p class="devxpert-admin-eyebrow">Automation</p>
            <h1 class="devxpert-admin-title">Telegram Bot Integration</h1>
            <p class="devxpert-admin-subtitle">Connect a Telegram bot to the same service menu, AI replies, and lead-capture flow used by the website chatbot.</p>
        </div>
    </div>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('devxpert_chatbot_settings');
        $telegram_webhook_url = rest_url('devxpert/v1/telegram/webhook');
        $telegram_secret = get_option('devxpert_telegram_secret_token', '');
        ?>

        <div class="devxpert-admin-panel">
            <div class="devxpert-admin-panel-heading">
                <div>
                    <span class="devxpert-admin-kicker">Activation</span>
                    <h2 class="devxpert-admin-panel-title">Telegram Controls</h2>
                </div>
            </div>
            <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="devxpert_telegram_enabled">Enable Telegram</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="devxpert_telegram_enabled" id="devxpert_telegram_enabled" value="1"
                                <?php checked(get_option('devxpert_telegram_enabled', false), true); ?> />
                            Process Telegram bot messages through this plugin
                        </label>
                        <p class="description">When enabled, Telegram users can start with <code>/start</code> and continue into the lead form.</p>
                    </td>
                </tr>
                <tr class="devxpert-telegram-field">
                    <th scope="row"><label for="devxpert_telegram_bot_token">Bot Token</label></th>
                    <td>
                        <input type="password" name="devxpert_telegram_bot_token" id="devxpert_telegram_bot_token"
                            value="<?php echo esc_attr(get_option('devxpert_telegram_bot_token', '')); ?>"
                            class="regular-text code" autocomplete="new-password" placeholder="123456:ABC..." />
                        <p class="description">Create your bot in Telegram via <code>@BotFather</code> and paste the bot token here.</p>
                    </td>
                </tr>
                <tr class="devxpert-telegram-field">
                    <th scope="row"><label for="devxpert_telegram_secret_token">Webhook Secret Token</label></th>
                    <td>
                        <input type="text" name="devxpert_telegram_secret_token" id="devxpert_telegram_secret_token"
                            value="<?php echo esc_attr($telegram_secret); ?>"
                            class="regular-text code" />
                        <p class="description">Use the same secret when you register the Telegram webhook so the plugin can verify inbound requests.</p>
                    </td>
                </tr>
            </tbody>
            </table>
        </div>

        <div class="devxpert-admin-panel">
            <div class="devxpert-admin-panel-heading">
                <div>
                    <span class="devxpert-admin-kicker">Developer</span>
                    <h2 class="devxpert-admin-panel-title">Webhook Configuration</h2>
                </div>
            </div>
            <table class="form-table" role="presentation">
            <tbody>
                <tr class="devxpert-telegram-field">
                    <th scope="row">Webhook URL</th>
                    <td>
                        <input type="text" readonly class="large-text code" value="<?php echo esc_attr($telegram_webhook_url); ?>" />
                        <p class="description">Set this URL as your Telegram bot webhook after saving settings.</p>
                    </td>
                </tr>
                <tr class="devxpert-telegram-field">
                    <th scope="row">Setup Command</th>
                    <td>
                        <textarea readonly rows="4" class="large-text code">curl -X POST "https://api.telegram.org/bot&lt;YOUR_BOT_TOKEN&gt;/setWebhook" \
-d "url=<?php echo esc_attr($telegram_webhook_url); ?>" \
-d "secret_token=<?php echo esc_attr($telegram_secret); ?>"</textarea>
                        <p class="description">Replace <code>&lt;YOUR_BOT_TOKEN&gt;</code> with the saved token, then run the command once.</p>
                    </td>
                </tr>
            </tbody>
            </table>
        </div>

        <?php submit_button('💾 Save Telegram Settings'); ?>
    </form>
</div>

<style>
.devxpert-telegram-field { transition: opacity .2s ease; }
.devxpert-telegram-field.smtp-hidden { opacity: .35; pointer-events: none; }
</style>

<script>
jQuery(document).ready(function ($) {
    function toggleTelegramFields() {
        var on = $('#devxpert_telegram_enabled').is(':checked');
        $('.devxpert-telegram-field').toggleClass('smtp-hidden', !on);
    }
    toggleTelegramFields();
    $('#devxpert_telegram_enabled').on('change', toggleTelegramFields);
});
</script>
