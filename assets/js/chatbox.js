// Floating Chatbox for HomeLink
class ChatBox {
    constructor() {
        this.isOpen = false;
        this.messages = [];
        this.init();
    }

    init() {
        this.createChatBox();
        this.attachEventListeners();
        this.loadWelcomeMessage();
    }

    createChatBox() {
        const chatHTML = `
            <!-- Chat Toggle Button -->
            <div id="chatToggle" class="chat-toggle">
                <i class="fas fa-comments"></i>
                <span class="chat-badge" id="chatBadge">1</span>
            </div>

            <!-- Chat Window -->
            <div id="chatWindow" class="chat-window">
                <div class="chat-header">
                    <div class="chat-header-info">
                        <i class="fas fa-headset"></i>
                        <div>
                            <h4>HomeLink Support</h4>
                            <span class="chat-status">
                                <span class="status-dot"></span>
                                Online
                            </span>
                        </div>
                    </div>
                    <button id="chatClose" class="chat-close-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <!-- Messages will be inserted here -->
                </div>

                <div class="chat-input-container">
                    <input 
                        type="text" 
                        id="chatInput" 
                        class="chat-input" 
                        placeholder="Type your message..."
                        autocomplete="off"
                    >
                    <button id="chatSend" class="chat-send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', chatHTML);
    }

    attachEventListeners() {
        const toggle = document.getElementById('chatToggle');
        const close = document.getElementById('chatClose');
        const send = document.getElementById('chatSend');
        const input = document.getElementById('chatInput');

        toggle.addEventListener('click', () => this.toggleChat());
        close.addEventListener('click', () => this.closeChat());
        send.addEventListener('click', () => this.sendMessage());
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        const chatWindow = document.getElementById('chatWindow');
        const chatToggle = document.getElementById('chatToggle');
        const chatBadge = document.getElementById('chatBadge');

        if (this.isOpen) {
            chatWindow.classList.add('active');
            chatToggle.classList.add('active');
            chatBadge.style.display = 'none';
            document.getElementById('chatInput').focus();
        } else {
            chatWindow.classList.remove('active');
            chatToggle.classList.remove('active');
        }
    }

    closeChat() {
        this.isOpen = false;
        document.getElementById('chatWindow').classList.remove('active');
        document.getElementById('chatToggle').classList.remove('active');
    }

    loadWelcomeMessage() {
        setTimeout(() => {
            this.addMessage('bot', 'Hello! 👋 Welcome to HomeLink. How can I help you today?');
            this.showQuickReplies();
        }, 1000);
    }

    showQuickReplies() {
        const quickReplies = [
            'Browse Properties',
            'How to Register',
            'Contact Support',
            'Pricing Info'
        ];

        const messagesContainer = document.getElementById('chatMessages');
        const quickRepliesHTML = `
            <div class="quick-replies">
                ${quickReplies.map(reply => 
                    `<button class="quick-reply-btn" onclick="chatBox.handleQuickReply('${reply}')">${reply}</button>`
                ).join('')}
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', quickRepliesHTML);
        this.scrollToBottom();
    }

    handleQuickReply(reply) {
        // Remove quick replies
        const quickRepliesEl = document.querySelector('.quick-replies');
        if (quickRepliesEl) quickRepliesEl.remove();

        // Add user message
        this.addMessage('user', reply);

        // Bot response based on quick reply
        setTimeout(() => {
            let response = '';
            switch(reply) {
                case 'Browse Properties':
                    response = 'You can browse our properties by clicking on "Browse Properties" in the menu, or <a href="properties.php">click here</a> to view them now!';
                    break;
                case 'How to Register':
                    response = 'To register, click on the "Register" button in the top menu. You can sign up as a Buyer, Seller, or Agent. <a href="register.php">Register now</a>';
                    break;
                case 'Contact Support':
                    response = 'You can reach us at:<br>📧 Email: support@homelink.zm<br>📞 Phone: +260 XXX XXX XXX<br>Or visit our <a href="contact.php">contact page</a>';
                    break;
                case 'Pricing Info':
                    response = 'Our platform is free for buyers! Sellers and agents can list properties with competitive commission rates. Contact us for detailed pricing.';
                    break;
                default:
                    response = 'How else can I help you?';
            }
            this.addMessage('bot', response);
        }, 500);
    }

    sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();

        if (message === '') return;

        // Add user message
        this.addMessage('user', message);
        input.value = '';

        // Simulate bot response
        setTimeout(() => {
            this.generateBotResponse(message);
        }, 1000);
    }

    generateBotResponse(userMessage) {
        const lowerMessage = userMessage.toLowerCase();
        let response = '';

        if (lowerMessage.includes('hello') || lowerMessage.includes('hi')) {
            response = 'Hello! How can I assist you today?';
        } else if (lowerMessage.includes('property') || lowerMessage.includes('house')) {
            response = 'We have a wide range of properties available! You can <a href="properties.php">browse our listings here</a>.';
        } else if (lowerMessage.includes('register') || lowerMessage.includes('sign up')) {
            response = 'You can register by clicking the "Register" button at the top. <a href="register.php">Click here to register</a>.';
        } else if (lowerMessage.includes('contact') || lowerMessage.includes('support')) {
            response = 'You can contact us through our <a href="contact.php">contact page</a> or email us at support@homelink.zm';
        } else if (lowerMessage.includes('price') || lowerMessage.includes('cost')) {
            response = 'Our pricing varies by property and service. Would you like to know about buyer services or seller/agent fees?';
        } else if (lowerMessage.includes('thank')) {
            response = 'You\'re welcome! Is there anything else I can help you with?';
        } else if (lowerMessage.includes('bye') || lowerMessage.includes('goodbye')) {
            response = 'Goodbye! Feel free to reach out anytime. Have a great day! 👋';
        } else {
            response = 'Thank you for your message. For specific inquiries, please contact our support team at support@homelink.zm or visit our <a href="contact.php">contact page</a>.';
        }

        this.addMessage('bot', response);
    }

    addMessage(sender, text) {
        const messagesContainer = document.getElementById('chatMessages');
        const timestamp = new Date().toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });

        const messageHTML = `
            <div class="chat-message ${sender}">
                <div class="message-content">
                    ${text}
                </div>
                <div class="message-time">${timestamp}</div>
            </div>
        `;

        messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
        this.scrollToBottom();
    }

    scrollToBottom() {
        const messagesContainer = document.getElementById('chatMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

// Initialize chatbox when DOM is loaded
let chatBox;
document.addEventListener('DOMContentLoaded', () => {
    chatBox = new ChatBox();
});
