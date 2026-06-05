/**
 * DevXpert Talent Chatbot - Main JavaScript
 * v1.0.7 — Robust toggle logic, Cyan & Black theme
 */

(function($) {
    'use strict';

    const DEVXPERT_CHATBOT = {

        // ── State ────────────────────────────────────────────
        isOpen: false,
        isBotTyping: false,
        isChatFinished: false,
        isInputDisabled: false,
        isConsultationActive: false,
        consultationStep: 0,
        hasAutoOpened: false,
        leadSavedEarly: false,
        initialisingTimer: null,

        // ── Data ─────────────────────────────────────────────
        messages: [],
        consultationData: {
            initial_intent: null,
            lead_type: null,
        },

        // ── DOM ──────────────────────────────────────────────
        $window: null,
        $toggle: null,
        $messagesContainer: null,
        $form: null,
        $input: null,
        $sendBtn: null,
        $chatIcon: null,
        $closeIcon: null,

        // ── Welcome + Service Questions ───────────────────────
        questions: [],
        consultationQuestions: [],
        
        // ── Init ─────────────────────────────────────────────
        init: function() {
            const self = this;
            
            // 1. Cache DOM elements immediately
            this.cacheDom();
            
            // 2. Bind Toggle Events immediately (so button is never "dead")
            this.bindToggleEvent();

            // 3. Load questions from WordPress, then bind rest
            this.loadQuestions().always(function() {
                self.bindFormEvents();
                self.bindMobileKeyboard();
                self.checkAutoOpen();
                console.log('✅ DevXpert Chatbot: Initialised');
            });
        },

        cacheDom: function() {
            this.$window            = $('#devxpert-chatbot-window');
            this.$toggle            = $('#devxpert-chat-toggle');
            this.$messagesContainer = $('#devxpert-messages-container');
            this.$form              = $('#devxpert-chat-form');
            this.$input             = $('#devxpert-message-input');
            this.$sendBtn           = $('#devxpert-send-btn');
            this.$chatIcon          = $('#devxpert-chat-icon');
            this.$closeIcon         = $('#devxpert-close-icon');
        },

        bindToggleEvent: function() {
            const self = this;
            this.$toggle.off('click').on('click', function(e) {
                e.preventDefault();
                self.toggleChat();
            });
            $('#devxpert-chat-close').off('click').on('click', function(e) {
                e.preventDefault();
                self.closeChat();
            });
        },

        bindFormEvents: function() {
            const self = this;

            this.$form.on('submit', function(e) {
                e.preventDefault();
                const msg = self.$input.val().trim();
                if (msg) {
                    self.$input.val('');
                    self.handleUserInput(msg);
                }
            });

            this.$sendBtn.on('click', function() {
                const msg = self.$input.val().trim();
                if (msg) {
                    self.$input.val('');
                    self.handleUserInput(msg);
                }
            });

            this.$input.on('keypress', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    const msg = self.$input.val().trim();
                    if (msg) {
                        self.$input.val('');
                        self.handleUserInput(msg);
                    }
                }
            });
        },

        // ── Load Questions Dynamically ─────────────────────────
        loadQuestions: function() {
            const self = this;
            const cached = localStorage.getItem('devxpert_questions');
            const cacheTime = localStorage.getItem('devxpert_questions_time');
            const now = Date.now();
            
            if (cached && cacheTime && (now - parseInt(cacheTime)) < 300000) {
                try {
                    const data = JSON.parse(cached);
                    self.applyQuestions(data);
                    return $.Deferred().resolve().promise();
                } catch(e) {
                    console.error('DevXpert: Cache parse error', e);
                }
            }
            
            return $.post(devxpertChatbot.ajaxUrl, {
                action: 'devxpert_get_questions'
            })
            .done(function(response) {
                if (response.success && response.data) {
                    localStorage.setItem('devxpert_questions', JSON.stringify(response.data));
                    localStorage.setItem('devxpert_questions_time', now.toString());
                    self.applyQuestions(response.data);
                } else {
                    self.useDefaultQuestions();
                }
            })
            .fail(function() {
                self.useDefaultQuestions();
            });
        },

        applyQuestions: function(data) {
            const self = this;
            const buildPrimaryCta = (service) => service.cta_primary || (service.lead_type === 'call' ? '📞 Book my strategy call' : '📋 Get matched options');
            const buildSecondaryCta = (service) => service.cta_secondary || (service.lead_type === 'call' ? '✉️ Send my requirements' : '💬 Talk to the team');

            this.questions = [{
                id: 0,
                keywords: ['hi', 'hello', 'start', 'menu'],
                answer: data.welcome.text,
                options: data.services.map((service, i) => ({
                    text: service.text,
                    nextId: 10 + i,
                    intent: service.intent,
                    leadType: service.lead_type
                }))
            }];
            
            data.services.forEach((service, i) => {
                this.questions.push({
                    id: 10 + i,
                    answer: service.message,
                    options: [
                        { text: buildPrimaryCta(service), nextId: 300, leadType: service.lead_type },
                        { text: buildSecondaryCta(service), nextId: 300, leadType: service.lead_type },
                        { text: '← Back', nextId: 0 }
                    ]
                });
            });
            
            this.consultationQuestions = data.consultation.map(q => ({
                key: q.key,
                text: q.text
            }));
        },

        useDefaultQuestions: function() {
            // Minimal fallback to avoid total failure
            const brand = (typeof devxpertChatbot !== 'undefined' ? devxpertChatbot.brandName : 'DevXpert');
            this.applyQuestions({
                welcome: { text: "Hi, we're " + brand + " 👋" },
                services: [
                    { text: 'Hire tech talent', message: "We help teams hire vetted specialists.", intent: 'hire', lead_type: 'details' },
                    { text: 'IT strategy help', message: "We provide architecture and strategy support.", intent: 'strategy', lead_type: 'call' }
                ],
                consultation: [
                    {key: 'name', text: "What's your **full name?**"},
                    {key: 'email', text: "Your **work email?**"}
                ]
            });
        },

        // ── Auto open ────────────────────────────────────────
        checkAutoOpen: function() {
            if (!devxpertChatbot.autoOpen) return;
            const self = this;
            $(window).on('scroll.devxpert', function() {
                const scrollPct = ($(window).scrollTop() / ($(document).height() - $(window).height())) * 100;
                if (scrollPct >= devxpertChatbot.scrollTrigger && !self.hasAutoOpened) {
                    self.hasAutoOpened = true;
                    $(window).off('scroll.devxpert');
                    self.openChat();
                }
            });
        },

        // ── Chat open / close / toggle ────────────────────────
        toggleChat: function() {
            const isCurrentlyOpen = this.$window.hasClass('devxpert-open');
            isCurrentlyOpen ? this.closeChat() : this.openChat();
        },

        openChat: function() {
            const self = this;
            this.isOpen = true;
            this.$window.addClass('devxpert-open').attr('aria-hidden', 'false');
            this.$toggle.addClass('devxpert-active').attr('aria-expanded', 'true');
            
            // Short delay before focus to ensure animation visibility
            setTimeout(() => { self.$input.trigger('focus'); }, 300);
            
            if (this.messages.length === 0) {
                // Clear any existing welcome timers to prevent double-starts
                if (this.initialisingTimer) clearTimeout(this.initialisingTimer);
                
                this.initialisingTimer = setTimeout(function() {
                    self.showTypingIndicator();
                    setTimeout(() => {
                        self.hideTypingIndicator();
                        self.askQuestionById(0);
                    }, 800);
                }, 400);
            }
        },

        closeChat: function() {
            this.isOpen = false;
            this.$window.removeClass('devxpert-open').attr('aria-hidden', 'true');
            this.$toggle.removeClass('devxpert-active').attr('aria-expanded', 'false');
            
            // Clean up timers if they closed it while it was "about to start"
            if (this.messages.length === 0 && this.initialisingTimer) {
                clearTimeout(this.initialisingTimer);
            }
        },

        // ── Message handling ───────────────────────────────────
        handleUserInput: function(userMessage) {
            if (this.isInputDisabled || this.isChatFinished) return;
            this.addMessage(userMessage, 'user');
            this.showTypingIndicator();

            if (devxpertChatbot.aiEnabled && !this.isConsultationActive) {
                this.getAIResponse(userMessage);
                return;
            }

            setTimeout(() => {
                this.hideTypingIndicator();
                if (this.isConsultationActive) {
                    this.handleConsultationInput(userMessage);
                } else {
                    this.processQuestion(userMessage);
                }
            }, 500);
        },

        getAIResponse: function(userMessage) {
            const self = this;
            const history = this.messages.slice(-6, -1).map(m => ({
                role:    m.sender === 'user' ? 'user' : 'assistant',
                content: m.text.replace(/\*\*(.*?)\*\*/g, '$1').replace(/<br>/g, '\n'),
            }));

            $.post(devxpertChatbot.ajaxUrl, {
                action:  'devxpert_ai_chat',
                nonce:   devxpertChatbot.nonce,
                message: userMessage,
                history: JSON.stringify(history),
            })
            .done(function(response) {
                self.hideTypingIndicator();
                if (response.success && response.data && response.data.message) {
                    self.addMessage(response.data.message, 'bot');
                } else {
                    self.processQuestion(userMessage);
                }
            })
            .fail(function() {
                self.hideTypingIndicator();
                self.processQuestion(userMessage);
            });
        },

        processQuestion: function(input) {
            const lower = input.toLowerCase();
            const q = this.questions.find(q => q.keywords.some(k => lower.includes(k)));
            if (q) {
                this.addMessage(q.answer, 'bot', q.options);
            } else {
                this.addMessage("How else can I help? Please choose an option below:", 'bot', this.questions[0].options);
            }
        },

        askQuestionById: function(id) {
            const q = this.questions.find(q => q.id === id);
            if (q) this.addMessage(q.answer, 'bot', q.options);
        },

        handleOptionClick: function(option) {
            this.addMessage(option.text, 'user');
            if (option.intent) this.consultationData.initial_intent = option.intent;
            if (option.leadType) this.consultationData.lead_type = option.leadType;

            this.showTypingIndicator();
            setTimeout(() => {
                this.hideTypingIndicator();
                if (option.action) {
                    this.handleAction(option.action, option);
                } else if (option.nextId === 300) {
                    this.startConsultation();
                } else if (option.nextId !== undefined) {
                    this.askQuestionById(option.nextId);
                }
            }, 600);
        },
        
        handleAction: function(action) {
            if (action === 'continue') {
                this.isInputDisabled = false;
                this.updateInputState();
                this.showTypingIndicator();
                setTimeout(() => {
                    this.hideTypingIndicator();
                    this.askConsultationQuestion();
                }, 400);
            }
        },

        // ── Consultation Flow ──────────────────────────────────
        startConsultation: function() {
            this.isConsultationActive = true;
            this.consultationStep     = 0;
            this.updateInputState();
            this.askConsultationQuestion();
        },

        askConsultationQuestion: function() {
            const q = this.consultationQuestions[this.consultationStep];
            if (!q) return;
            this.isInputDisabled = false;
            this.updateInputState();
            const text = q.text.replace('{name}', this.consultationData.name || '');
            this.addMessage(text, 'bot');
        },

        handleConsultationInput: function(answer) {
            const q = this.consultationQuestions[this.consultationStep];
            if (!q) return;

            if (q.key === 'email' && !this.isValidEmail(answer)) {
                this.addMessage("Please provide a valid work email address.", 'bot');
                return;
            }

            this.consultationData[q.key] = answer;
            this.consultationStep++;

            if (this.consultationStep === 2 && !this.leadSavedEarly) {
                this.saveEarlyLead();
            }

            if (this.consultationStep === 3) {
                if (this.leadSavedEarly) this.updateLead();
                this.showTypingIndicator();
                setTimeout(() => {
                    this.hideTypingIndicator();
                    this.addMessage(
                        `Thanks! Our team will get back to you within 24 hours. Would you like to provide more details about your requirement?`,
                        'bot',
                        [{ text: '📋 Provide More Details', action: 'continue' }]
                    );
                    this.isInputDisabled = true;
                    this.updateInputState();
                }, 600);
                return;
            }

            if (this.consultationStep < this.consultationQuestions.length) {
                this.showTypingIndicator();
                setTimeout(() => {
                    this.hideTypingIndicator();
                    this.askConsultationQuestion();
                }, 400);
            } else {
                this.finalizeConsultation();
            }
        },

        isValidEmail: (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email),

        finalizeConsultation: function() {
            this.isChatFinished  = true;
            this.updateInputState();
            const name = this.consultationData.name || 'there';
            this.addMessage(`Thank you, **${name}**! We've received your request and will be in touch shortly.`, 'bot');
            this.updateLead();
        },

        saveEarlyLead: function() {
            this.leadSavedEarly = true;
            $.post(devxpertChatbot.ajaxUrl, {
                action: 'devxpert_submit_lead',
                nonce: devxpertChatbot.nonce,
                name: this.consultationData.name || '',
                email: this.consultationData.email || '',
                initial_intent: this.consultationData.initial_intent || '',
                lead_type: this.consultationData.lead_type || '',
                page_url: window.location.href,
                early_save: true
            });
        },

        updateLead: function() {
            $.post(devxpertChatbot.ajaxUrl, {
                action: 'devxpert_update_lead',
                nonce: devxpertChatbot.nonce,
                email: this.consultationData.email || '',
                company: this.consultationData.company || '',
                location: this.consultationData.location || '',
                industry: this.consultationData.industry || '',
                platforms: this.consultationData.platforms || '',
                capabilities: this.consultationData.capabilities || '',
                service_type: this.consultationData.service_type || '',
                pain: this.consultationData.pain || ''
            });
        },

        // ── Rendering ─────────────────────────────────────────
        addMessage: function(text, sender, options = []) {
            this.messages.push({ text, sender, options });
            const $msg = $('<div>').addClass('devxpert-message').addClass(sender);
            const $bubble = $('<div>').addClass('devxpert-message-bubble');
            const html = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
            $bubble.html(html);
            $msg.append($bubble);

            if (sender === 'bot' && options.length > 0) {
                const $opts = $('<div>').addClass('devxpert-message-options');
                options.forEach(opt => {
                    $('<button>').addClass('devxpert-option-btn').text(opt.text)
                        .on('click', () => this.handleOptionClick(opt))
                        .appendTo($opts);
                });
                $msg.append($opts);
            }

            $msg.append($('<span>').addClass('devxpert-message-time').text(this.formatTime()));
            this.$messagesContainer.append($msg);
            this.scrollToBottom();
        },

        formatTime: () => {
            const now = new Date();
            return now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        },

        showTypingIndicator: function() {
            const $t = $('<div>').addClass('devxpert-message bot').attr('id', 'devxpert-typing');
            const $i = $('<div>').addClass('devxpert-typing-indicator');
            for (let i = 0; i < 3; i++) $i.append($('<span>').addClass('devxpert-typing-dot'));
            $t.append($i);
            this.$messagesContainer.append($t);
            this.scrollToBottom();
        },

        hideTypingIndicator: () => $('#devxpert-typing').remove(),

        updateInputState: function() {
            let placeholder = 'Type a message…';
            let disabled = false;
            if (this.isChatFinished) {
                placeholder = 'Chat ended – thank you!';
                disabled = true;
            } else if (this.isInputDisabled) {
                placeholder = 'Please choose an option above…';
                disabled = true;
            } else if (this.isConsultationActive) {
                placeholder = 'Type your answer…';
            }
            this.$input.prop('disabled', disabled).attr('placeholder', placeholder);
            this.$sendBtn.prop('disabled', disabled);
        },

        scrollToBottom: function() {
            const container = this.$messagesContainer[0];
            if (container) {
                setTimeout(() => { container.scrollTop = container.scrollHeight; }, 50);
            }
        },

        bindMobileKeyboard: function() {
            if (!window.visualViewport) return;
            const self = this;
            const $wrapper = this.$window.closest('.devxpert-chatbot-wrapper');
            window.visualViewport.addEventListener('resize', () => {
                if (!self.isOpen) return;
                const h = window.innerHeight - window.visualViewport.height - window.visualViewport.offsetTop;
                $wrapper.css('bottom', h > 100 ? (h + 10) + 'px' : '');
                self.$window.css('max-height', h > 100 ? (window.visualViewport.height - 90) + 'px' : '');
                self.scrollToBottom();
            });
        }
    };

    $(document).ready(() => DEVXPERT_CHATBOT.init());

})(jQuery);
