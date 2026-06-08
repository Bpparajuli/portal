@extends('layouts.app')
@section('title', 'AI Assistant')
@section('page-title', 'AI Assistant')

@section('content')
<style>
    .ai-page {
        max-width: 900px;
        margin: 0 auto;
    }

    .ai-header {
        text-align: center;
        padding: 2rem 1rem;
        background: var(--gradient-premium);
        border-radius: var(--radius-xl);
        color: #fff;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .ai-header::before {
        content: '';
        position: absolute;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
        top: -50px;
        right: -50px;
    }

    .ai-header::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
        bottom: -30px;
        left: 20%;
    }

    .ai-header i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.9;
    }

    .ai-header h3 {
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .ai-header p {
        opacity: 0.8;
        font-size: 0.9rem;
        max-width: 500px;
        margin: 0 auto;
    }

    .chat-container {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }

    .chat-messages {
        height: 400px;
        overflow-y: auto;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .msg-row {
        display: flex;
        gap: 0.75rem;
        max-width: 85%;
    }

    .msg-row.user {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .msg-bubble {
        padding: 0.875rem 1.125rem;
        border-radius: var(--radius-lg);
        font-size: 0.9rem;
        line-height: 1.6;
    }

    .msg-row.user .msg-bubble {
        background: var(--gradient-primary);
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    .msg-row.ai .msg-bubble {
        background: var(--bg-main);
        border: 1px solid var(--border);
        border-bottom-left-radius: 4px;
    }

    .msg-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.9rem;
    }

    .msg-avatar.user {
        background: var(--gradient-primary);
        color: #fff;
    }

    .msg-avatar.ai {
        background: var(--gradient-premium);
        color: #fff;
    }

    .chat-input-area {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border);
        background: var(--bg-main);
    }

    .chat-input-row {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .chat-input-row input {
        flex: 1;
        padding: 0.75rem 1.125rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        font-size: 0.9rem;
        outline: none;
        transition: border-color var(--transition-fast);
        background: var(--bg-card);
    }

    .chat-input-row input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(109,40,217,0.1);
    }

    .quick-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 0.75rem;
    }

    .quick-action-chip {
        padding: 0.35rem 0.85rem;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-full);
        font-size: 0.78rem;
        cursor: pointer;
        transition: all var(--transition-fast);
        color: var(--text-muted);
    }

    .quick-action-chip:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: var(--primary-soft);
    }

    .typing-dots {
        display: flex;
        gap: 4px;
        padding: 12px 16px;
    }

    .typing-dots span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--text-muted);
        animation: typingBounce 1.4s infinite ease-in-out both;
    }

    .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
    .typing-dots span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes typingBounce {
        0%, 80%, 100% { transform: scale(0.6); }
        40% { transform: scale(1); }
    }

    .msg-time {
        font-size: 0.65rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }

    .msg-row.user .msg-time { text-align: right; }
</style>

<div class="ai-page">
    <!-- Header -->
    <div class="ai-header">
        <i class="fas fa-robot"></i>
        <h3><i class="fas fa-magic me-2"></i>AI Assistant</h3>
        <p>Your intelligent assistant for reports, document analysis, statistics, and general queries</p>
    </div>

    <!-- Chat -->
    <div class="chat-container">
        <div class="chat-messages" id="chatMessages">
            <div class="msg-row ai">
                <div class="msg-avatar ai"><i class="fas fa-robot"></i></div>
                <div>
                    <div class="msg-bubble">
                        👋 Hello! I'm your AI assistant. Ask me anything about:
                        <br><br>
                        📊 <strong>Statistics</strong> - "How many students do we have?"<br>
                        📄 <strong>Document Analysis</strong> - "Analyze this document"<br>
                        🔍 <strong>Search</strong> - "Find applications for..."<br>
                        ❓ <strong>General</strong> - "What can you help me with?"
                    </div>
                    <div class="msg-time">Just now</div>
                </div>
            </div>
        </div>

        <div class="chat-input-area">
            <!-- Quick actions -->
            <div class="quick-actions" id="quickActions">
                <span class="quick-action-chip" onclick="quickAsk('How many students are in the system?')">
                    <i class="fas fa-users me-1"></i>Student Count
                </span>
                <span class="quick-action-chip" onclick="quickAsk('How many applications have been submitted?')">
                    <i class="fas fa-file-alt me-1"></i>Application Stats
                </span>
                <span class="quick-action-chip" onclick="quickAsk('Show me a summary of recent activity')">
                    <i class="fas fa-chart-line me-1"></i>Activity Summary
                </span>
                <span class="quick-action-chip" onclick="quickAsk('What can you help me with?')">
                    <i class="fas fa-question-circle me-1"></i>Help
                </span>
            </div>

            <!-- Input -->
            <div class="chat-input-row">
                <input type="text" id="chatInput" placeholder="Ask me anything..." autocomplete="off"
                       onkeydown="if(event.key==='Enter') sendMessage()">
                <button class="btn btn-primary" onclick="sendMessage()" id="sendBtn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');

    function addMessage(text, type) {
        const row = document.createElement('div');
        row.className = `msg-row ${type}`;
        const avatar = document.createElement('div');
        avatar.className = `msg-avatar ${type}`;
        avatar.innerHTML = type === 'user' ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';
        const content = document.createElement('div');
        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';
        bubble.innerHTML = text.replace(/\n/g, '<br>');
        const time = document.createElement('div');
        time.className = 'msg-time';
        time.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        content.appendChild(bubble);
        content.appendChild(time);
        row.appendChild(avatar);
        row.appendChild(content);
        chatMessages.appendChild(row);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function showTyping() {
        const row = document.createElement('div');
        row.className = 'msg-row ai';
        row.id = 'typingRow';
        const avatar = document.createElement('div');
        avatar.className = 'msg-avatar ai';
        avatar.innerHTML = '<i class="fas fa-robot"></i>';
        const content = document.createElement('div');
        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';
        bubble.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
        content.appendChild(bubble);
        row.appendChild(avatar);
        row.appendChild(content);
        chatMessages.appendChild(row);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function removeTyping() {
        const typing = document.getElementById('typingRow');
        if (typing) typing.remove();
    }

    async function sendMessage() {
        const msg = chatInput.value.trim();
        if (!msg) return;

        addMessage(msg, 'user');
        chatInput.value = '';
        sendBtn.disabled = true;

        showTyping();

        try {
            const res = await fetch('/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ message: msg, context: 'general' }),
            });

            const data = await res.json();
            removeTyping();

            if (data.success) {
                addMessage(data.message, 'ai');
            } else {
                addMessage('Sorry, I encountered an error. Please try again.', 'ai');
            }
        } catch (e) {
            removeTyping();
            addMessage('Sorry, I encountered a connection error. Please try again.', 'ai');
        }

        sendBtn.disabled = false;
        chatInput.focus();
    }

    function quickAsk(text) {
        chatInput.value = text;
        sendMessage();
    }

    chatInput.focus();
</script>
@endsection
