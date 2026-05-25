<?php
/**
 * DevXpert Chatbot - RAG AI Settings Page
 */

if (!defined('ABSPATH')) exit;

require_once DEVXPERT_CHATBOT_PLUGIN_DIR . 'devxpert-chatbot-rag-ai.php';

$rag = new DEVXPERT_Chatbot_RAG();
$stats = $rag->get_stats();

// Handle form submissions
if (isset($_POST['devxpert_save_ai_settings'])) {
    check_admin_referer('devxpert_ai_settings');
    
    update_option('devxpert_claude_api_key', sanitize_text_field($_POST['claude_api_key']));
    update_option('devxpert_ai_enabled', isset($_POST['ai_enabled']) ? 1 : 0);
    update_option('devxpert_ai_fallback_message', sanitize_textarea_field($_POST['ai_fallback_message']));
    
    echo '<div class="notice notice-success"><p>✅ Settings saved!</p></div>';
}

if (isset($_POST['devxpert_scrape_website'])) {
    check_admin_referer('devxpert_ai_settings');
    
    $count = $rag->scrape_website();
    echo '<div class="notice notice-success"><p>✅ Scraped ' . $count . ' pages successfully!</p></div>';
    
    $stats = $rag->get_stats(); // Refresh stats
}

$ai_enabled = get_option('devxpert_ai_enabled', false);
$api_key = get_option('devxpert_claude_api_key', '');
$fallback_message = get_option('devxpert_ai_fallback_message', "I don't have specific information about that. Would you like to speak with our team?");
$brand_name = get_option('devxpert_brand_name', 'DevXpert');
?>

<div class="wrap devxpert-admin devxpert-ai-settings-wrap">
    <div class="devxpert-admin-header">
        <div>
            <p class="devxpert-admin-eyebrow">AI Workspace</p>
            <h1 class="devxpert-admin-title"><?php echo esc_html($brand_name); ?> AI Chatbot</h1>
            <p class="devxpert-admin-subtitle">Configure Claude access, maintain the knowledge base, and test retrieval-backed answers before exposing them to visitors.</p>
        </div>
        <div class="devxpert-admin-actions">
            <span class="devxpert-admin-chip">Claude API</span>
            <span class="devxpert-admin-chip">Knowledge base</span>
            <span class="devxpert-admin-chip">Live testing</span>
        </div>
    </div>
    
    <!-- Stats Dashboard -->
    <div class="devxpert-stat-grid">
        <div class="devxpert-stat-card">
            <p class="devxpert-stat-label">Knowledge Chunks</p>
            <p class="devxpert-stat-value"><?php echo number_format($stats['total_chunks']); ?></p>
            <p class="devxpert-stat-note">Stored retrieval units available to the chatbot.</p>
        </div>
        
        <div class="devxpert-stat-card">
            <p class="devxpert-stat-label">Pages Indexed</p>
            <p class="devxpert-stat-value"><?php echo number_format($stats['total_pages']); ?></p>
            <p class="devxpert-stat-note">Published posts and pages currently scraped into the knowledge base.</p>
        </div>
        
        <div class="devxpert-stat-card">
            <p class="devxpert-stat-label">Last Updated</p>
            <p class="devxpert-stat-value"><?php echo $stats['last_updated'] ? date('M j', strtotime($stats['last_updated'])) : 'Never'; ?></p>
            <p class="devxpert-stat-note"><?php echo $stats['last_updated'] ? esc_html(date('Y g:i A', strtotime($stats['last_updated']))) : 'Run a scrape to initialize the index.'; ?></p>
        </div>
    </div>
    <div class="devxpert-admin-grid">
        <div class="devxpert-span-8">
            <div class="devxpert-admin-panel">
                <div class="devxpert-admin-panel-heading">
                    <div>
                        <span class="devxpert-admin-kicker">Setup</span>
                        <h2 class="devxpert-admin-panel-title">AI Settings</h2>
                        <p class="devxpert-admin-panel-copy">Enable answer generation, store the Claude API key, and define the fallback copy shown when the bot lacks enough context.</p>
                    </div>
                </div>
                <form method="post">
                    <?php wp_nonce_field('devxpert_ai_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="ai_enabled">Enable AI Chatbot</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="ai_enabled" 
                                           id="ai_enabled" 
                                           value="1" 
                                           <?php checked($ai_enabled, 1); ?>>
                                    Enable intelligent AI responses
                                </label>
                                <p class="description">
                                    When enabled, chatbot will use Claude AI to answer questions based on your website content.
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="claude_api_key">Claude API Key *</label>
                            </th>
                            <td>
                                <input type="password" 
                                       name="claude_api_key" 
                                       id="claude_api_key" 
                                       value="<?php echo esc_attr($api_key); ?>" 
                                       class="regular-text"
                                       placeholder="sk-ant-...">
                                <p class="description">
                                    Get your API key from <a href="https://console.anthropic.com/settings/keys" target="_blank">Anthropic Console</a>
                                    <br>Estimated cost: around $0.003 per conversation.
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ai_fallback_message">Fallback Message</label>
                            </th>
                            <td>
                                <textarea name="ai_fallback_message" 
                                          id="ai_fallback_message" 
                                          rows="3" 
                                          class="large-text"><?php echo esc_textarea($fallback_message); ?></textarea>
                                <p class="description">
                                    Used when AI does not have enough indexed context to answer confidently.
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" name="devxpert_save_ai_settings" class="button button-primary button-large">
                            💾 Save Settings
                        </button>
                    </p>
                </form>
            </div>

            <div class="devxpert-admin-panel">
                <div class="devxpert-admin-panel-heading">
                    <div>
                        <span class="devxpert-admin-kicker">Indexing</span>
                        <h2 class="devxpert-admin-panel-title">Knowledge Base</h2>
                        <p class="devxpert-admin-panel-copy">Scrape current published content whenever your service pages or company information changes.</p>
                    </div>
                </div>
                <form method="post">
                    <?php wp_nonce_field('devxpert_ai_settings'); ?>
                    
                    <p>
                        <button type="submit" 
                                name="devxpert_scrape_website" 
                                class="button button-secondary button-large"
                                onclick="return confirm('This will scrape all published pages and posts. Continue?');">
                            🔄 Scrape Website Now
                        </button>
                    </p>
                    
                    <p class="description">
                        Run this after content updates to keep retrieval results aligned with the live site.
                    </p>
                </form>
            </div>

            <div class="devxpert-admin-panel devxpert-test-panel">
                <div class="devxpert-admin-panel-heading">
                    <div>
                        <span class="devxpert-admin-kicker">Testing</span>
                        <h2 class="devxpert-admin-panel-title">Test AI Response</h2>
                        <p class="devxpert-admin-panel-copy">Try a real question and inspect both the generated answer and the linked source pages.</p>
                    </div>
                </div>
                
                <div class="devxpert-test-form">
                    <input type="text" 
                           id="devxpert-test-question" 
                           placeholder="e.g. What services does <?php echo esc_attr($brand_name); ?> offer?"
                           class="large-text">
                    <button type="button" id="devxpert-test-ai" class="button button-secondary">
                        Ask AI
                    </button>
                </div>
                
                <div id="devxpert-test-response" style="display: none; margin-top: 20px;">
                    <h4>AI Response:</h4>
                    <div class="devxpert-ai-response"></div>
                    
                    <h4 style="margin-top: 15px;">Sources:</h4>
                    <div class="devxpert-ai-sources"></div>
                </div>
            </div>
        </div>

        <div class="devxpert-span-4">
            <div class="devxpert-admin-panel">
                <div class="devxpert-admin-panel-heading">
                    <div>
                        <span class="devxpert-admin-kicker">How It Works</span>
                        <h2 class="devxpert-admin-panel-title">RAG Flow</h2>
                    </div>
                </div>
                <div class="devxpert-info-panel">
        <ol>
            <li><strong>Scrape:</strong> Extract content from all your pages/posts</li>
            <li><strong>Store:</strong> Save content chunks in database</li>
            <li><strong>Search:</strong> When user asks a question, find relevant chunks</li>
            <li><strong>Generate:</strong> Send chunks + question to Claude AI</li>
            <li><strong>Respond:</strong> AI generates intelligent answer based on your content</li>
        </ol>
                </div>
            </div>

            <div class="devxpert-admin-panel">
                <span class="devxpert-admin-kicker">Cost</span>
                <h3 class="devxpert-admin-panel-title">Estimated Usage</h3>
        <ul>
            <li>~$0.003 per conversation</li>
            <li>1000 conversations = ~$3</li>
            <li>10,000 conversations = ~$30/month</li>
        </ul>
            </div>

            <div class="devxpert-admin-panel">
                <span class="devxpert-admin-kicker">Benefits</span>
                <h3 class="devxpert-admin-panel-title">Why Use It</h3>
        <ul>
            <li>✅ Answers based on YOUR content (not generic)</li>
            <li>✅ Always up-to-date (re-scrape anytime)</li>
            <li>✅ No hallucinations (uses only your content)</li>
            <li>✅ Shows sources (transparency)</li>
            <li>✅ Very affordable</li>
        </ul>
            </div>
        </div>
    </div>
</div>

<style>
.devxpert-ai-settings-wrap {
    max-width: 1200px;
}

.devxpert-info-panel {
    background: #f7f8fb;
    border: 1px solid #e5e7eb;
    padding: 18px;
    border-radius: 14px;
}

.devxpert-info-panel ol {
    margin-left: 20px;
}

.devxpert-test-panel {
    margin-bottom: 0;
}

.devxpert-test-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.devxpert-test-form input {
    flex: 1;
}

.devxpert-ai-response {
    background: #f9f9f9;
    padding: 15px;
    border-left: 4px solid var(--dx-accent);
    border-radius: 4px;
}

.devxpert-ai-sources {
    background: #f0f0f0;
    padding: 15px;
    border-radius: 4px;
}

.devxpert-ai-sources a {
    display: block;
    margin-bottom: 5px;
    text-decoration: none;
}

.devxpert-ai-sources a:hover {
    text-decoration: underline;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#devxpert-test-ai').on('click', function() {
        const question = $('#devxpert-test-question').val().trim();
        
        if (!question) {
            alert('Please enter a question');
            return;
        }
        
        $(this).prop('disabled', true).text('Thinking...');
        
        $.post(ajaxurl, {
            action: 'devxpert_test_ai',
            nonce: '<?php echo wp_create_nonce('devxpert_ai_test'); ?>',
            message: question
        }, function(response) {
            $('#devxpert-test-ai').prop('disabled', false).text('Ask AI');
            
            if (response.success) {
                $('.devxpert-ai-response').html(response.data.message);
                
                let sourcesHtml = '';
                if (response.data.sources && response.data.sources.length > 0) {
                    response.data.sources.forEach(source => {
                        sourcesHtml += `<a href="${source.url}" target="_blank">📄 ${source.title}</a>`;
                    });
                } else {
                    sourcesHtml = '<em>No sources found</em>';
                }
                $('.devxpert-ai-sources').html(sourcesHtml);
                
                $('#devxpert-test-response').slideDown();
            } else {
                alert('Error: ' + response.data.message);
            }
        });
    });
});
</script>
 
 
