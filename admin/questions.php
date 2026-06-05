<?php
/**
 * DevXpert Chatbot - Questions Editor
 * Simple admin page to edit chatbot questions
 */

if (!defined('ABSPATH')) exit;

// Handle Save
if (isset($_POST['devxpert_save_questions']) && check_admin_referer('devxpert_questions_nonce')) {
    $questions_json = stripslashes($_POST['devxpert_questions_data']);
    update_option('devxpert_chatbot_questions_json', $questions_json);
    echo '<div class="notice notice-success is-dismissible"><p><strong>✅ Questions saved!</strong> Changes are live on your site.</p></div>';
}

// Handle Reset
if (isset($_POST['devxpert_reset_questions']) && check_admin_referer('devxpert_questions_nonce')) {
    delete_option('devxpert_chatbot_questions_json');
    echo '<div class="notice notice-success is-dismissible"><p><strong>🔄 Reset complete!</strong> Using default questions.</p></div>';
}

// Load questions
$questions_json = get_option('devxpert_chatbot_questions_json', '');
if (empty($questions_json)) {
    $brand_name = get_option('devxpert_brand_name', 'DevXpert');
    // Defaults - match current chatbot.js structure exactly
    $default = array(
        'welcome' => array(
            'text' => "Hi, we're {$brand_name} 👋\n\nWe build high-performance websites and digital solutions that help businesses grow. How can we help you today?",
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
            array('key' => 'pain', 'text' => "Briefly describe your **project goals** or the problem you're solving."),
        ),
        'final' => array(
            'text' => "Thanks, **{name}**. We've received your project details.\n\nA senior strategist will review your requirements and reach out to **{email}** within 24 hours."
        )
    );
    $questions_json = json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
?>

<div class="wrap devxpert-admin">
    <div class="devxpert-admin-header">
        <div>
            <p class="devxpert-admin-eyebrow">Conversation Design</p>
            <h1 class="devxpert-admin-title">Edit Chatbot Questions</h1>
            <p class="devxpert-admin-subtitle">Maintain the live conversation flow in one JSON document. Changes apply immediately to the public chatbot.</p>
        </div>
        <div class="devxpert-admin-actions">
            <span class="devxpert-admin-chip">Welcome flow</span>
            <span class="devxpert-admin-chip">Lead capture</span>
            <span class="devxpert-admin-chip">Response copy</span>
        </div>
    </div>

    <form method="post">
        <?php wp_nonce_field('devxpert_questions_nonce'); ?>
        
        <div class="devxpert-admin-grid">
            <div class="devxpert-span-8">
                <div class="devxpert-admin-panel">
                    <div class="devxpert-admin-panel-heading">
                        <div>
                            <span class="devxpert-admin-kicker">Editor</span>
                            <h2 class="devxpert-admin-panel-title">Questions JSON Editor</h2>
                            <p class="devxpert-admin-panel-copy">Use this editor for service buttons, qualification steps, and higher-converting CTA copy.</p>
                        </div>
                    </div>
            
            <textarea 
                name="devxpert_questions_data" 
                id="json-editor" 
                rows="40" 
                style="width:100%;font-family:'Courier New',monospace;font-size:13px;line-height:1.7;padding:18px;background:#fafafa;"
            ><?php echo esc_textarea($questions_json); ?></textarea>
            
                    <div class="devxpert-admin-inline" style="margin-top:15px;">
                        <button type="button" id="validate-json" class="button button-secondary">
                    ✓ Validate JSON
                </button>
                <span id="json-status" style="margin-left:12px;font-weight:600;"></span>
                    </div>

                    <p style="margin-top:25px;">
                        <button type="submit" name="devxpert_save_questions" class="button button-primary button-large">
                            💾 Save Questions
                        </button>
                        
                        <button 
                            type="submit" 
                            name="devxpert_reset_questions" 
                            class="button button-secondary" 
                            onclick="return confirm('Are you sure? This will reset all questions to defaults and cannot be undone.');"
                            style="margin-left:10px;"
                        >
                            🔄 Reset to Defaults
                        </button>
                        
                        <a href="<?php echo admin_url('admin.php?page=devxpert-chatbot'); ?>" class="button" style="margin-left:10px;">
                            ← Back to Settings
                        </a>
                    </p>
                </div>
            </div>

            <div class="devxpert-span-4">
                <div class="devxpert-admin-panel">
                    <div class="devxpert-admin-panel-heading">
                        <div>
                            <span class="devxpert-admin-kicker">Reference</span>
                            <h2 class="devxpert-admin-panel-title">What This JSON Controls</h2>
                            <p class="devxpert-admin-panel-copy">These sections map directly to the live chatbot flow.</p>
                        </div>
                    </div>
                    <ul style="margin-left:18px;line-height:1.8;">
                        <li><strong>welcome.text</strong> — First message users see</li>
                        <li><strong>services</strong> — Both the initial buttons (<code>text</code>) and the bot's response (<code>message</code>).</li>
                        <li><strong>consultation</strong> — The lead capture questions</li>
                        <li><strong>final.text</strong> — Closing thank you message</li>
                    </ul>
                </div>

                <div class="devxpert-admin-note">
                    <strong>Tips</strong>
                    <ul style="margin:8px 0 0 18px;line-height:1.7;">
                        <li>Use <code>\n</code> for line breaks in text</li>
                        <li>Use <code>{name}</code> or <code>{email}</code> to insert user data dynamically</li>
                        <li>Use <code>**text**</code> for bold and <code>*text*</code> for italic</li>
                        <li>Validate before saving to catch syntax issues early</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
#json-status.valid { color: #0a0; }
#json-status.invalid { color: #d00; }
code { background: #f1f1f1; padding: 2px 6px; border-radius: 3px; font-size: 12px; font-family: 'Courier New', monospace; }
</style>

<script>
jQuery(document).ready(function($) {
    // Validate JSON
    $('#validate-json').on('click', function() {
        const json = $('#json-editor').val();
        const $status = $('#json-status');
        try {
            const parsed = JSON.parse(json);
            $status.text('✓ Valid JSON!').removeClass('invalid').addClass('valid');
            
            // Check structure
            if (!parsed.welcome || !parsed.services || !parsed.consultation) {
                $status.text('⚠️ Valid JSON but missing required sections (welcome, services, consultation)').removeClass('valid').addClass('invalid');
            }
        } catch (e) {
            $status.text('✗ Invalid JSON: ' + e.message).removeClass('valid').addClass('invalid');
        }
    });

    // Auto-validate on page load
    $('#validate-json').click();
});
</script>
