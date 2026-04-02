@extends('layouts.agent') {{-- Assuming you have an agent layout --}}

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* ... Keep all your CSS from the Admin Blade here ... */
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

    .msg-file-preview {
        max-width: 200px;
        border-radius: 8px;
        display: block;
        margin-bottom: 8px;
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
        font-size: 12px;
    }

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
    }

    .bubble:hover .msg-actions {
        display: flex;
    }

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

    .unread-count {
        background: red;
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
                Admin Support <span id="totalUnreadBadge" class="badge bg-danger ms-2 d-none">0</span>
            </h4>
            <div class="search-box">
                <i class="fas fa-search text-muted"></i>
                <input type="text" id="userSearch" placeholder="Search Admins..." onkeyup="filterUsers()">
            </div>
        </div>
        <div class="user-list" id="userList">
            <div class="text-center mt-5 text-muted small">Loading...</div>
        </div>
    </div>

    <div class="chat-main">
        <div id="noChatSelected" class="m-auto text-center">
            <i class="fas fa-user-shield fa-3x text-light mb-3"></i>
            <h5 class="fw-bold">Contact Admin</h5>
            <p class="text-muted small">Select an administrator to start chatting.</p>
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
                        <input type="text" id="msgInput" class="form-control border-0 bg-transparent" placeholder="Write message..." onkeypress="if(event.key==='Enter') send()">
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
            const res = await fetch("{{ route('agent.chat.users') }}");
            const data = await res.json();

            const totalBadge = document.getElementById('totalUnreadBadge');
            if (totalBadge) {
                totalBadge.innerText = data.total_unread;
                data.total_unread > 0 ? totalBadge.classList.remove('d-none') : totalBadge.classList.add('d-none');
            }

            const list = document.getElementById('userList');
            list.innerHTML = data.users.map(u => {
                const activeClass = (activeUser && activeUser.id === u.id) ? 'active' : '';
                let displayMsg = u.last_message || (u.has_attachment ? '<i class="fas fa-paperclip"></i> Attachment' : "No messages yet");

                return `
                <div class="user-card ${activeClass}" onclick='selectUser(${JSON.stringify(u)})'>
                    <div class="avatar-container">
                        ${u.business_logo ? `<img src="/storage/${u.business_logo}">` : `<i class="fas fa-user-tie text-muted"></i>`}
                        <span class="status-dot ${u.is_online ? 'bg-online' : 'bg-offline'}"></span>
                    </div>
                    <div class="ms-3 flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-truncate" style="font-size: 14px;">${u.business_name}</span>
                            <small style="font-size: 10px; color: #94a3b8;">${formatLocalTime(u.last_message_time)}</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted text-truncate w-75" style="font-size: 12px;">${displayMsg}</small>
                            ${u.unread_count > 0 ? `<span class="unread-count">${u.unread_count}</span>` : ''}
                        </div>
                    </div>
                </div>`;
            }).join('');
        } catch (e) {
            console.error(e);
        }
    }

    function selectUser(user) {
        activeUser = user;
        document.getElementById('noChatSelected').classList.add('d-none');
        document.getElementById('chatActive').classList.remove('d-none');
        document.getElementById('headerName').innerText = user.business_name;
        document.getElementById('headerAvatar').innerHTML = user.business_logo ? `<img src="/storage/${user.business_logo}">` : `<i class="fas fa-user-tie text-muted"></i>`;
        document.getElementById('headerStatus').innerText = user.is_online ? 'Active Now' : 'Offline';
        loadMessages(true);
        loadUsers();
    }

    async function loadMessages(forceScroll = false) {
        if (!activeUser) return;
        try {
            const res = await fetch(`/agent/chat/messages/${activeUser.id}`);
            const msgs = await res.json();
            const box = document.getElementById('messageBox');

            if (msgs.length !== messageCount || forceScroll) {
                messageCount = msgs.length;
                box.innerHTML = '';
                msgs.forEach(m => {
                    const isSent = m.sender_id == authId;
                    const bubble = document.createElement('div');
                    bubble.className = `bubble ${isSent ? 'sent' : 'received'}`;

                    if (m.file) {
                        const fileUrl = `/storage/${m.file}`;
                        if (m.file.match(/\.(jpg|jpeg|png|gif)$/i)) {
                            bubble.innerHTML += `<a href="${fileUrl}" target="_blank"><img src="${fileUrl}" class="msg-file-preview"></a>`;
                        } else {
                            bubble.innerHTML += `<a href="${fileUrl}" target="_blank" class="doc-link"><i class="fas fa-file-alt"></i> View File</a>`;
                        }
                    }

                    const txt = document.createElement('div');
                    txt.textContent = m.message || '';
                    bubble.appendChild(txt);

                    const meta = document.createElement('div');
                    meta.className = 'msg-meta';
                    meta.innerHTML = `<span class="time">${formatLocalTime(m.created_at)}</span>`;

                    if (isSent) {
                        const iconClass = m.status === 'read' ? 'status-read' : 'text-white-50';
                        meta.innerHTML += `<i class="fas fa-check-double ms-1 ${iconClass}" style="font-size:10px;"></i>`;

                        // Delete logic check: only for sent messages
                        const actions = document.createElement('div');
                        actions.className = 'msg-actions';
                        actions.innerHTML = `<i class="fas fa-trash-alt text-danger" onclick="deleteMessage(${m.id})"></i>`;
                        bubble.appendChild(actions);
                    }

                    bubble.appendChild(meta);
                    box.appendChild(bubble);
                });
                box.scrollTop = box.scrollHeight;
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function send() {
        if (!activeUser) return;
        const input = document.getElementById('msgInput');
        const fileIn = document.getElementById('fileInput');
        if (!input.value.trim() && !fileIn.files[0]) return;

        const formData = new FormData();
        formData.append('receiver_id', activeUser.id);
        formData.append('message', input.value);
        if (fileIn.files[0]) formData.append('file', fileIn.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const res = await fetch("{{ route('agent.chat.send') }}", {
                method: 'POST'
                , body: formData
            });
            if (res.ok) {
                input.value = '';
                cancelAttachment();
                loadMessages(true);
            }
        } catch (e) {
            console.error(e);
        } finally {
            sendBtn.disabled = false;
            input.placeholder = "Write message...";
        }
    }

    async function deleteMessage(msgId) {
        if (!confirm('Delete your message?')) return;
        await fetch(`/agent/chat/delete/${msgId}`, {
            method: 'DELETE'
            , headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        loadMessages(true);
    }

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

    loadUsers();
    setInterval(loadUsers, 5000);
    setInterval(loadMessages, 3000);

</script>
@endsection
