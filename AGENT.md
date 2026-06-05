# AGENT.md

## Purpose

This repository is a WordPress plugin that delivers three connected capabilities:

1. A frontend lead-capture chatbot for **Web Development Agency** project enquiries.
2. A frontend newsletter popup with subscriber capture.
3. An optional AI response layer backed by a local RAG-style knowledge base and Anthropic Claude.

The plugin is implemented in procedural WordPress style with one main class, template partials, admin pages, jQuery-driven frontend scripts, and custom database tables.

## Scope Boundaries

This plugin is responsible for:

- Rendering the chatbot and newsletter popup on the public site.
- Capturing and storing leads and newsletter subscribers.
- Sending notification emails, optionally through custom SMTP settings.
- Providing WordPress admin pages to configure chatbot, newsletter, SMTP, leads, subscribers, questions, and AI settings.
- Building and querying a lightweight knowledge base from published WordPress posts/pages.
- Sending user chat prompts to Anthropic when AI is configured.

This plugin is not responsible for:

- CRM syncs or external lead routing beyond email notifications and CSV export.
- General site search or content management outside the plugin’s own knowledge-base table.
- Complex analytics, consent management, or marketing automation.
- Multi-channel messaging beyond the embedded website UI.

## Entry Points

- `devxpert-talent-chatbot.php`
  Main plugin bootstrap, hook registration, AJAX handlers, admin menus, DB setup, email handling, and rendering logic.
- `devxpert-chatbot-rag-ai.php`
  RAG/AI helper: content scraping, chunk storage, knowledge search, and Claude API integration.

## Runtime Architecture

### Frontend

- `wp_footer` renders:
  - `templates/chatbot.php`
  - `templates/newsletter-popup.php`
- `wp_enqueue_scripts` conditionally loads:
  - `assets/css/chatbot.css`
  - `assets/js/chatbot.js`
  - `assets/css/newsletter.css`
  - `assets/js/newsletter.js`
- Frontend uses `admin-ajax.php` for all interactions.

### Admin

- `admin/settings.php`
  Core settings for chatbot, newsletter, and SMTP.
- `admin/questions.php`
  JSON editor for chatbot flow content.
- `admin/devxpert-chatbot-rag-settings.php`
  AI configuration, scrape trigger, and AI test UI.
- `admin/leads.php`
  Lead list, status updates, deletion, and CSV export.
- `admin/newsletter.php`
  Subscriber list, deletion, and CSV export.

### Data Storage

Custom tables:

- `{$wpdb->prefix}devxpert_chatbot_leads`
- `{$wpdb->prefix}devxpert_newsletter_subscribers`
- `{$wpdb->prefix}devxpert_knowledge_base`

WordPress options are used heavily for configuration and question content.

## Primary User Flows

### Chatbot Lead Capture

1. Visitor opens chatbot manually or via scroll-trigger auto-open.
2. Frontend script loads question config over AJAX.
3. Visitor selects an intent and progresses through consultation questions.
4. Lead is stored in `devxpert_chatbot_leads`.
5. Notification email is sent to configured recipients.
6. Admin can review, export, delete, or update lead status.

### Newsletter Capture

1. Popup appears after configured delay unless blocked by cookie/local storage rules.
2. Visitor submits name/email.
3. Subscriber is stored in `devxpert_newsletter_subscribers`.
4. Notification email is sent.
5. Admin can review, export, or delete subscribers.

### AI Chat

1. Admin adds Claude API key and enables AI settings.
2. Admin scrapes site content into `devxpert_knowledge_base`.
3. Frontend sends chat prompt plus recent history to AJAX.
4. Plugin searches relevant content chunks locally.
5. Prompt + context are sent to Anthropic.
6. AI response is returned to frontend.

## Important Technical Constraints

- This is a WordPress plugin, so changes must preserve hook timing, nonce validation, and `manage_options` capability boundaries.
- Frontend code is jQuery-based and expects localized globals such as `devxpertChatbot` and `devxpertNewsletter`.
- The chatbot and newsletter are intentionally independent toggles.
- Display targeting for chatbot is implemented in PHP and supports:
  - all pages
  - homepage only
  - specific pages via URL, `id:123`, or `slug:name`
- AI requests are rate-limited per IP with a transient.
- The RAG implementation is keyword/fulltext retrieval.

## High-Value Files

- `devxpert-talent-chatbot.php`
  Most business logic lives here.
- `devxpert-chatbot-rag-ai.php`
  AI behavior and knowledge indexing live here.
- `assets/js/chatbot.js`
  Main conversational UX, state machine, AJAX submissions.
- `assets/js/newsletter.js`
  Popup timing, validation, and subscription UX.
- `admin/questions.php`
  Defines/editors the chatbot conversation structure.

## Branding & UI

- **Identity:** Digital Project Assistant for Web Development Agencies.
- **Design System:** Navy Ink (`#0F172A`), Brand Blue (`#2563EB`), and Teal gradients.
- **Dynamic CSS:** Variables are injected via PHP based on user settings to keep frontend/backend synchronized.

## Safe Change Strategy

When modifying this plugin:

- Start from `devxpert-talent-chatbot.php` to understand the hook or AJAX entry point.
- Trace any frontend behavior through the matching template and JS file together.
- Preserve nonce checks and capability checks on all admin/AJAX paths.
- Preserve existing option names and DB schema unless intentionally migrating them.
- If changing lead, subscriber, or AI data flows, check both admin UI and frontend behavior.

## Practical Definition Of Done

A change in this plugin is usually complete only when:

- The relevant frontend/admin entry point still renders.
- The associated AJAX request still succeeds with nonce/capability checks intact.
- Data still persists to the expected option/table.
- Any affected email/export/admin page still works.
- No existing toggle or display-rule behavior is unintentionally broken.
