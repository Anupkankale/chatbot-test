<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?> - Settings</h1>
    
    <?php settings_errors(); ?>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('yallo_chatbot_settings');
        do_settings_sections('yallo_chatbot_settings');
        ?>
        
        <table class="form-table" role="presentation">
            <tbody>
                <!-- Enable/Disable Chatbot -->
                <tr>
                    <th scope="row">
                        <label for="yallo_chatbot_enabled">Enable Chatbot</label>
                    </th>
                    <td>
                        <label for="yallo_chatbot_enabled">
                            <input 
                                type="checkbox" 
                                name="yallo_chatbot_enabled" 
                                id="yallo_chatbot_enabled" 
                                value="1"
                                <?php checked(get_option('yallo_chatbot_enabled', true), true); ?>
                            />
                            Enable the chatbot on your website
                        </label>
                        <p class="description">Uncheck to temporarily disable the chatbot without uninstalling the plugin.</p>
                    </td>
                </tr>
                
                <!-- Auto Open -->
                <tr>
                    <th scope="row">
                        <label for="yallo_chatbot_auto_open">Auto Open</label>
                    </th>
                    <td>
                        <label for="yallo_chatbot_auto_open">
                            <input 
                                type="checkbox" 
                                name="yallo_chatbot_auto_open" 
                                id="yallo_chatbot_auto_open" 
                                value="1"
                                <?php checked(get_option('yallo_chatbot_auto_open', true), true); ?>
                            />
                            Automatically open chatbot when user scrolls
                        </label>
                        <p class="description">The chatbot will open automatically when the user reaches the scroll trigger percentage.</p>
                    </td>
                </tr>
                
                <!-- Scroll Trigger Percentage -->
                <tr>
                    <th scope="row">
                        <label for="yallo_chatbot_scroll_trigger">Scroll Trigger (%)</label>
                    </th>
                    <td>
                        <input 
                            type="number" 
                            name="yallo_chatbot_scroll_trigger" 
                            id="yallo_chatbot_scroll_trigger" 
                            value="<?php echo esc_attr(get_option('yallo_chatbot_scroll_trigger', 50)); ?>"
                            min="0"
                            max="100"
                            step="5"
                            class="regular-text"
                        />
                        <p class="description">Percentage of page scroll to trigger auto-open (0-100). Default: 50%</p>
                    </td>
                </tr>
                
                <!-- Notification Email -->
                <tr>
                    <th scope="row">
                        <label for="yallo_chatbot_notification_email">Notification Email</label>
                    </th>
                    <td>
                        <input 
                            type="email" 
                            name="yallo_chatbot_notification_email" 
                            id="yallo_chatbot_notification_email" 
                            value="<?php echo esc_attr(get_option('yallo_chatbot_notification_email', get_option('admin_email'))); ?>"
                            class="regular-text"
                        />
                        <p class="description">Email address to receive new lead notifications. Multiple emails can be comma-separated.</p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button('Save Settings'); ?>
    </form>
    
    <hr>
    
    <h2>About YALLO Chatbot</h2>
    <div class="card">
        <h3>Plugin Information</h3>
        <p><strong>Version:</strong> <?php echo YALLO_CHATBOT_VERSION; ?></p>
        <p><strong>Brand Color:</strong> #BFA25E</p>
        <p><strong>Theme:</strong> Dark Mode</p>
        
        <h3>Features</h3>
        <ul style="list-style: disc; margin-left: 20px;">
            <li>Intelligent conversation flow with conditional logic</li>
            <li>Lead capture and consultation workflow</li>
            <li>Email notifications for new leads</li>
            <li>Auto-open on scroll trigger</li>
            <li>Mobile responsive design</li>
            <li>Accessibility compliant (WCAG 2.1)</li>
            <li>Dark theme with YALLO brand colors</li>
            <li>Database storage for all leads</li>
        </ul>
        
        <h3>Quick Links</h3>
        <ul style="list-style: disc; margin-left: 20px;">
            <li><a href="<?php echo admin_url('admin.php?page=yallo-chatbot-leads'); ?>">View All Leads</a></li>
            <li><a href="https://yallo.com" target="_blank">YALLO Website</a></li>
        </ul>
    </div>
    
    <hr>
    
    <div class="card" style="border-left: 4px solid #BFA25E;">
        <h3>💡 Tips for Best Results</h3>
        <ul style="list-style: disc; margin-left: 20px;">
            <li><strong>Scroll Trigger:</strong> Set between 30-60% for optimal engagement without being intrusive</li>
            <li><strong>Notification Email:</strong> Use a team inbox or CRM integration email for better lead management</li>
            <li><strong>Testing:</strong> Test the chatbot in incognito mode to see the first-time user experience</li>
            <li><strong>Mobile:</strong> The chatbot is fully responsive and works great on mobile devices</li>
        </ul>
    </div>
</div>

<style>
.wrap h1 {
    color: #1a1a1a;
}

.wrap .card {
    max-width: 800px;
    padding: 20px;
    margin: 20px 0;
}

.wrap .card h3 {
    margin-top: 0;
    color: #BFA25E;
}

.form-table th {
    padding: 20px 10px 20px 0;
}

.form-table td {
    padding: 15px 10px;
}
</style>
