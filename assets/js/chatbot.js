/**
 * YALLO Talent Chatbot - Main JavaScript
 */

(function($) {
    'use strict';
    
    // Chatbot Configuration
    const YALLO_CHATBOT = {
        
        // State variables
        isOpen: false,
        isBotTyping: false,
        isChatFinished: false,
        isInputDisabled: false,
        isConsultationActive: false,
        consultationStep: 0,
        hasAutoOpened: false,
        
        // Data storage
        messages: [],
        consultationData: {
            initial_intent: null,
            lead_type: null,
        },
        
        // DOM elements
        $window: null,
        $toggle: null,
        $messagesContainer: null,
        $form: null,
        $input: null,
        $sendBtn: null,
        $chatIcon: null,
        $closeIcon: null,
        
        // Questions configuration
        questions: [
            {
                id: 0,
                keywords: ['hi', 'hello', 'start', 'menu'],
                answer: "Hi, we're YALLO 👋\n\nWe connect tech strategy with architect-vetted talent so your programmes don't stall.\n\nHow can we help you today?",
                options: [
                    { text: 'Hire tech talent / build a squad', nextId: 10, intent: 'Hire tech talent / build a squad' },
                    { text: 'Stabilise a project / programme', nextId: 11, intent: 'Stabilise a project / programme' },
                    { text: 'EA / IT strategy support', nextId: 12, intent: 'TS/EA as a Service' },
                    { text: 'Not sure – need guidance', nextId: 13, intent: 'Not sure – need guidance' }
                ]
            },
            {
                id: 10,
                keywords: ['hire', 'talent', 'squad'],
                answer: "Got it – you want help with **tech talent / squads**.\n\nWe specialise in architect-led, contract and project-based talent across AI, Data, Cloud, SAP, Oracle, Microsoft, Salesforce, Blue Yonder and more – with vetted profiles in ~72 hours.",
                options: [
                    { text: 'Share my details', nextId: 300, leadType: 'details' },
                    { text: 'Book a call', nextId: 300, leadType: 'call' },
                    { text: 'Back to start', nextId: 0 }
                ]
            },
            {
                id: 11,
                keywords: ['stabilise', 'project', 'programme'],
                answer: "You're looking to **stabilise a project / programme**.\n\nYALLO uses enterprise architects and delivery leads to quickly assess where talent, capability or role clarity is causing risk – then fill the gaps with the right specialists.",
                options: [
                    { text: 'Share my details', nextId: 300, leadType: 'details' },
                    { text: 'Book a call', nextId: 300, leadType: 'call' },
                    { text: 'Back to start', nextId: 0 }
                ]
            },
            {
                id: 12,
                keywords: ['ea', 'strategy', 'architecture'],
                answer: "You need **TS/EA as a Service** – architecture and strategy support.\n\nWe provide Chief Architect / domain architect capacity to align roadmaps, platforms and talent – without locking you into big consulting contracts.",
                options: [
                    { text: 'Share my details', nextId: 300, leadType: 'details' },
                    { text: 'Book a call', nextId: 300, leadType: 'call' },
                    { text: 'Back to start', nextId: 0 }
                ]
            },
            {
                id: 13,
                keywords: ['not sure', 'guidance'],
                answer: "No problem – many leaders just know something's stuck.\n\nWe can quickly understand your context and recommend the right mix of talent and architecture support for you.",
                options: [
                    { text: 'Share my details', nextId: 300, leadType: 'details' },
                    { text: 'Book a call', nextId: 300, leadType: 'call' },
                    { text: 'Back to start', nextId: 0 }
                ]
            }
        ],
        
        // Consultation questions
        consultationQuestions: [
            { key: 'name', text: "Great, let's get you connected to the right expert. What's your **full name**?" },
            { key: 'email', text: "Thanks, {name}. What's the **best work email** for you?" },
            { key: 'company', text: "What's your **company or organisation** name?" },
            { key: 'location', text: "Where are you based? Please share **city + country** (e.g. Dubai, UAE or London, UK)." },
            {
                key: 'industry',
                text: "Which **industry** best fits your business?\n\nOptions:\n- Retail & Consumer\n- Manufacturing & Logistics\n- Banking & Financial Services\n- Government & Public Sector\n- Healthcare & Life Science\n- Telco & Media\n- Other"
            },
            {
                key: 'platforms',
                text: "Which core **platforms** are most relevant to your current roadmap?\n\nOptions:\n- SAP\n- Oracle\n- Microsoft\n- Salesforce\n- Blue Yonder\n- Workday\n- Other / Not sure"
            },
            {
                key: 'capabilities',
                text: "Where do you feel the **biggest capability gap** is right now?\n\nOptions:\n- Data & AI\n- Digital & DevOps\n- Cloud & Infrastructure\n- Cybersecurity\n- Integration & Middleware\n- Emerging Technologies"
            },
            {
                key: 'service_type',
                text: "Which sounds **closest to what you need**?\n\nOptions:\n- Talent in a Box\n- TS/EA as a Service\n- Managed IT CoE\n- Not sure – need guidance"
            },
            {
                key: 'pain',
                text: "In **1–2 lines**, what's the main **pain point** you want us to help with?\n\nExample: \"We can't hire strong SAP architects fast enough in KSA\" or \"Data platform migration is delayed due to talent gaps\"."
            }
        ],
        
        /**
         * Initialize the chatbot
         */
        init: function() {
            this.cacheDom();
            this.bindEvents();
            this.checkAutoOpen();
        },
        
        /**
         * Cache DOM elements
         */
        cacheDom: function() {
            this.$window = $('#yallo-chatbot-window');
            this.$toggle = $('#yallo-chat-toggle');
            this.$messagesContainer = $('#yallo-messages-container');
            this.$form = $('#yallo-chat-form');
            this.$input = $('#yallo-message-input');
            this.$sendBtn = $('#yallo-send-btn');
            this.$chatIcon = $('#yallo-chat-icon');
            this.$closeIcon = $('#yallo-close-icon');
        },
        
        /**
         * Bind event listeners
         */
        bindEvents: function() {
            const self = this;
            
            // Toggle chat window
            this.$toggle.on('click', function() {
                self.toggleChat();
            });
            
            // Close button in header
            $('#yallo-chat-close').on('click', function() {
                self.closeChat();
            });
            
            // Form submission
            this.$form.on('submit', function(e) {
                e.preventDefault();
                self.handleUserInput();
            });
            
            // Scroll trigger for auto-open
            if (yalloChatbot.autoOpen) {
                $(window).on('scroll', function() {
                    self.handleScrollTrigger();
                });
            }
        },
        
        /**
         * Check if should auto-open on page load
         */
        checkAutoOpen: function() {
            // Don't auto-open immediately, wait for scroll
        },
        
        /**
         * Handle scroll trigger for auto-open
         */
        handleScrollTrigger: function() {
            if (this.hasAutoOpened || this.isOpen) return;
            
            const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            
            if (scrollPercent >= yalloChatbot.scrollTrigger) {
                this.hasAutoOpened = true;
                this.openChat();
            }
        },
        
        /**
         * Toggle chat window
         */
        toggleChat: function() {
            if (this.isOpen) {
                this.closeChat();
            } else {
                this.openChat();
            }
        },
        
        /**
         * Open chat window
         */
        openChat: function() {
            this.isOpen = true;
            this.$window.fadeIn(300);
            this.$chatIcon.hide();
            this.$closeIcon.show();
            
            // Start chat if no messages
            if (this.messages.length === 0) {
                this.startChat();
            }
        },
        
        /**
         * Close chat window
         */
        closeChat: function() {
            this.isOpen = false;
            this.$window.addClass('closing');
            
            setTimeout(() => {
                this.$window.hide().removeClass('closing');
                this.$chatIcon.show();
                this.$closeIcon.hide();
            }, 200);
        },
        
        /**
         * Start new chat
         */
        startChat: function() {
            this.isChatFinished = false;
            this.isConsultationActive = false;
            this.isInputDisabled = false;
            this.consultationStep = 0;
            this.consultationData = {
                initial_intent: null,
                lead_type: null
            };
            this.messages = [];
            this.$messagesContainer.empty();
            this.askQuestionById(0);
        },
        
        /**
         * Handle user text input
         */
        handleUserInput: function() {
            const userAnswer = this.$input.val().trim();
            
            if (!userAnswer || this.isChatFinished || this.isInputDisabled) {
                return;
            }
            
            // Add user message
            this.addMessage(userAnswer, 'user');
            const userInput = this.$input.val();
            this.$input.val('');
            
            // Show typing indicator
            this.showTypingIndicator();
            
            // Process after delay
            setTimeout(() => {
                this.hideTypingIndicator();
                
                if (this.isConsultationActive) {
                    this.handleConsultationInput(userInput);
                } else {
                    this.processUserMessage(userInput);
                }
            }, 700);
        },
        
        /**
         * Process user message to find matching question
         */
        processUserMessage: function(message) {
            const lowerMessage = message.toLowerCase();
            let foundQuestion = null;
            
            // Find matching question by keywords
            for (let i = 0; i < this.questions.length; i++) {
                const question = this.questions[i];
                for (let j = 0; j < question.keywords.length; j++) {
                    if (lowerMessage.includes(question.keywords[j].toLowerCase())) {
                        foundQuestion = question;
                        break;
                    }
                }
                if (foundQuestion) break;
            }
            
            if (foundQuestion) {
                this.askQuestionById(foundQuestion.id);
            } else {
                // Start consultation flow
                this.isConsultationActive = true;
                this.consultationStep = 0;
                this.isInputDisabled = false;
                
                const firstQuestion = this.consultationQuestions[0].text;
                const fallbackText = "I may not have a direct answer for that here, but I can connect you with the right YALLO expert.\n\n" + firstQuestion;
                
                this.addMessage(fallbackText, 'bot');
            }
        },
        
        /**
         * Ask question by ID
         */
        askQuestionById: function(id) {
            const question = this.questions.find(q => q.id === id);
            
            if (question) {
                this.isInputDisabled = !!(question.options && question.options.length > 0);
                this.updateInputState();
                this.addMessage(question.answer, 'bot', question.options || []);
            }
        },
        
        /**
         * Handle option button click
         */
        handleOptionClick: function(option) {
            // Add user message
            this.addMessage(option.text, 'user');
            
            // Store intent and lead type
            if (option.intent) {
                this.consultationData.initial_intent = option.intent;
            }
            if (option.leadType) {
                this.consultationData.lead_type = option.leadType;
            }
            
            // Show typing indicator
            this.showTypingIndicator();
            
            setTimeout(() => {
                this.hideTypingIndicator();
                
                if (option.nextId === 300) {
                    this.startConsultation();
                } else {
                    this.askQuestionById(option.nextId);
                }
            }, 700);
        },
        
        /**
         * Start consultation flow
         */
        startConsultation: function() {
            this.isConsultationActive = true;
            this.isInputDisabled = false;
            this.consultationStep = 0;
            this.updateInputState();
            this.askConsultationQuestion();
        },
        
        /**
         * Ask consultation question
         */
        askConsultationQuestion: function() {
            const current = this.consultationQuestions[this.consultationStep];
            
            if (!current) return;
            
            let questionText = current.text;
            
            // Replace placeholders
            for (const key in this.consultationData) {
                questionText = questionText.replace(`{${key}}`, this.consultationData[key] || '');
            }
            
            this.isInputDisabled = false;
            this.updateInputState();
            this.addMessage(questionText, 'bot');
        },
        
        /**
         * Handle consultation input
         */
        handleConsultationInput: function(userAnswer) {
            const currentStepConfig = this.consultationQuestions[this.consultationStep];
            
            if (!currentStepConfig) return;
            
            this.consultationData[currentStepConfig.key] = userAnswer;
            this.consultationStep++;
            
            if (this.consultationStep < this.consultationQuestions.length) {
                this.askConsultationQuestion();
            } else {
                this.finalizeConsultation();
            }
        },
        
        /**
         * Finalize consultation and submit lead
         */
        finalizeConsultation: function() {
            this.isChatFinished = true;
            this.isInputDisabled = true;
            this.updateInputState();
            
            const userName = this.consultationData.name || 'there';
            const userEmail = this.consultationData.email || 'your email';
            
            const finalMessage = `Perfect, ${userName}. Thank you 🙌\n\n` +
                `One of our **account managers or architecture leads** will review this and contact you at **${userEmail}** within **24 hours** with:\n` +
                `- the right expert to speak with\n` +
                `- recommended next step (call / workshop / talent shortlist)\n\n` +
                `You can close this chat now – we'll take it from here.`;
            
            this.addMessage(finalMessage, 'bot');
            this.submitLead();
        },
        
        /**
         * Submit lead via AJAX
         */
        submitLead: function() {
            const self = this;
            
            const leadData = {
                action: 'yallo_submit_lead',
                nonce: yalloChatbot.nonce,
                name: this.consultationData.name || '',
                email: this.consultationData.email || '',
                company: this.consultationData.company || '',
                location: this.consultationData.location || '',
                industry: this.consultationData.industry || '',
                platforms: this.consultationData.platforms || '',
                capabilities: this.consultationData.capabilities || '',
                service_type: this.consultationData.service_type || '',
                pain: this.consultationData.pain || '',
                initial_intent: this.consultationData.initial_intent || '',
                lead_type: this.consultationData.lead_type || '',
                page_url: window.location.href
            };
            
            $.ajax({
                url: yalloChatbot.ajaxUrl,
                type: 'POST',
                data: leadData,
                success: function(response) {
                    console.log('Lead submitted successfully', response);
                },
                error: function(xhr, status, error) {
                    console.error('Error submitting lead:', error);
                }
            });
        },
        
        /**
         * Add message to chat
         */
        addMessage: function(text, sender, options) {
            options = options || [];
            
            const messageId = Date.now() + Math.random();
            this.messages.push({ id: messageId, text, sender, options });
            
            // Create message element
            const $message = $('<div>').addClass('yallo-message').addClass(sender);
            const $bubble = $('<div>').addClass('yallo-message-bubble').text(text);
            
            // Convert markdown-style bold to HTML
            const htmlText = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            $bubble.html(htmlText);
            
            $message.append($bubble);
            
            // Add options if bot message
            if (sender === 'bot' && options.length > 0) {
                const $optionsContainer = $('<div>').addClass('yallo-message-options');
                
                options.forEach(option => {
                    const $btn = $('<button>')
                        .addClass('yallo-option-btn')
                        .text(option.text)
                        .on('click', () => {
                            this.handleOptionClick(option);
                        });
                    
                    $optionsContainer.append($btn);
                });
                
                $message.append($optionsContainer);
            }
            
            this.$messagesContainer.append($message);
            this.scrollToBottom();
        },
        
        /**
         * Show typing indicator
         */
        showTypingIndicator: function() {
            this.isBotTyping = true;
            
            const $typing = $('<div>').addClass('yallo-message bot').attr('id', 'yallo-typing');
            const $indicator = $('<div>').addClass('yallo-typing-indicator');
            
            for (let i = 0; i < 3; i++) {
                $indicator.append($('<span>').addClass('yallo-typing-dot'));
            }
            
            $typing.append($indicator);
            this.$messagesContainer.append($typing);
            this.scrollToBottom();
        },
        
        /**
         * Hide typing indicator
         */
        hideTypingIndicator: function() {
            this.isBotTyping = false;
            $('#yallo-typing').remove();
        },
        
        /**
         * Update input state
         */
        updateInputState: function() {
            if (this.isChatFinished) {
                this.$input.prop('disabled', true).attr('placeholder', 'Chat has ended. Thank you!');
                this.$sendBtn.prop('disabled', true);
            } else if (this.isInputDisabled) {
                this.$input.prop('disabled', true).attr('placeholder', 'Please use the options above...');
                this.$sendBtn.prop('disabled', true);
            } else if (this.isConsultationActive) {
                this.$input.prop('disabled', false).attr('placeholder', 'Please type your answer...');
                this.$sendBtn.prop('disabled', false);
            } else {
                this.$input.prop('disabled', false).attr('placeholder', 'Type your message or use the options...');
                this.$sendBtn.prop('disabled', false);
            }
        },
        
        /**
         * Scroll to bottom of messages
         */
        scrollToBottom: function() {
            setTimeout(() => {
                this.$messagesContainer.scrollTop(this.$messagesContainer[0].scrollHeight);
            }, 100);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        YALLO_CHATBOT.init();
    });
    
})(jQuery);
