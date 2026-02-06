# YALLO Talent Chatbot - Quick Installation Guide

## 📦 What You'll Get

A complete WordPress plugin that adds an intelligent chatbot to your website with:
- Dark theme design with YALLO brand color (#BFA25E)
- Automatic lead capture and consultation workflow
- Email notifications
- Admin dashboard for managing leads
- Mobile-responsive interface
- Auto-open on scroll feature

---

## 🚀 Step-by-Step Installation

### Step 1: Prepare the Plugin

1. Download all the plugin files
2. Ensure the folder structure looks like this:

```
yallo-talent-chatbot/
├── yallo-talent-chatbot.php
├── README.md
├── assets/
│   ├── css/
│   │   └── chatbot.css
│   └── js/
│       └── chatbot.js
├── admin/
│   ├── settings.php
│   └── leads.php
└── templates/
    └── chatbot.php
```

### Step 2: Install the Plugin

**Option A: Upload via WordPress Admin (Recommended)**

1. Create a ZIP file of the entire `yallo-talent-chatbot` folder
2. Log in to your WordPress admin panel
3. Go to **Plugins → Add New**
4. Click the **Upload Plugin** button at the top
5. Choose the ZIP file you created
6. Click **Install Now**
7. After installation completes, click **Activate Plugin**

**Option B: Manual Installation via FTP**

1. Connect to your website via FTP
2. Navigate to `/wp-content/plugins/`
3. Upload the entire `yallo-talent-chatbot` folder
4. Go to WordPress Admin → Plugins
5. Find "YALLO Talent Chatbot" and click **Activate**

### Step 3: Configure Settings

1. In WordPress admin, go to **YALLO Chatbot → Settings**
2. Configure these options:
   - ✅ **Enable Chatbot** - Make sure this is checked
   - ✅ **Auto Open** - Enable if you want automatic opening on scroll
   - **Scroll Trigger** - Set to 50% (recommended)
   - **Notification Email** - Enter your email for lead notifications

3. Click **Save Settings**

### Step 4: Test the Chatbot

1. Visit your website's homepage
2. Scroll down to 50% of the page (if auto-open is enabled)
3. Or click the gold circular button in the bottom-right corner
4. Test the conversation flow:
   - Click through the main menu options
   - Try the consultation workflow
   - Submit a test lead

### Step 5: Check Lead Submission

1. Go to **YALLO Chatbot → Leads** in WordPress admin
2. You should see your test lead
3. Check your email for the notification
4. Click "View Details" to see all information

---

## ⚙️ Post-Installation Setup

### Configure Email Delivery (Important!)

WordPress's default email function may not work on all servers. For reliable email delivery:

1. Install **WP Mail SMTP** plugin (free)
2. Configure it with your email provider:
   - Gmail
   - SendGrid
   - Mailgun
   - Your hosting provider's SMTP

### Recommended Settings

For best results:
- **Scroll Trigger:** 50-60% (balances engagement without being intrusive)
- **Auto Open:** Enabled (but test user feedback)
- **Notification Email:** Use a team inbox or CRM integration email

### Privacy Compliance

Add to your privacy policy:
```
We use a chatbot to help connect you with our services. When you use the chatbot, 
we collect your name, email, company information, and responses to our questions. 
This information is used solely to provide you with relevant services and will be 
stored securely. You can request deletion of your data by contacting us at [email].
```

---

## 🎨 Customization

### Change Brand Color

1. Open `assets/css/chatbot.css`
2. Find and replace all instances of `#BFA25E` with your color
3. Save and clear cache

### Modify Questions

1. Open `assets/js/chatbot.js`
2. Find the `questions` array (around line 25)
3. Modify the text, options, or flow
4. Save and test

### Adjust Auto-Open Timing

1. Go to **YALLO Chatbot → Settings**
2. Change **Scroll Trigger** percentage
3. Test on your website

---

## 🔍 Troubleshooting

### Chatbot Doesn't Appear

**Check:**
- ✅ Plugin is activated
- ✅ "Enable Chatbot" is checked in settings
- ✅ No JavaScript errors in browser console (F12)
- ✅ Theme doesn't have conflicts

**Solution:**
- Clear browser cache
- Try a different browser
- Temporarily switch to a default WordPress theme (Twenty Twenty-Four)

### Leads Not Saving

**Check:**
- ✅ Database table was created
- ✅ PHP error logs

**Solution:**
```php
// Run this in phpMyAdmin to check:
SELECT * FROM wp_yallo_chatbot_leads;
```

### Emails Not Sending

**Check:**
- ✅ Email address is correct in settings
- ✅ Check spam folder
- ✅ WordPress can send emails

**Solution:**
- Install WP Mail SMTP plugin
- Test email sending: **Tools → Site Health → Info → Mail**

### Chatbot Not Mobile Responsive

**Check:**
- ✅ CSS file loaded correctly
- ✅ No theme conflicts

**Solution:**
- Clear cache
- Test in incognito mode
- Check if theme has custom CSS overriding styles

---

## 📊 Features to Explore

### Admin Dashboard

- **View all leads** with sortable columns
- **Lead statistics** (today, this week, total)
- **Detailed information** for each lead
- **Bulk delete** unwanted leads
- **Pagination** for large datasets

### Lead Information Captured

Each lead includes:
- Full name & email
- Company & location
- Industry & platforms
- Capability gaps
- Service type needed
- Pain point description
- Initial intent
- Page URL where submitted
- IP address & timestamp

---

## 🎯 Best Practices

1. **Test Thoroughly** - Use incognito mode to test as a first-time visitor
2. **Monitor Daily** - Check leads dashboard at least once per day
3. **Respond Quickly** - Aim to contact leads within 24 hours
4. **Optimize Flow** - Adjust questions based on user feedback
5. **Mobile Test** - Always test on actual mobile devices
6. **Email Setup** - Configure SMTP for reliable delivery
7. **Backup Regularly** - Include the leads database table in backups

---

## 📞 Support

### Need Help?

- **Email:** support@yallo.com
- **Website:** https://yallo.com
- **Documentation:** Check the full README.md file

### Report Issues

Include these details:
- WordPress version
- PHP version
- Theme name
- Browser & device
- Error messages (from console)
- Steps to reproduce

---

## ✅ Installation Checklist

Before going live, verify:

- [ ] Plugin activated successfully
- [ ] Chatbot appears on website
- [ ] Conversation flow works correctly
- [ ] Test lead submission completes
- [ ] Lead appears in admin dashboard
- [ ] Email notification received
- [ ] Mobile version works properly
- [ ] Colors match YALLO branding
- [ ] Auto-open triggers at correct scroll
- [ ] Privacy policy updated
- [ ] Email SMTP configured
- [ ] Team trained on lead management

---

## 🎉 You're All Set!

Your YALLO Talent Chatbot is now ready to capture leads and engage visitors. 

**Next Steps:**
1. Share the website link with your team
2. Monitor initial leads closely
3. Gather user feedback
4. Optimize based on performance

**Pro Tip:** The first week is crucial. Monitor closely and make adjustments to the conversation flow and auto-open settings based on real user behavior.

---

**Made with ❤️ by YALLO | Version 1.0.0**
