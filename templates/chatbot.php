<?php
$chat_title    = get_option('devxpert_chatbot_title', get_option('devxpert_brand_name', 'DevXpert') . ' Talent Assistant');
$chat_subtitle = get_option('devxpert_chatbot_subtitle', 'Ask about talent, delivery support, or enterprise architecture.');
?>
<div id="devxpert-chatbot-container" class="devxpert-chatbot-wrapper">
    <!-- Chatbot Window -->
    <div
        id="devxpert-chatbot-window"
        class="devxpert-chat-window"
        role="dialog"
        aria-labelledby="devxpert-chat-title"
        aria-describedby="devxpert-chat-subtitle"
        aria-hidden="true"
    >
        <div class="devxpert-chat-container">
            <!-- Header -->
            <div class="devxpert-chat-header">
                <div class="devxpert-chat-header-copy">
                    <div class="devxpert-chat-header-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <div class="devxpert-chat-meta">
                        <p class="devxpert-chat-status">Live assistant</p>
                        <h3 id="devxpert-chat-title" class="devxpert-chat-title"><?php echo esc_html($chat_title); ?></h3>
                        <p id="devxpert-chat-subtitle" class="devxpert-chat-subtitle"><?php echo esc_html($chat_subtitle); ?></p>
                    </div>
                </div>
                <button type="button" id="devxpert-chat-close" class="devxpert-close-btn" aria-label="Close chat">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <!-- Messages Container -->
            <div id="devxpert-messages-container" class="devxpert-messages" role="log" aria-live="polite" aria-relevant="additions text">
                <!-- Messages will be added here dynamically -->
            </div>
            
            <!-- Input Area -->
            <div class="devxpert-input-area">
                <form id="devxpert-chat-form" class="devxpert-input-form">
                    <label for="devxpert-message-input" class="devxpert-sr-only">Type your message</label>
                    <input 
                        type="text" 
                        id="devxpert-message-input" 
                        class="devxpert-message-input" 
                        placeholder="Type your message or use the options..."
                        autocomplete="off"
                    />
                    <button type="submit" id="devxpert-send-btn" class="devxpert-send-btn" aria-label="Send message">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </form>
                <p class="devxpert-input-help">Use the quick options for faster routing, or type your question directly.</p>
            </div>
        </div>
    </div>
    
    <!-- Toggle Button -->
    <button
        type="button"
        id="devxpert-chat-toggle"
        class="devxpert-chat-toggle"
        aria-label="Open chat"
        aria-controls="devxpert-chatbot-window"
        aria-expanded="false"
    >
        <svg id="devxpert-chat-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <svg id="devxpert-close-icon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
        <span class="devxpert-toggle-badge" aria-hidden="true">Ask</span>
    </button>
</div>
