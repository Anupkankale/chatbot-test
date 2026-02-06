# YALLO Talent Chatbot - Quick Start Guide

## 🚀 Get Started in 5 Minutes

### What This Plugin Does
Adds a professional AI chatbot to your WordPress site that:
- Captures qualified leads automatically
- Guides visitors through a consultation process
- Sends you email notifications
- Stores all data in WordPress database
- Works perfectly on mobile devices

### Brand Colors
- Primary: **#BFA25E** (Gold)
- Background: **#1a1a1a** (Dark)
- Theme: **Dark Mode**

---

## Installation (3 Steps)

### 1. Upload & Activate
```
WordPress Admin → Plugins → Add New → Upload Plugin
→ Choose yallo-talent-chatbot.zip → Install → Activate
```

### 2. Configure Settings
```
YALLO Chatbot → Settings
→ ✅ Enable Chatbot
→ ✅ Auto Open (enabled)
→ Scroll Trigger: 50%
→ Notification Email: your@email.com
→ Save Settings
```

### 3. Test It
- Visit your website
- Scroll down 50% of the page
- Click the gold button in bottom-right
- Complete a test conversation
- Check: YALLO Chatbot → Leads

**Done! 🎉**

---

## File Structure

```
yallo-talent-chatbot/
├── 📄 yallo-talent-chatbot.php    (Main plugin)
├── 📄 README.md                    (Full documentation)
├── 📄 INSTALLATION.md              (Detailed setup)
├── 📄 CHANGELOG.md                 (Version history)
├── 📁 admin/
│   ├── settings.php               (Settings page)
│   └── leads.php                  (Leads dashboard)
├── 📁 assets/
│   ├── css/chatbot.css           (Dark theme styles)
│   └── js/chatbot.js             (Chat functionality)
└── 📁 templates/
    └── chatbot.php                (HTML structure)
```

---

## Key Features

### Conversation Flow
1. **Welcome Message** with 4 options
2. **Service Selection** (Hire talent / Stabilize project / EA support / Need guidance)
3. **Lead Capture** (9 questions including name, email, company, pain points)
4. **Confirmation** message with next steps

### Admin Features
- **Dashboard**: View all leads with statistics
- **Email Alerts**: Instant notifications
- **Details View**: Click to see full lead information
- **Bulk Actions**: Delete multiple leads at once

### Design Features
- ✅ Dark theme (black background)
- ✅ YALLO gold (#BFA25E) accents
- ✅ Smooth animations
- ✅ Typing indicators
- ✅ Mobile responsive
- ✅ Accessible (WCAG 2.1)

---

## Customization Quick Tips

### Change Colors
**File:** `assets/css/chatbot.css`
```css
/* Find and replace #BFA25E with your color */
background: #BFA25E; → background: #YOUR_COLOR;
```

### Change Questions
**File:** `assets/js/chatbot.js`
```javascript
// Around line 25, edit the questions array
answer: "Your custom greeting message"
```

### Change Auto-Open Timing
**Admin:** YALLO Chatbot → Settings
```
Scroll Trigger: 30-70% (experiment to find sweet spot)
```

---

## Troubleshooting

### Chatbot Not Showing?
- ✅ Plugin activated?
- ✅ "Enable Chatbot" checked in settings?
- ✅ Clear browser cache (Ctrl+Shift+R)

### Emails Not Arriving?
- Install "WP Mail SMTP" plugin
- Configure with Gmail/SendGrid
- Check spam folder

### Mobile Issues?
- Clear cache
- Test in incognito mode
- Ensure theme doesn't override CSS

---

## Support

**Email:** support@yallo.com  
**Website:** https://yallo.com  
**Docs:** See README.md for full documentation

---

## What Gets Captured

Each lead includes:
- ✅ Full name & email
- ✅ Company & location
- ✅ Industry & platforms
- ✅ Service needs
- ✅ Pain points
- ✅ Page URL
- ✅ Timestamp
- ✅ IP address

---

## Next Steps After Installation

1. **Test thoroughly** - Complete the full conversation
2. **Check email** - Verify you receive notifications
3. **Review leads** - Go to YALLO Chatbot → Leads
4. **Customize** - Adjust colors/questions if needed
5. **Train team** - Show them how to access leads
6. **Monitor** - Check daily for first week

---

## Pro Tips

💡 **Scroll Trigger**: 50% works best for most sites  
💡 **Email Setup**: Use SMTP for reliable delivery  
💡 **Mobile Test**: Always test on real devices  
💡 **Privacy Policy**: Update to mention data collection  
💡 **Response Time**: Aim to contact leads within 24 hours  

---

## Requirements

- WordPress 5.8+
- PHP 7.4+
- MySQL 5.6+
- Modern browser

---

## Quick Links

- [Full Documentation](README.md)
- [Installation Guide](INSTALLATION.md)
- [Changelog](CHANGELOG.md)

---

**Version:** 1.0.0  
**License:** GPL v2 or later  
**Made with ❤️ by YALLO**

---

## At a Glance

| Feature | Status |
|---------|--------|
| Lead Capture | ✅ |
| Email Notifications | ✅ |
| Mobile Responsive | ✅ |
| Dark Theme | ✅ |
| Admin Dashboard | ✅ |
| Auto-Open | ✅ |
| AJAX Powered | ✅ |
| Accessible | ✅ |
| Secure | ✅ |
| Easy Setup | ✅ |

**You're ready to capture leads! 🎯**
