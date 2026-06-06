<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap devxpert-admin">
    <div class="devxpert-admin-header">
        <div>
            <p class="devxpert-admin-eyebrow">Configuration</p>
            <h1 class="devxpert-admin-title"><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p class="devxpert-admin-subtitle">Manage chatbot visibility, project qualification, and SMTP notifications from one place.</p>
        </div>
        <div class="devxpert-admin-actions">
            <span class="devxpert-admin-chip">Frontend chatbot</span>
            <span class="devxpert-admin-chip">Lead capture</span>
            <span class="devxpert-admin-chip">SMTP delivery</span>
        </div>
    </div>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('devxpert_chatbot_settings');
        $brand_name   = get_option('devxpert_brand_name', 'DevXpert');
        $chat_title   = get_option('devxpert_chatbot_title', $brand_name . ' Digital Project Assistant');
        $chat_subtitle = get_option('devxpert_chatbot_subtitle', 'Ask about custom websites, e-commerce, or performance optimization.');
        $brand_color  = get_option('devxpert_brand_accent_color', '#2563EB');
        ?>

        <div class="devxpert-admin-panel">
            <div class="devxpert-admin-panel-heading">
                <div>
                    <span class="devxpert-admin-kicker">Branding</span>
                    <h2 class="devxpert-admin-panel-title">Brand Controls</h2>
                    <p class="devxpert-admin-panel-copy">Manage the brand name, chatbot header text, and accent color used across the frontend widget, popup, emails, and admin screens.</p>
                </div>
            </div>
            <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="devxpert_brand_name">Brand Name</label></th>
                    <td>
                        <input type="text" name="devxpert_brand_name" id="devxpert_brand_name" value="<?php echo esc_attr($brand_name); ?>" class="regular-text" />
                        <p class="description">Used in emails, labels, and chatbot defaults.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="devxpert_chatbot_title">Chatbot Title</label></th>
                    <td>
                        <input type="text" name="devxpert_chatbot_title" id="devxpert_chatbot_title" value="<?php echo esc_attr($chat_title); ?>" class="regular-text" />
                        <p class="description">Shown in the chatbot header and email header.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="devxpert_chatbot_subtitle">Chatbot Subtitle</label></th>
                    <td>
                        <input type="text" name="devxpert_chatbot_subtitle" id="devxpert_chatbot_subtitle" value="<?php echo esc_attr($chat_subtitle); ?>" class="large-text" />
                        <p class="description">Small supporting line shown under the chatbot title.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="devxpert_brand_accent_color">Brand Accent Color</label></th>
                    <td>
                        <div class="devxpert-admin-inline">
                            <input type="color" name="devxpert_brand_accent_color" id="devxpert_brand_accent_color" value="<?php echo esc_attr($brand_color); ?>" />
                            <input type="text" id="devxpert_brand_accent_color_text" value="<?php echo esc_attr($brand_color); ?>" class="regular-text" />
                            <span class="devxpert-admin-chip">Live brand color</span>
                        </div>
                        <p class="description">Used for buttons, highlights, cards, and email header styling.</p>
                    </td>
                </tr>
            </tbody>
            </table>
        </div>

        <div class="devxpert-admin-panel">
            <div class="devxpert-admin-panel-heading">
                <div>
                    <span class="devxpert-admin-kicker">Chatbot</span>
                    <h2 class="devxpert-admin-panel-title">Chatbot Settings</h2>
                    <p class="devxpert-admin-panel-copy">Control where the assistant appears, how it opens, and who receives captured leads.</p>
                </div>
            </div>
            <table class="form-table" role="presentation">
            <tbody>

                <tr>
                    <th scope="row"><label for="devxpert_chatbot_enabled">Enable Chatbot</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="devxpert_chatbot_enabled" id="devxpert_chatbot_enabled" value="1"
                                <?php checked(get_option('devxpert_chatbot_enabled', true), true); ?> />
                            Enable the chatbot on your website
                        </label>
                        <p class="description">Uncheck to temporarily disable the chatbot without uninstalling the plugin.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="devxpert_chatbot_auto_open">Auto Open</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="devxpert_chatbot_auto_open" id="devxpert_chatbot_auto_open" value="1"
                                <?php checked(get_option('devxpert_chatbot_auto_open', true), true); ?> />
                            Automatically open chatbot when user scrolls
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="devxpert_chatbot_scroll_trigger">Scroll Trigger (%)</label></th>
                    <td>
                        <input type="number" name="devxpert_chatbot_scroll_trigger" id="devxpert_chatbot_scroll_trigger"
                            value="<?php echo esc_attr(get_option('devxpert_chatbot_scroll_trigger', 50)); ?>"
                            min="0" max="100" step="5" class="regular-text" />
                        <p class="description">Percentage of page scroll to trigger auto-open. Default: 50%</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="devxpert_chatbot_notification_email">Notification Email</label></th>
                    <td>
                        <input type="email" name="devxpert_chatbot_notification_email" id="devxpert_chatbot_notification_email"
                            value="<?php echo esc_attr(get_option('devxpert_chatbot_notification_email', get_option('admin_email'))); ?>"
                            class="regular-text" />
                        <p class="description">Receives all lead &amp; newsletter alerts. Separate multiple with commas.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="devxpert_chatbot_display_mode">Display On</label></th>
                    <td>
                        <?php $display_mode = get_option('devxpert_chatbot_display_mode', 'all_pages'); ?>
                        <select name="devxpert_chatbot_display_mode" id="devxpert_chatbot_display_mode" class="regular-text">
                            <option value="all_pages"      <?php selected($display_mode, 'all_pages'); ?>>All Pages</option>
                            <option value="homepage_only"  <?php selected($display_mode, 'homepage_only'); ?>>Homepage Only</option>
                            <option value="specific_pages" <?php selected($display_mode, 'specific_pages'); ?>>Specific Pages</option>
                        </select>
                    </td>
                </tr>

                <tr id="devxpert_specific_pages_row" style="<?php echo ($display_mode !== 'specific_pages') ? 'display:none;' : ''; ?>">
                    <th scope="row"><label for="devxpert_chatbot_specific_pages">Specific Pages</label></th>
                    <td>
                        <textarea name="devxpert_chatbot_specific_pages" id="devxpert_chatbot_specific_pages"
                            rows="6" class="large-text code"><?php echo esc_textarea(get_option('devxpert_chatbot_specific_pages', '')); ?></textarea>
                        <p class="description">One page per line. Supports: full URL, partial URL (<code>/services</code>), <code>id:123</code>, <code>slug:about</code></p>
                    </td>
                </tr>
            </tbody>
            </table>
        </div>

        <div class="devxpert-admin-panel">
            <div class="devxpert-admin-panel-heading">
                <div>
                    <span class="devxpert-admin-kicker">Email Delivery</span>
                    <h2 class="devxpert-admin-panel-title">SMTP Email Settings</h2>
                    <p class="devxpert-admin-panel-copy">Use a dedicated sender setup for lead alerts and newsletter notifications, then verify it with a test email.</p>
                </div>
            </div>
            <table class="form-table" role="presentation">
            <tbody>

                <!-- Enable SMTP -->
                <tr>
                    <th scope="row"><label for="devxpert_smtp_enabled">Enable SMTP</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="devxpert_smtp_enabled" id="devxpert_smtp_enabled" value="1"
                                <?php checked(get_option('devxpert_smtp_enabled', false), true); ?> />
                            Use custom SMTP server to send emails
                        </label>
                        <p class="description">
                            When enabled, all plugin emails (lead alerts, newsletter notifications) use your SMTP server.<br>
                            When disabled, WordPress default PHP mail is used.
                        </p>
                    </td>
                </tr>

                <!-- SMTP Host -->
                <tr class="devxpert-smtp-field">
                    <th scope="row"><label for="devxpert_smtp_host">SMTP Host</label></th>
                    <td>
                        <input type="text" name="devxpert_smtp_host" id="devxpert_smtp_host"
                            value="<?php echo esc_attr(get_option('devxpert_smtp_host', '')); ?>"
                            class="regular-text" placeholder="smtp.gmail.com" />
                        <p class="description">
                            <strong>Common providers:</strong><br>
                            Gmail: <code>smtp.gmail.com</code> &nbsp;|&nbsp;
                            Outlook / Office 365: <code>smtp.office365.com</code> &nbsp;|&nbsp;
                            Yahoo: <code>smtp.mail.yahoo.com</code> &nbsp;|&nbsp;
                            SendGrid: <code>smtp.sendgrid.net</code> &nbsp;|&nbsp;
                            Mailgun: <code>smtp.mailgun.org</code>
                        </p>
                    </td>
                </tr>

                <!-- SMTP Port -->
                <tr class="devxpert-smtp-field">
                    <th scope="row"><label for="devxpert_smtp_port">SMTP Port</label></th>
                    <td>
                        <input type="number" name="devxpert_smtp_port" id="devxpert_smtp_port"
                            value="<?php echo esc_attr(get_option('devxpert_smtp_port', 587)); ?>"
                            class="small-text" min="1" max="65535" />
                        <p class="description">
                            <code>587</code> — TLS (recommended) &nbsp;|&nbsp;
                            <code>465</code> — SSL &nbsp;|&nbsp;
                            <code>25</code> — None (not recommended)
                        </p>
                    </td>
                </tr>

                <!-- Encryption -->
                <tr class="devxpert-smtp-field">
                    <th scope="row"><label for="devxpert_smtp_encryption">Encryption</label></th>
                    <td>
                        <?php $enc = get_option('devxpert_smtp_encryption', 'tls'); ?>
                        <select name="devxpert_smtp_encryption" id="devxpert_smtp_encryption">
                            <option value="tls" <?php selected($enc, 'tls'); ?>>TLS — Port 587 (Recommended)</option>
                            <option value="ssl" <?php selected($enc, 'ssl'); ?>>SSL — Port 465</option>
                            <option value=""    <?php selected($enc, '');    ?>>None — Port 25 (Not Recommended)</option>
                        </select>
                    </td>
                </tr>

                <!-- SMTP Username -->
                <tr class="devxpert-smtp-field">
                    <th scope="row"><label for="devxpert_smtp_username">SMTP Username</label></th>
                    <td>
                        <input type="text" name="devxpert_smtp_username" id="devxpert_smtp_username"
                            value="<?php echo esc_attr(get_option('devxpert_smtp_username', '')); ?>"
                            class="regular-text" placeholder="your@email.com" autocomplete="off" />
                        <p class="description">Usually your full email address.</p>
                    </td>
                </tr>

                <!-- SMTP Password -->
                <tr class="devxpert-smtp-field">
                    <th scope="row"><label for="devxpert_smtp_password">SMTP Password</label></th>
                    <td>
                        <div class="devxpert-admin-inline">
                            <input type="password" name="devxpert_smtp_password" id="devxpert_smtp_password"
                                value="<?php echo esc_attr(get_option('devxpert_smtp_password', '')); ?>"
                                class="regular-text" autocomplete="new-password" />
                            <button type="button" id="devxpert-toggle-password" class="button button-secondary">
                                👁 Show
                            </button>
                        </div>
                        <p class="description">
                            For Gmail, use an <strong>App Password</strong> — not your normal Google password.<br>
                            <a href="https://myaccount.google.com/apppasswords" target="_blank">Generate a Gmail App Password →</a>
                        </p>
                    </td>
                </tr>

                <!-- From Email -->
                <tr class="devxpert-smtp-field">
                    <th scope="row"><label for="devxpert_smtp_from_email">From Email</label></th>
                    <td>
                        <input type="email" name="devxpert_smtp_from_email" id="devxpert_smtp_from_email"
                            value="<?php echo esc_attr(get_option('devxpert_smtp_from_email', get_option('admin_email'))); ?>"
                            class="regular-text" placeholder="noreply@yoursite.com" />
                        <p class="description">The address emails will appear to come from.</p>
                    </td>
                </tr>

                <!-- From Name -->
                <tr class="devxpert-smtp-field">
                    <th scope="row"><label for="devxpert_smtp_from_name">From Name</label></th>
                    <td>
                        <input type="text" name="devxpert_smtp_from_name" id="devxpert_smtp_from_name"
                            value="<?php echo esc_attr(get_option('devxpert_smtp_from_name', get_bloginfo('name'))); ?>"
                            class="regular-text" placeholder="<?php echo esc_attr($brand_name); ?>" />
                        <p class="description">The sender name shown in the inbox.</p>
                    </td>
                </tr>

                <!-- Test Email -->
                <tr class="devxpert-smtp-field">
                    <th scope="row">Send Test Email</th>
                    <td>
                        <button type="button" id="devxpert-smtp-test" class="button devxpert-button-primary">
                            📨 Send Test Email
                        </button>
                        <span id="devxpert-smtp-test-result" style="margin-left:15px;font-weight:600;font-size:14px;"></span>
                        <p class="description">
                            Sends a test email to <strong><?php echo esc_html(get_option('devxpert_chatbot_notification_email', get_option('admin_email'))); ?></strong>.<br>
                            <em>Save your settings first before testing.</em>
                        </p>
                    </td>
                </tr>

            </tbody>
            </table>
        </div>

        <?php submit_button('Save Settings'); ?>
    </form>
</div>

<style>
.devxpert-admin .form-table th { padding: 18px 10px 18px 0; vertical-align: top; }
.devxpert-admin .form-table td { padding: 14px 10px; }
.devxpert-smtp-field { transition: opacity .2s ease; }
.devxpert-smtp-field.smtp-hidden { opacity: .35; pointer-events: none; }
code { background: #f1f1f1; padding: 2px 5px; border-radius: 3px; font-size: 12px; }
</style>

<script>
jQuery(document).ready(function ($) {

    /* ── Show/hide specific pages textarea ───────────── */
    $('#devxpert_chatbot_display_mode').on('change', function () {
        $('#devxpert_specific_pages_row').toggle($(this).val() === 'specific_pages');
    });

    /* ── Grey out SMTP fields when disabled ──────────── */
    function toggleSmtpFields() {
        var on = $('#devxpert_smtp_enabled').is(':checked');
        $('.devxpert-smtp-field').toggleClass('smtp-hidden', !on);
    }
    toggleSmtpFields();
    $('#devxpert_smtp_enabled').on('change', toggleSmtpFields);

    /* ── Show / hide password ────────────────────────── */
    $('#devxpert-toggle-password').on('click', function () {
        var $f = $('#devxpert_smtp_password');
        var hide = $f.attr('type') === 'text';
        $f.attr('type', hide ? 'password' : 'text');
        $(this).text(hide ? '👁 Show' : '🙈 Hide');
    });

    $('#devxpert_brand_accent_color').on('input change', function () {
        $('#devxpert_brand_accent_color_text').val($(this).val());
    });

    $('#devxpert_brand_accent_color_text').on('input change', function () {
        var value = $(this).val();
        if (/^#[0-9a-fA-F]{6}$/.test(value)) {
            $('#devxpert_brand_accent_color').val(value);
        }
    });

    /* ── Send test email ─────────────────────────────── */
    $('#devxpert-smtp-test').on('click', function () {
        var $btn = $(this);
        var $msg = $('#devxpert-smtp-test-result');
        $btn.prop('disabled', true).text('⏳ Sending…');
        $msg.text('').css('color', '');

        $.post(ajaxurl, {
            action: 'devxpert_smtp_test',
            nonce:  '<?php echo wp_create_nonce("devxpert_chatbot_nonce"); ?>'
        })
        .done(function (r) {
            if (r.success) {
                $msg.text('✅ ' + r.data.message).css('color', '#4CAF50');
            } else {
                $msg.text('❌ ' + r.data.message).css('color', '#e74c3c');
            }
        })
        .fail(function () {
            $msg.text('❌ Request failed. Check browser console.').css('color', '#e74c3c');
        })
        .always(function () {
            $btn.prop('disabled', false).text('📨 Send Test Email');
        });
    });

});
</script>
