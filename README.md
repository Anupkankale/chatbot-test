# YALLO Talent Chatbot WordPress Plugin

A professional, AI-powered chatbot for YALLO talent acquisition and consultation services. Features a sleek dark theme with the YALLO brand color (#BFA25E).

## Features

- ✅ **Intelligent Conversation Flow** - Multi-level question routing with conditional logic
- ✅ **Lead Capture System** - Complete consultation workflow with 9-step data collection
- ✅ **Auto-Open on Scroll** - Configurable scroll trigger for automatic chatbot opening
- ✅ **Email Notifications** - Instant notifications when new leads are submitted
- ✅ **Database Storage** - All leads stored in WordPress database with full details
- ✅ **Admin Dashboard** - View, manage, and export leads from WordPress admin
- ✅ **Mobile Responsive** - Fully optimized for all device sizes
- ✅ **Dark Theme** - Modern dark UI with YALLO brand colors
- ✅ **Accessibility** - WCAG 2.1 compliant with keyboard navigation and screen reader support
- ✅ **AJAX-Powered** - Smooth interactions without page reloads
- ✅ **Typing Indicators** - Visual feedback during bot responses
- ✅ **No Dependencies** - Only requires jQuery (included with WordPress)

## Installation

### Method 1: Upload via WordPress Admin

1. Download the plugin folder as a ZIP file
2. Go to WordPress Admin > Plugins > Add New
3. Click "Upload Plugin" and select the ZIP file
4. Click "Install Now" and then "Activate"

### Method 2: Manual Installation

1. Upload the `yallo-talent-chatbot` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to YALLO Chatbot > Settings to configure

## File Structure

```
yallo-talent-chatbot/
├── yallo-talent-chatbot.php    # Main plugin file
├── README.md                     # This file
├── assets/
│   ├── css/
│   │   └── chatbot.css          # Chatbot styles (dark theme)
│   └── js/
│       └── chatbot.js           # Chatbot functionality
├── admin/
│   ├── settings.php             # Settings page template
│   └── leads.php                # Leads management page
└── templates/
    └── chatbot.php              # Chatbot HTML template
```

## Configuration

### Settings Page

Access: **WordPress Admin > YALLO Chatbot > Settings**

Available options:
- **Enable Chatbot** - Turn the chatbot on/off
- **Auto Open** - Enable/disable automatic opening on scroll
- **Scroll Trigger** - Percentage of page scroll to trigger auto-open (0-100%)
- **Notification Email** - Email address(es) for lead notifications

### Brand Customization

The plugin uses the YALLO brand color: **#BFA25E**

To customize colors, edit `assets/css/chatbot.css` and replace `#BFA25E` with your desired color.

## Conversation Flow

### Main Menu Options:
1. Hire tech talent / build a squad
2. Stabilise a project / programme
3. EA / IT strategy support
4. Not sure – need guidance

### Consultation Questions:
1. Full name
2. Work email
3. Company name
4. Location (city + country)
5. Industry
6. Core platforms
7. Capability gaps
8. Service type
9. Main pain point

## Database Schema

The plugin creates a table: `wp_yallo_chatbot_leads`

**Fields:**
- `id` - Primary key
- `name` - Lead's full name
- `email` - Work email
- `company` - Company name
- `location` - City + Country
- `industry` - Business industry
- `platforms` - Core platforms used
- `capabilities` - Capability gaps
- `service_type` - Requested service
- `pain` - Main pain point (text)
- `initial_intent` - First selected option
- `lead_type` - Type of inquiry (details/call)
- `page_url` - Page where chatbot was used
- `created_at` - Submission timestamp
- `user_agent` - Browser information
- `ip_address` - User's IP address

## Admin Features

### Leads Dashboard

Access: **WordPress Admin > YALLO Chatbot > Leads**

Features:
- View all submitted leads in a table
- Statistics: Total leads, today's leads, this week's leads
- View full lead details with one click
- Bulk delete functionality
- Pagination for large datasets
- Export capabilities (future feature)

### Email Notifications

When a lead is submitted:
1. Data is saved to the database
2. Email notification is sent to configured address(es)
3. Email includes all lead information

## Developer Notes

### Hooks and Filters

The plugin follows WordPress best practices with proper hooks:

**Actions:**
- `wp_enqueue_scripts` - Enqueue frontend assets
- `wp_footer` - Render chatbot HTML
- `admin_menu` - Add admin pages
- `admin_init` - Register settings

**AJAX Endpoints:**
- `wp_ajax_yallo_submit_lead` - Handle lead submission (logged in)
- `wp_ajax_nopriv_yallo_submit_lead` - Handle lead submission (not logged in)

### Security Features

- ✅ Nonce verification for AJAX requests
- ✅ Data sanitization for all inputs
- ✅ SQL injection prevention with prepared statements
- ✅ XSS prevention with proper escaping
- ✅ CSRF protection on admin forms
- ✅ Direct file access prevention

### Performance

- Minimal JavaScript (single file, ~12KB minified)
- Optimized CSS (single file, ~8KB minified)
- No external dependencies
- Lazy loading (only loads when needed)
- Efficient database queries with indexes

## Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.6 or higher
- **jQuery:** Included with WordPress

## Troubleshooting

### Chatbot Not Appearing

1. Check if the plugin is activated
2. Go to Settings and ensure "Enable Chatbot" is checked
3. Clear browser cache
4. Check browser console for JavaScript errors

### Leads Not Being Saved

1. Check database table was created during activation
2. Verify AJAX URL in browser console network tab
3. Check PHP error logs for database errors
4. Ensure proper file permissions

### Email Notifications Not Sending

1. Verify notification email is set in Settings
2. Check WordPress email functionality (use WP Mail SMTP plugin if needed)
3. Check spam folder
4. Review server email logs

## Customization Examples

### Change Brand Color

Edit `assets/css/chatbot.css`:

```css
/* Replace all instances of #BFA25E with your color */
background: #BFA25E; /* Your color here */
```

### Modify Questions

Edit `assets/js/chatbot.js` in the `questions` array:

```javascript
questions: [
    {
        id: 0,
        keywords: ['hi', 'hello'],
        answer: "Your custom greeting",
        options: [/* your options */]
    }
]
```

### Add Custom Fields

1. Add field to consultation questions in `chatbot.js`
2. Update database schema in main plugin file
3. Add field to email template
4. Update leads admin page to display new field

## Support

For support, feature requests, or bug reports:
- Email: support@yallo.com
- Website: https://yallo.com

## License

GPL v2 or later

## Credits

- **Developer:** YALLO Development Team
- **Design:** YALLO Brand Team
- **Version:** 1.0.0
- **Last Updated:** 2024

## Changelog

### Version 1.0.0 (Initial Release)
- Complete chatbot functionality
- Lead capture and consultation workflow
- Admin dashboard for lead management
- Email notifications
- Dark theme with YALLO branding
- Mobile responsive design
- Accessibility features
- Auto-open on scroll
- Database storage
- Security hardening

## Future Enhancements

- [ ] Export leads to CSV
- [ ] CRM integration (Salesforce, HubSpot)
- [ ] Analytics dashboard
- [ ] A/B testing for conversation flows
- [ ] Multi-language support
- [ ] Custom CSS editor in admin
- [ ] Conversation history for returning visitors
- [ ] Lead scoring system
- [ ] Automated follow-up emails
- [ ] Integration with email marketing platforms

## Best Practices

1. **Test Before Launch** - Test the chatbot thoroughly in a staging environment
2. **Monitor Leads** - Check the leads dashboard regularly
3. **Optimize Scroll Trigger** - Test different percentages for best engagement
4. **Mobile Testing** - Always test on actual mobile devices
5. **Email Setup** - Configure SMTP for reliable email delivery
6. **Regular Backups** - Backup your WordPress database regularly
7. **Update Regularly** - Keep the plugin updated for security and features

## Privacy & GDPR

The plugin collects user information for business purposes. Ensure your privacy policy includes:
- What data is collected
- How it's used
- How long it's stored
- User rights (access, deletion)

Consider adding a privacy notice in the chatbot before collecting data.

---

**Made with ❤️ by YALLO**
# chatbot-test
