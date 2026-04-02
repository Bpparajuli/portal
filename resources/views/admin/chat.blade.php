@extends('layouts.admin')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --portal-green: #10b981;
        --primary-soft: #eef2ff;
        --bg-glass: rgba(255, 255, 255, 0.95);
        --sidebar-bg: #ffffff;
        --chat-bg: #efefef;
    }

    .chat-wrapper {
        display: flex;
        height: 85vh;
        background: var(--bg-glass);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    /* Sidebar */
    .chat-sidebar {
        width: 380px;
        border-right: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        background: var(--sidebar-bg);
    }

    .sidebar-header {
        padding: 24px;
        border-bottom: 1px solid #f1f5f9;
    }

    .search-box {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 10px 15px;
        display: flex;
        align-items: center;
        margin-top: 15px;
    }

    .search-box input {
        border: none;
        background: transparent;
        width: 100%;
        outline: none;
        margin-left: 10px;
        font-size: 14px;
    }

    /* User List */
    .user-list {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
    }

    .user-card {
        display: flex;
        padding: 12px 15px;
        margin-bottom: 5px;
        cursor: pointer;
        transition: 0.2s;
        border-radius: 12px;
        align-items: center;
    }

    .user-card:hover {
        background: #f8fafc;
    }

    .user-card.active {
        background: var(--primary-soft);
        border-left: 4px solid var(--primary);
    }

    .avatar-container {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        flex-shrink: 0;
    }

    .avatar-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .status-dot {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
    }

    .bg-online {
        background: var(--portal-green);
    }

    .bg-offline {
        background: #94a3b8;
    }

    /* Chat Main */
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--chat-bg);
        position: relative;
    }

    .chat-header {
        padding: 15px 25px;
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    #messageBox {
        flex: 1;
        overflow-y: auto;
        padding: 25px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    /* Bubbles */
    .bubble {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 18px;
        font-size: 14px;
        position: relative;
        line-height: 1.5;
    }

    .sent {
        align-self: flex-end;
        background: var(--primary);
        color: white;
        border-bottom-right-radius: 2px;
    }

    .received {
        align-self: flex-start;
        background: white;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 2px;
    }

    /* File styling inside bubbles */
    .msg-file-preview {
        max-width: 200px;
        border-radius: 8px;
        display: block;
        margin-bottom: 8px;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .doc-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        text-decoration: none;
        color: inherit;
        font-weight: 500;
        font-size: 12px;
    }

    /* Delete Options */
    .bubble .msg-actions {
        position: absolute;
        top: 0px;
        right: -10px;
        background: #fff;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        z-index: 10;
    }

    .bubble:hover .msg-actions {
        display: flex;
    }

    .msg-actions i {
        cursor: pointer;
        color: #94a3b8;
        transition: 0.2s;
        font-size: 14px;
    }

    .msg-actions i:hover {
        color: #ef4444;
    }

    /* Meta (Time + Ticks) */
    .msg-meta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
        margin-top: 4px;
    }

    .time {
        font-size: 10px;
        opacity: 0.8;
    }

    .tick-icon {
        font-size: 11px;
        margin-left: -3px;
    }

    .status-sent {
        color: rgba(255, 255, 255, 0.5);
    }

    .status-delivered {
        color: rgba(255, 255, 255, 0.8);
    }

    .status-read {
        color: #38bdf8;
    }

    .chat-footer {
        padding: 20px;
        background: #fff;
        border-top: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
    }

    .input-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
    }

    .input-container {
        flex: 1;
        background: #f1f5f9;
        border-radius: 15px;
        padding: 10px 20px;
    }

    .input-container input {
        border: none;
        background: transparent;
        width: 100%;
        outline: none;
    }

    .unread-count {
        background: var(--primary);
        color: white;
        font-size: 10px;
        padding: 2px 7px;
        border-radius: 10px;
    }

</style>

<div class="chat-wrapper" id="chatWrapper" data-auth-id="{{ Auth::id() }}">
    <div class="chat-sidebar">
        <div class="sidebar-header">
            <h4 class="fw-bold m-0 text-dark">
                Business Chats <span id="totalUnreadBadge" class="badge bg-danger ms-2 d-none">0</span>
            </h4>
            <div class="search-box">
                <i class="fas fa-search text-muted"></i>
                <input type="text" id="userSearch" placeholder="Search businesses..." onkeyup="filterUsers()">
            </div>
        </div>
        <div class="user-list" id="userList">
            <div class="text-center mt-5 text-muted small">Loading...</div>
        </div>
    </div>

    <div class="chat-main">
        <div id="noChatSelected" class="m-auto text-center">
            <i class="fas fa-paper-plane fa-3x text-light mb-3"></i>
            <h5 class="fw-bold">Select a Business</h5>
            <p class="text-muted small">Choose a contact from the left to view messages.</p>
        </div>

        <div id="chatActive" class="d-none h-100 flex-column d-flex">
            <div class="chat-header">
                <div class="d-flex align-items-center">
                    <div class="avatar-container me-3" id="headerAvatar"></div>
                    <div>
                        <h6 class="m-0 fw-bold" id="headerName">---</h6>
                        <small id="headerStatus" class="text-muted" style="font-size: 11px;"></small>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="clearChat()">
                                <i class="fas fa-broom me-2"></i>Clear Conversation</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div id="messageBox"></div>

            <div class="chat-footer">
                <div id="attachmentPreview" class="d-none w-100 p-2 border-bottom mb-2 bg-light rounded d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div id="previewIcon" class="me-2"></div>
                        <span id="fileNameDisplay" class="small fw-bold text-truncate" style="max-width: 200px;"></span>
                    </div>
                    <button type="button" class="btn-close small" onclick="cancelAttachment()"></button>
                </div>

                <div class="d-flex align-items-center w-100 gap-2">
                    <label for="fileInput" class="mb-0 cursor-pointer">
                        <i class="fas fa-paperclip text-muted fa-lg"></i>
                    </label>

                    <input type="file" id="fileInput" class="d-none" onchange="handleFileSelect(this)">
                    <div class="input-wrapper flex-grow-1">
                        <input type="text" id="msgInput" placeholder="Write message..." onkeypress="if(event.key==='Enter') send()">
                    </div>
                    <button class="btn btn-primary rounded-circle" id="sendBtn" onclick="send()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let activeUser = null;
    let messageCount = 0;
    const authId = parseInt(document.getElementById('chatWrapper').dataset.authId);

    function formatLocalTime(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr.endsWith('Z') ? dateStr : dateStr + 'Z');
        return date.toLocaleTimeString([], {
            hour: '2-digit'
            , minute: '2-digit'
        });
    }

    async function loadUsers() {
        try {
            const res = await fetch("{{ route('admin.chat.users') }}");
            const data = await res.json();

            // Total unread badge
            const totalBadge = document.getElementById('totalUnreadBadge');
            if (totalBadge) {
                totalBadge.innerText = data.total_unread;
                data.total_unread > 0 ?
                    totalBadge.classList.remove('d-none') :
                    totalBadge.classList.add('d-none');
            }

            const users = data.users;
            const list = document.getElementById('userList');

            list.innerHTML = users.map(u => {

                const activeClass = (activeUser && activeUser.id === u.id) ? 'active' : '';

                // ✅ --- FIX APPLIED HERE ---
                let displayMsg = u.last_message;

                if (!displayMsg || displayMsg.trim() === "") {
                    if (u.last_message_file || u.file || u.has_attachment) {
                        displayMsg = '<i class="fas fa-paperclip"></i> Attachment';
                    } else {
                        displayMsg = "No messages yet";
                    }
                }

                return `
            <div class="user-card ${activeClass}" onclick='selectUser(${JSON.stringify(u)})'>
                <div class="avatar-container">
                    ${u.business_logo 
                        ? `<img src="/storage/${u.business_logo}">` 
                        : `<i class="fas fa-building text-muted"></i>`}
                    <span class="status-dot ${u.is_online ? 'bg-online' : 'bg-offline'}"></span>
                </div>

                <div class="ms-3 flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-truncate" style="font-size: 14px;">
                            ${u.business_name}
                        </span>
                        <small style="font-size: 10px; color: #94a3b8;">
                            ${formatLocalTime(u.last_message_time)}
                        </small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted text-truncate w-75" style="font-size: 12px;">
                            ${displayMsg}
                        </small>

                        ${u.unread_count > 0 
                            ? `<span class="unread-count">${u.unread_count}</span>` 
                            : ''}
                    </div>
                </div>
            </div>`;
            }).join('');

        } catch (e) {
            console.error("Error loading users:", e);
        }
    }

    function selectUser(user) {
        activeUser = user;
        document.getElementById('noChatSelected').classList.add('d-none');
        document.getElementById('chatActive').classList.remove('d-none');
        document.getElementById('headerName').innerText = user.business_name;
        document.getElementById('headerAvatar').innerHTML = user.business_logo ? `<img src="/storage/${user.business_logo}">` : `<i class="fas fa-building text-muted"></i>`;
        document.getElementById('headerStatus').innerText = user.is_online ? 'Active Now' : 'Offline';
        loadMessages(true);
        loadUsers();
    }

    async function loadMessages(forceScroll = false) {
        if (!activeUser) return;
        try {
            const res = await fetch(`/admin/chat/messages/${activeUser.id}`);
            const msgs = await res.json();
            const box = document.getElementById('messageBox');

            if (msgs.length !== messageCount || forceScroll) {
                messageCount = msgs.length;
                box.innerHTML = '';
                msgs.forEach(m => {
                    const isSent = m.sender_id == authId;
                    const bubble = document.createElement('div');
                    bubble.className = `bubble ${isSent ? 'sent' : 'received'}`;

                    // Visual Attachment Preview
                    if (m.file) {
                        const fileUrl = `/storage/${m.file}`;
                        const isImg = m.file.match(/\.(jpg|jpeg|png|gif)$/i);
                        if (isImg) {
                            bubble.innerHTML += `<a href="${fileUrl}" target="_blank"><img src="${fileUrl}" class="msg-file-preview"></a>`;
                        } else {
                            bubble.innerHTML += `<a href="${fileUrl}" target="_blank" class="doc-link">
                                <i class="fas fa-file-pdf fa-lg text-danger"></i>
                                <span class="text-truncate">View Attachment</span>
                            </a>`;
                        }
                    }

                    const txt = document.createElement('div');
                    txt.textContent = m.message || '';
                    bubble.appendChild(txt);

                    const meta = document.createElement('div');
                    meta.className = 'msg-meta';
                    const time = document.createElement('span');
                    time.className = 'time';
                    time.textContent = formatLocalTime(m.created_at);
                    meta.appendChild(time);

                    if (isSent) {
                        const ticks = document.createElement('span');
                        if (m.status === 'read') {
                            ticks.innerHTML = `<i class="fas fa-check tick-icon status-read"></i><i class="fas fa-check tick-icon status-read"></i>`;
                        } else if (m.status === 'delivered') {
                            ticks.innerHTML = `<i class="fas fa-check tick-icon status-delivered"></i><i class="fas fa-check tick-icon status-delivered"></i>`;
                        } else {
                            ticks.innerHTML = `<i class="fas fa-check tick-icon status-sent"></i>`;
                        }
                        meta.appendChild(ticks);
                    }
                    bubble.appendChild(meta);

                    const actions = document.createElement('div');
                    actions.className = 'msg-actions';
                    actions.innerHTML = `<i class="fas fa-trash-alt" onclick="deleteMessage(${m.id})"></i>`;
                    bubble.appendChild(actions);

                    box.appendChild(bubble);
                });
                box.scrollTop = box.scrollHeight;
            }
        } catch (e) {
            console.error(e);
        }
    }

    // Attachment Preview Logic
    function handleFileSelect(input) {
        const file = input.files[0];
        if (!file) return;

        const previewContainer = document.getElementById('attachmentPreview');
        const nameDisplay = document.getElementById('fileNameDisplay');
        const iconDisplay = document.getElementById('previewIcon');

        nameDisplay.innerText = file.name;
        previewContainer.classList.remove('d-none');

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                iconDisplay.innerHTML = `<img src="${e.target.result}" style="width:100px; height:100px; object-fit:cover; border-radius:4px;">`;
            };
            reader.readAsDataURL(file);
        } else {
            iconDisplay.innerHTML = `<i class="fas fa-file-alt fa-lg text-primary"></i>`;
        }
    }

    function cancelAttachment() {
        document.getElementById('fileInput').value = "";
        document.getElementById('attachmentPreview').classList.add('d-none');
    }

    async function send() {
        if (!activeUser) return;
        const input = document.getElementById('msgInput');
        const fileIn = document.getElementById('fileInput');
        const sendBtn = document.getElementById('sendBtn');

        if (!input.value.trim() && !fileIn.files[0]) return;

        const formData = new FormData();
        formData.append('receiver_id', activeUser.id);
        formData.append('message', input.value);
        if (fileIn.files[0]) formData.append('file', fileIn.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        sendBtn.disabled = true;
        input.placeholder = "Sending...";

        try {
            const res = await fetch("{{ route('admin.chat.send') }}", {
                method: 'POST'
                , body: formData
                , headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (res.ok) {
                input.value = '';
                cancelAttachment();
                loadMessages(true);
                loadUsers();
            }
        } catch (e) {
            console.error(e);
        } finally {
            sendBtn.disabled = false;
            input.placeholder = "Write message...";
        }
    }

    async function deleteMessage(msgId) {
        if (!confirm('Delete this message?')) return;
        await fetch(`/admin/chat/delete/${msgId}`, {
            method: 'DELETE'
            , headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        loadMessages(true);
    }

    async function clearChat() {
        if (!activeUser || !confirm('Clear all messages?')) return;
        await fetch(`/admin/chat/clear/${activeUser.id}`, {
            method: 'DELETE'
            , headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        loadMessages(true);
        loadUsers();
    }

    function filterUsers() {
        const q = document.getElementById('userSearch').value.toLowerCase();
        document.querySelectorAll('.user-card').forEach(c => {
            const name = c.querySelector('.fw-bold').innerText.toLowerCase();
            c.style.display = name.includes(q) ? 'flex' : 'none';
        });
    }

    loadUsers();
    setInterval(loadUsers, 5000);
    setInterval(loadMessages, 3000);

</script>
@endsection
