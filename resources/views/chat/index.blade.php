@extends('layouts.app')

@section('title', 'CyberEd - Chat')

@push('styles')
<style>
.message-content p {
    white-space: pre-line;
}

.message-content strong {
    color: var(--color-primary);
    font-weight: 600;
}

.error-message {
    background-color: rgba(239, 68, 68, 0.1);
    border: 1px solid var(--color-danger);
    color: var(--color-danger);
    padding: 0.75rem 1rem;
    border-radius: var(--border-radius);
    margin: 1rem;
    text-align: center;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(15, 23, 42, 0.7);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.loading-overlay p {
    margin-top: 1rem;
    color: var(--color-text-primary);
}

.chat-messages {
    height: calc(100vh - 140px);
    overflow-y: auto;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: var(--color-text-secondary);
    text-align: center;
    padding: 2rem;
}

.empty-state svg {
    width: 64px;
    height: 64px;
    margin-bottom: 1rem;
    color: var(--color-text-muted);
}

.empty-state h3 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}

.empty-state p {
    max-width: 400px;
}
</style>
@endpush

@section('content')
<div class="chat-container">

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('placeholder.svg?height=40&width=40') }}" alt="CyberEd Logo" class="sidebar-logo">
            <h2>CyberEd</h2>
        </div>
        <button id="new-chat-btn" class="btn btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14M5 12h14"></path>
            </svg>
            New Session
        </button>
        <div class="sidebar-content">
            <h3>History</h3>
            <ul class="chat-history" id="chat-history">
                @foreach($sessions as $session)
                <li class="history-item" data-session-id="{{ $session->id }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span>{{ $session->title }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">{{ substr(auth()->user()->name, 0, 2) }}</div>
                <span class="user-name">{{ auth()->user()->name }}</span>
            </div>
            <div class="user-actions">
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-icon" title="Admin Dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                        <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                        <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                        <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                    </svg>
                </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-icon" title="Logout">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>


    <main class="chat-main">
        <header class="chat-header">
            <h1>Cyber Threat Education Bot</h1>
            <div class="header-actions">
                <button id="toggle-sidebar-btn" class="btn btn-icon" title="Toggle Sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                        <line x1="9" x2="9" y1="3" y2="21"></line>
                    </svg>
                </button>
            </div>
        </header>
        <div class="chat-messages" id="chat-messages">

            <div class="empty-state" id="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <h3>Welcome to CyberEd</h3>
                <p>Start a new conversation or select an existing session from the sidebar to learn about cybersecurity
                    threats.</p>
            </div>
        </div>
        <div class="chat-input">
            <form id="chat-form">
                <div class="input-container">
                    <input type="text" id="user-input" placeholder="Type your message..." autocomplete="off">
                    <button type="submit" class="send-btn" title="Send">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatInterface = {
        currentSessionId: null,
        isLoading: false,

        init: function() {
            this.bindEvents();


            const historyItems = document.querySelectorAll('.history-item');
            if (historyItems.length > 0) {

                this.loadSession(historyItems[0].dataset.sessionId);
            } else {

                document.getElementById('empty-state').style.display = 'flex';
                document.getElementById('user-input').disabled = true;
            }
        },

        bindEvents: function() {

            document.getElementById('new-chat-btn').addEventListener('click', () => {
                this.createNewSession();
            });


            document.getElementById('chat-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.sendMessage();
            });

            document.getElementById('toggle-sidebar-btn').addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('collapsed');
            });

            document.addEventListener('click', (e) => {
                const historyItem = e.target.closest('.history-item');
                if (historyItem) {
                    const sessionId = historyItem.dataset.sessionId;
                    this.loadSession(sessionId);
                }
            });

            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('option-btn')) {
                    const option = e.target.dataset.topic || e.target.textContent.toLowerCase();
                    this.sendOptionMessage(option);
                }
                if (e.target.classList.contains('severity-btn')) {
                    const severity = e.target.classList.contains('low') ? 'low' :
                        e.target.classList.contains('medium') ? 'medium' : 'high';
                    this.sendOptionMessage(severity);
                }
            });
        },

        createNewSession: async function() {
            try {
                this.showLoading();

                const response = await fetch('/chat/session', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.currentSessionId = data.session_id;


                    await this.updateSidebar();


                    await this.loadSession(data.session_id);

                    document.getElementById('user-input').disabled = false;


                    document.getElementById('empty-state').style.display = 'none';
                } else {
                    throw new Error(data.error || 'Failed to create session');
                }
            } catch (error) {
                console.error('Error creating session:', error);
                this.showError('Failed to create a new session. Please try again.');
            } finally {
                this.hideLoading();
            }
        },

        updateSidebar: async function() {
            try {
                const response = await fetch('/chat/sessions');
                const data = await response.json();

                if (response.ok && data.success) {
                    const historyList = document.getElementById('chat-history');
                    historyList.innerHTML = '';

                    data.sessions.forEach(session => {
                        const li = document.createElement('li');
                        li.className = 'history-item';
                        li.dataset.sessionId = session.id;
                        if (session.id === this.currentSessionId) {
                            li.classList.add('active');
                        }

                        li.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <span>${session.title}</span>
                        `;

                        historyList.appendChild(li);
                    });
                } else {
                    throw new Error(data.error || 'Failed to update sidebar');
                }
            } catch (error) {
                console.error('Error updating sidebar:', error);
                this.showError('Failed to update chat history. Please refresh the page.');
            }
        },

        loadSession: async function(sessionId) {
            if (!sessionId) return;

            try {
                this.showLoading();

                const response = await fetch(`/chat/session/${sessionId}`);
                const data = await response.json();

                if (response.ok && data.success) {
                    this.currentSessionId = sessionId;
                    this.displayMessages(data.messages);


                    document.querySelectorAll('.history-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    document.querySelector(`[data-session-id="${sessionId}"]`)?.classList.add('active');


                    document.getElementById('user-input').disabled = false;


                    document.getElementById('empty-state').style.display = 'none';
                } else {
                    throw new Error(data.error || 'Failed to load session');
                }
            } catch (error) {
                console.error('Error loading session:', error);
                this.showError('Failed to load chat session. Please try again.');
            } finally {
                this.hideLoading();
            }
        },

        sendMessage: async function() {
            const input = document.getElementById('user-input');
            const message = input.value.trim();

            if (!message || this.isLoading || !this.currentSessionId) return;

            this.isLoading = true;
            input.value = '';


            this.addMessage(message, false);
            this.showTypingIndicator();

            try {
                const response = await fetch('/chat/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({
                        session_id: this.currentSessionId,
                        message: message
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.hideTypingIndicator();
                    this.addMessage(data.message, true, data.type, data.metadata);


                    this.updateSidebar();
                } else {
                    throw new Error(data.error || 'Failed to send message');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                this.hideTypingIndicator();
                this.addMessage('Sorry, there was an error processing your message. Please try again.',
                    true);
            } finally {
                this.isLoading = false;
            }
        },

        sendOptionMessage: function(option) {
            const input = document.getElementById('user-input');
            input.value = option;
            this.sendMessage();
        },

        displayMessages: function(messages) {
            const container = document.getElementById('chat-messages');
            container.innerHTML = '';

            if (messages && messages.length > 0) {
                messages.forEach(msg => {
                    this.addMessage(msg.message, msg.is_bot, msg.type, msg.metadata);
                });


                setTimeout(() => {
                    container.scrollTop = container.scrollHeight;
                }, 100);
            } else {

                this.addMessage(
                    'Welcome to CyberEd! Start a conversation to learn about cybersecurity threats.',
                    true);
            }
        },

        addMessage: function(message, isBot, type = 'text', metadata = {}) {
            const container = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isBot ? 'bot-message' : 'user-message'}`;

            if (isBot) {
                messageDiv.innerHTML = `
                    <div class="message-avatar">
                        <img src="/placeholder.svg?height=40&width=40" alt="Bot Avatar">
                    </div>
                    <div class="message-content">
                        <p>${this.formatMessage(message)}</p>
                        ${this.renderMessageOptions(type, metadata)}
                    </div>
                `;
            } else {
                const userInitials = document.querySelector('.user-avatar').textContent || 'U';
                messageDiv.innerHTML = `
                    <div class="message-avatar">
                        <div class="user-avatar">${userInitials}</div>
                    </div>
                    <div class="message-content">
                        <p>${message}</p>
                    </div>
                `;
            }

            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
        },

        formatMessage: function(message) {
            if (!message) return '';


            return message
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>');
        },

        renderMessageOptions: function(type, metadata) {
            if (!metadata) return '';

            if (type === 'greeting' && metadata.options) {
                return `
                    <div class="message-options">
                        ${metadata.options.map(option => 
                            `<button class="option-btn" data-topic="${option}">${option.charAt(0).toUpperCase() + option.slice(1)}</button>`
                        ).join('')}
                    </div>
                `;
            }

            if (type === 'severity_request' && metadata.severity_options) {
                return `
                    <div class="severity-options">
                        ${metadata.severity_options.map(severity => 
                            `<button class="severity-btn ${severity}">${severity.charAt(0).toUpperCase() + severity.slice(1)}</button>`
                        ).join('')}
                    </div>
                `;
            }

            return '';
        },

        showTypingIndicator: function() {
            const container = document.getElementById('chat-messages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot-message typing-indicator';
            typingDiv.id = 'typing-indicator';
            typingDiv.innerHTML = `
                <div class="message-avatar">
                    <img src="/placeholder.svg?height=40&width=40" alt="Bot Avatar">
                </div>
                <div class="message-content">
                    <div class="typing-indicator">
                        <span>Bot is typing</span>
                        <div class="typing-dots">
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(typingDiv);
            container.scrollTop = container.scrollHeight;
        },

        hideTypingIndicator: function() {
            const indicator = document.getElementById('typing-indicator');
            if (indicator) {
                indicator.remove();
            }
        },

        showLoading: function() {
            const container = document.querySelector('.chat-main');
            if (!document.getElementById('loading-overlay')) {
                const loadingDiv = document.createElement('div');
                loadingDiv.id = 'loading-overlay';
                loadingDiv.className = 'loading-overlay';
                loadingDiv.innerHTML = `
                    <div class="spinner"></div>
                    <p>Loading...</p>
                `;
                container.appendChild(loadingDiv);
            }
        },

        hideLoading: function() {
            const loadingOverlay = document.getElementById('loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.remove();
            }
        },

        showError: function(message) {
            const container = document.getElementById('chat-messages');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            container.appendChild(errorDiv);
            container.scrollTop = container.scrollHeight;

            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }
    };

    chatInterface.init();
});
</script>
@endpush
@endsection