<!-- DevXpert Newsletter Popup -->
<?php $brand_name = get_option('devxpert_brand_name', 'DevXpert'); ?>
<div id="devxpert-newsletter-popup" class="devxpert-newsletter-overlay" style="display: none;">
    <div
        class="devxpert-newsletter-container"
        role="dialog"
        aria-modal="true"
        aria-labelledby="devxpert-newsletter-title"
        aria-describedby="devxpert-newsletter-description"
    >
        <button type="button" id="devxpert-newsletter-close" class="devxpert-newsletter-close" aria-label="Close newsletter popup">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div class="devxpert-newsletter-content">
            <p class="devxpert-newsletter-eyebrow"><?php echo esc_html($brand_name); ?> Insights</p>

            <!-- Icon/Logo Area -->
            <div class="devxpert-newsletter-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>
            
            <!-- Title -->
            <h2 id="devxpert-newsletter-title" class="devxpert-newsletter-title">
                <?php echo esc_html(get_option('devxpert_newsletter_title', 'Stay Updated with ' . $brand_name)); ?>
            </h2>
            
            <!-- Description -->
            <p id="devxpert-newsletter-description" class="devxpert-newsletter-description">
                <?php echo esc_html(get_option('devxpert_newsletter_description', 'Get the latest insights on tech talent and enterprise architecture delivered to your inbox.')); ?>
            </p>

            <div class="devxpert-newsletter-benefits" aria-hidden="true">
                <span class="devxpert-newsletter-benefit">Talent trends</span>
                <span class="devxpert-newsletter-benefit">Delivery insights</span>
                <span class="devxpert-newsletter-benefit">No spam</span>
            </div>
            
            <!-- Form -->
            <form id="devxpert-newsletter-form" class="devxpert-newsletter-form">
                <div class="devxpert-newsletter-field">
                    <label for="devxpert-newsletter-name" class="devxpert-sr-only">Your name</label>
                    <input 
                        type="text" 
                        id="devxpert-newsletter-name" 
                        name="name"
                        placeholder="Your name"
                        class="devxpert-newsletter-input"
                        autocomplete="name"
                    />
                </div>
                
                <div class="devxpert-newsletter-field">
                    <label for="devxpert-newsletter-email" class="devxpert-sr-only">Your email address</label>
                    <input 
                        type="email" 
                        id="devxpert-newsletter-email" 
                        name="email"
                        placeholder="Work email address"
                        required
                        class="devxpert-newsletter-input"
                        autocomplete="email"
                    />
                </div>
                
                <button 
                    type="submit" 
                    id="devxpert-newsletter-submit" 
                    class="devxpert-newsletter-button"
                >
                    <?php echo esc_html(get_option('devxpert_newsletter_button_text', 'Subscribe Now')); ?>
                </button>
                
                <p class="devxpert-newsletter-privacy">
                    One useful update at a time. Unsubscribe whenever you want.
                </p>
            </form>
            
            <!-- Success Message (hidden by default) -->
            <div id="devxpert-newsletter-success" class="devxpert-newsletter-success" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <h3>Thank You!</h3>
                <p>You've successfully subscribed to our newsletter.</p>
            </div>
            
            <!-- Error Message (hidden by default) -->
            <div id="devxpert-newsletter-error" class="devxpert-newsletter-error" style="display: none;" aria-live="polite">
                <p></p>
            </div>
        </div>
    </div>
</div>
