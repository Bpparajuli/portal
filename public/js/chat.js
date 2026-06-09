(function() {
    'use strict';

    const cfg = window.CHAT_CONFIG || {};
    const role = cfg.role || 'staff';
    const authId = parseInt(cfg.authId) || 0;
    const canDeleteAny = cfg.canDeleteAny === true;
    const csrfToken = cfg.csrfToken || '';
    const usersRoute = cfg.usersRoute || '';
    const pusherKey = cfg.pusherKey || '';
    const pusherCluster = cfg.pusherCluster || '';
    const broadcastDefault = cfg.broadcastDefault || '';

    let activeUser = null;
    let allSections = [];
    let lastMessageId = 0;
    let typingTimer = null;
    let isTyping = false;
    let pollInterval = null;
    let isLoadingMessages = false;
    let messageIds = new Set();
    let audioCtx = null;

    function initAudio() {
        if (!audioCtx || audioCtx.state === 'closed') {
            try { audioCtx = new (window.AudioContext || window.webkitAudioContext)(); } catch(e) {}
        }
    }
    initAudio();
    document.addEventListener('click', initAudio);
    document.addEventListener('touchstart', initAudio);
    function getAudioCtx() {
        if (!audioCtx || audioCtx.state === 'closed') { initAudio(); }
        if (audioCtx && audioCtx.state === 'suspended') audioCtx.resume();
        return audioCtx;
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function formatTime(d) {
        if (!d) return '';
        const dt = new Date(d.endsWith('Z') ? d : d + 'Z');
        return dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function formatDate(d) {
        if (!d) return '';
        const dt = new Date(d.endsWith('Z') ? d : d + 'Z');
        const t = new Date();
        if (dt.toDateString() === t.toDateString()) return formatTime(d);
        const y = new Date(t);
        y.setDate(y.getDate() - 1);
        if (dt.toDateString() === y.toDateString()) return 'Yesterday';
        return dt.toLocaleDateString([], { month: 'short', day: 'numeric' });
    }

    function initials(n) {
        if (!n) return '?';
        return n.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
    }

    window.toggleChatSidebar = function() {
        const sb = document.getElementById('chatSidebar');
        const ov = document.getElementById('chatSidebarOverlay');
        const isMobile = window.innerWidth < 992;
        if (isMobile) {
            sb.classList.toggle('open');
            if (sb.classList.contains('open')) {
                document.getElementById('chatMain').style.display = '';
                document.getElementById('noChatSelected').classList.remove('d-none');
                document.getElementById('chatActive').classList.add('d-none');
                document.getElementById('chatActive').style.display = 'none';
                ov.classList.add('show');
            } else {
                ov.classList.remove('show');
            }
            const mainSb = document.getElementById('appSidebar');
            const mainOv = document.getElementById('sidebarOverlay');
            if (mainSb && mainSb.classList.contains('show')) {
                mainSb.classList.remove('show');
                mainOv.classList.remove('show');
            }
        } else {
            sb.classList.toggle('open');
            ov.style.display = sb.classList.contains('open') ? 'block' : 'none';
        }
    };

    document.getElementById('chatSidebarOverlay')?.addEventListener('click', window.toggleChatSidebar);

    function toggleSection(el) {
        var header = el.closest('.accordion-section')?.querySelector('.section-header');
        if (header) header.click();
    }

    function sectionIconHtml(role) {
        var icons = { admin: 'fa-user-shield', agent: 'fa-user-tie', staff: 'fa-users' };
        return '<i class="fas ' + (icons[role] || 'fa-user') + ' section-icon"></i>';
    }

    async function loadUsers() {
        try {
            const res = await fetch(usersRoute);
            const data = await res.json();
            const badge = document.getElementById('totalUnreadBadge');
            if (badge) {
                badge.textContent = data.total_unread;
                data.total_unread > 0 ? badge.classList.remove('d-none') : badge.classList.add('d-none');
            }
            allSections = data.sections || [];
            renderUserList();
        } catch(e) { console.error('loadUsers', e); }
    }

    function getActiveUserId() {
        return activeUser ? activeUser.id : null;
    }

    function buildUserHtml(u) {
        var ac = (activeUser && activeUser.id === u.id) ? 'active' : '';
        var dm = escapeHtml(u.last_message || '');
        if (!dm && (u.last_message_file || u.has_attachment)) dm = '<i class="fas fa-paperclip"></i> Attachment';
        if (!dm) dm = 'No messages yet';
        return '<div class="user-card ' + ac + '" data-uid="' + u.id + '">'
            + '<div class="chat-avatar">' + (u.business_logo ? '<img src="/storage/' + u.business_logo + '">' : initials(u.business_name || u.name))
            + '<span class="status-dot ' + (u.is_online ? 'online' : 'offline') + '"></span></div>'
            + '<div class="user-info-wrap"><div class="user-name">' + escapeHtml(u.business_name || u.name) + '</div>'
            + '<div class="user-preview">' + dm + '</div></div>'
            + '<div class="user-meta"><span class="user-time">' + formatDate(u.last_message_time) + '</span>'
            + (u.unread_count > 0 ? '<span class="unread-badge">' + u.unread_count + '</span>' : '') + '</div>'
            + '</div>';
    }

    function renderUserList() {
        var list = document.getElementById('userList');
        var searchQuery = (document.getElementById('userSearch')?.value || '').toLowerCase().trim();

        if (allSections.length === 0) {
            list.innerHTML = '<div class="text-center mt-4 text-muted small">No contacts found</div>';
            return;
        }

        var html = '';

        allSections.forEach(function(section, idx) {
            var filteredUsers = section.users;
            if (searchQuery) {
                filteredUsers = section.users.filter(function(u) {
                    var name = (u.business_name || u.name || '').toLowerCase();
                    return name.includes(searchQuery);
                });
            }
            if (filteredUsers.length === 0 && !searchQuery) return;

            var sectionHasActive = filteredUsers.some(function(u) { return u.id === getActiveUserId(); });
            var expanded = searchQuery ? true : (sectionHasActive || (idx === 0 && section.unread_count > 0));
            var collapseId = 'sec-collapse-' + idx;

            var unreadBadge = section.unread_count > 0
                ? '<span class="section-unread-badge">' + section.unread_count + '</span>'
                : '';

            html += '<div class="accordion-section">'
                + '<div class="section-header" data-bs-toggle="collapse" data-bs-target="#' + collapseId + '" aria-expanded="' + expanded + '" role="button">'
                + '<div class="section-header-left">'
                + sectionIconHtml(section.role)
                + '<span class="section-label">' + section.label + '</span>'
                + '</div>'
                + '<div class="section-header-right">'
                + unreadBadge
                + '<i class="fas fa-chevron-down section-chevron"></i>'
                + '</div>'
                + '</div>'
                + '<div id="' + collapseId + '" class="collapse' + (expanded ? ' show' : '') + '" data-bs-parent="#chatAccordion">'
                + '<div class="section-body">'
                + filteredUsers.map(buildUserHtml).join('')
                + '</div>'
                + '</div>'
                + '</div>';
        });

        list.innerHTML = html || '<div class="text-center mt-4 text-muted small">No contacts found</div>';

        allSections.forEach(function(section, idx) {
            var collapseId = 'sec-collapse-' + idx;
            var el = document.getElementById(collapseId);
            if (el) {
                el.addEventListener('show.bs.collapse', function() {
                    var header = this.closest('.accordion-section')?.querySelector('.section-header');
                    if (header) header.classList.add('expanded');
                });
                el.addEventListener('hide.bs.collapse', function() {
                    var header = this.closest('.accordion-section')?.querySelector('.section-header');
                    if (header) header.classList.remove('expanded');
                });
            }
        });
    }

    window.selectUser = async function(user) {
        activeUser = user;
        lastMessageId = 0;
        messageIds = new Set();

        document.getElementById('noChatSelected').classList.add('d-none');
        document.getElementById('chatActive').classList.remove('d-none');
        document.getElementById('chatActive').style.display = 'flex';
        document.getElementById('headerName').innerText = user.business_name || user.name;
        document.getElementById('headerAvatar').innerHTML = user.business_logo
            ? '<img src="/storage/' + user.business_logo + '" alt="' + (user.business_name || '') + '">'
            : initials(user.business_name || user.name);
        document.getElementById('headerStatus').innerText = user.is_online ? 'Active Now' : (user.last_seen ? 'Last seen ' + formatDate(user.last_seen) : 'Offline');

        if (window.innerWidth < 992) {
            document.getElementById('chatSidebar').classList.remove('open');
            document.getElementById('chatSidebarOverlay').classList.remove('show');
            document.getElementById('chatMain').style.display = 'flex';
        }

        await loadMessages(true);
        loadUsers();
        startPolling();
        if (typeof window.checkChatUnread !== 'undefined') window.checkChatUnread();
    };

    async function loadMessages(forceScroll) {
        if (!activeUser || isLoadingMessages) return;
        try {
            isLoadingMessages = true;
            var url = (forceScroll || lastMessageId === 0)
                ? '/' + role + '/chat/messages/' + activeUser.id
                : '/' + role + '/chat/new?user_id=' + activeUser.id + '&last_message_id=' + lastMessageId;
            const res = await fetch(url);
            const msgs = await res.json();
            const box = document.getElementById('messageBox');
            if (forceScroll || lastMessageId === 0) {
                messageIds = new Set();
                box.innerHTML = '';
                renderMessages(msgs, box);
                box.scrollTop = box.scrollHeight;
            } else if (msgs.length > 0) {
                var newMsgs = msgs.filter(function(m) { return !messageIds.has(m.id); });
                if (newMsgs.length > 0) {
                    renderMessages(newMsgs, box, true);
                    box.scrollTop = box.scrollHeight;
                    if (newMsgs.some(function(m) { return m.sender_id != authId; })) playSound('received');
                }
            }
        } catch(e) { console.error(e); }
        finally { isLoadingMessages = false; }
    }

    function renderMessages(msgs, box, append) {
        if (!append) {
            box.innerHTML = '';
            var groups = {};
            msgs.forEach(function(m) {
                var d = m.created_at ? new Date(m.created_at.endsWith('Z') ? m.created_at : m.created_at + 'Z').toDateString() : '';
                if (!groups[d]) groups[d] = [];
                groups[d].push(m);
            });
            Object.entries(groups).forEach(function(_ref) {
                var date = _ref[0], gMsgs = _ref[1];
                var l = new Date(date).toDateString() === new Date().toDateString() ? 'Today'
                    : new Date(date).toDateString() === new Date(Date.now() - 86400000).toDateString() ? 'Yesterday'
                    : new Date(date).toLocaleDateString([], { weekday: 'long', month: 'short', day: 'numeric' });
                var d = document.createElement('div');
                d.style.cssText = 'text-align:center;margin:6px 0;';
                d.innerHTML = '<span style="background:rgba(225,218,208,0.8);padding:3px 10px;border-radius:8px;font-size:11px;color:#667781;">' + l + '</span>';
                box.appendChild(d);
                gMsgs.forEach(function(m) {
                    if (m.id > lastMessageId) lastMessageId = m.id;
                    messageIds.add(m.id);
                    appendBubble(m, box);
                });
            });
        } else {
            msgs.forEach(function(m) {
                if (m.id > lastMessageId) lastMessageId = m.id;
                messageIds.add(m.id);
                appendBubble(m, box);
            });
        }
    }

    function appendBubble(m, box) {
        var sent = m.sender_id == authId;
        var b = document.createElement('div');
        b.className = 'bubble ' + (sent ? 'sent' : 'received');
        b.id = 'msg-' + m.id;

        if (m.file) {
            var url = '/storage/' + m.file;
            if (m.file.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
                var a = document.createElement('a');
                a.href = url; a.target = '_blank';
                var img = document.createElement('img');
                img.src = url; img.className = 'msg-image'; img.loading = 'lazy';
                a.appendChild(img); b.appendChild(a);
            } else {
                var a2 = document.createElement('a');
                a2.href = url; a2.target = '_blank'; a2.className = 'msg-file';
                a2.innerHTML = '<i class="fas fa-file-alt" style="color:var(--primary);"></i><span>' + (m.file.split('/').pop()) + '</span>';
                b.appendChild(a2);
            }
        }
        if (m.message) {
            var t = document.createElement('div');
            t.className = 'msg-text';
            t.textContent = m.message;
            b.appendChild(t);
        }

        var meta = document.createElement('div');
        meta.className = 'msg-meta';
        var time = document.createElement('span');
        time.className = 'msg-time';
        time.textContent = formatTime(m.created_at);
        meta.appendChild(time);

        if (sent) {
            var tk = document.createElement('span');
            tk.className = 'msg-tick';
            tk.innerHTML = m.read_at
                ? '<i class="fas fa-check-double" style="color:#53bdeb;"></i>'
                : '<i class="fas fa-check" style="color:#8696a0;"></i>';
            meta.appendChild(tk);
        }

        if (sent || canDeleteAny) {
            var del = document.createElement('button');
            del.className = 'btn-del-msg';
            del.innerHTML = '<i class="fas fa-trash-alt"></i>';
            del.onclick = function(e) { e.stopPropagation(); deleteMessage(m.id); };
            b.appendChild(del);
        }

        b.appendChild(meta);
        box.appendChild(b);
    }

    function startPolling() {
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(function() {
            if (activeUser) loadMessages(false);
        }, 4000);
    }
    function stopPolling() {
        if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
    }

    function handleFileSelect(input) {
        var f = input.files[0];
        if (!f) return;
        document.getElementById('fileNameDisplay').innerText = f.name;
        document.getElementById('previewIcon').innerHTML = f.type.startsWith('image/')
            ? '<i class="fas fa-image fs-5" style="color:var(--primary);"></i>'
            : '<i class="fas fa-file-alt fs-5" style="color:var(--primary);"></i>';
        document.getElementById('attachmentPreview').classList.add('show');
    }
    window.handleFileSelect = handleFileSelect;

    window.cancelAttachment = function() {
        document.getElementById('fileInput').value = '';
        document.getElementById('attachmentPreview').classList.remove('show');
    };

    window.send = async function() {
        if (!activeUser) return;
        var input = document.getElementById('msgInput');
        var fileIn = document.getElementById('fileInput');
        var btn = document.getElementById('sendBtn');
        var msg = input.value.trim();
        if (!msg && !fileIn.files[0]) return;

        var fd = new FormData();
        fd.append('receiver_id', activeUser.id);
        fd.append('message', msg);
        if (fileIn.files[0]) fd.append('file', fileIn.files[0]);
        fd.append('_token', csrfToken);

        btn.disabled = true;
        input.placeholder = 'Sending...';

        try {
            var res = await fetch('/' + role + '/chat/send', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                input.value = '';
                window.cancelAttachment();
                playSound('sent');
                var data = await res.json();
                if (data.data) {
                    var box = document.getElementById('messageBox');
                    messageIds.add(data.data.id);
                    if (data.data.id > lastMessageId) lastMessageId = data.data.id;
                    appendBubble(data.data, box);
                    box.scrollTop = box.scrollHeight;
                }
                loadUsers();
            }
        } catch(e) { console.error(e); }
        finally {
            btn.disabled = false;
            input.placeholder = 'Type a message...';
            input.focus();
        }
    };

    function deleteMessage(msgId) {
        Swal.fire({
            title: 'Delete message?',
            text: 'This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Delete'
        }).then(async function(r) {
            if (r.isConfirmed) {
                await fetch('/' + role + '/chat/delete/' + msgId, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                loadMessages(true);
            }
        });
    }

    window.clearChat = function() {
        if (!activeUser) return;
        Swal.fire({
            title: 'Clear conversation?',
            text: 'All messages will be deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Clear'
        }).then(async function(r) {
            if (r.isConfirmed) {
                await fetch('/' + role + '/chat/clear/' + activeUser.id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                loadMessages(true);
                loadUsers();
            }
        });
    };

    window.onTyping = function() {
        if (!isTyping && activeUser) {
            isTyping = true;
            fetch('/' + role + '/chat/typing', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ receiver_id: activeUser.id, typing: true })
            });
        }
        clearTimeout(typingTimer);
        typingTimer = setTimeout(function() {
            isTyping = false;
            if (activeUser) {
                fetch('/' + role + '/chat/typing', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ receiver_id: activeUser.id, typing: false })
                });
            }
        }, 1500);
    };

    window.filterUsers = function() {
        renderUserList();
    };

    function playSound(type) {
        if (!type) type = 'received';
        try {
            var ctx = getAudioCtx();
            if (!ctx) return;
            if (type === 'received') {
                var t = ctx.currentTime;
                var osc1 = ctx.createOscillator(), g1 = ctx.createGain();
                osc1.connect(g1); g1.connect(ctx.destination);
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(1200, t);
                g1.gain.setValueAtTime(0.28, t);
                g1.gain.exponentialRampToValueAtTime(0.001, t + 0.06);
                osc1.frequency.setValueAtTime(1800, t + 0.10);
                g1.gain.setValueAtTime(0.38, t + 0.10);
                g1.gain.setValueAtTime(0.32, t + 0.11);
                g1.gain.exponentialRampToValueAtTime(0.001, t + 0.30);
                osc1.start(t); osc1.stop(t + 0.31);
                var osc2 = ctx.createOscillator(), g2 = ctx.createGain();
                osc2.connect(g2); g2.connect(ctx.destination);
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(1800, t);
                g2.gain.setValueAtTime(0.14, t);
                g2.gain.exponentialRampToValueAtTime(0.001, t + 0.05);
                osc2.frequency.setValueAtTime(2800, t + 0.10);
                g2.gain.setValueAtTime(0.20, t + 0.10);
                g2.gain.exponentialRampToValueAtTime(0.001, t + 0.28);
                osc2.start(t); osc2.stop(t + 0.31);
            } else {
                var osc = ctx.createOscillator(), gain = ctx.createGain();
                osc.connect(gain); gain.connect(ctx.destination);
                osc.frequency.value = 880; osc.type = 'sine';
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);
                osc.start(ctx.currentTime); osc.stop(ctx.currentTime + 0.1);
            }
        } catch(e) {}
    }

    function showToast(message, type, title) {
        if (!type) type = 'info';
        if (!title) title = '';
        var toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:300px;padding:16px;border-radius:8px;color:#fff;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.15);animation:slideIn 0.3s ease;';
        var colors = { info: '#2176ff', success: '#00a884', error: '#dc3545', warning: '#ff9800' };
        toast.style.background = colors[type] || colors.info;
        toast.innerHTML = '<strong>' + title + '</strong><br>' + message;
        document.body.appendChild(toast);
        setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; }, 3000);
        setTimeout(function() { toast.remove(); }, 3500);
    }

    function initPusher() {
        if (typeof Pusher === 'undefined') return;
        if (!pusherKey || pusherKey === '' || broadcastDefault !== 'pusher') return;
        var pusher = new Pusher(pusherKey, { cluster: pusherCluster, forceTLS: true, enabledTransports: ['ws', 'wss'] });
        var ch = pusher.subscribe('chat.' + authId);
        ch.bind('App\\Events\\MessageSent', function(data) {
            if (!activeUser || activeUser.id != data.message.sender_id) { playSound('received'); showToast('New message from ' + (data.sender_name || 'someone'), 'info', 'Chat'); }
            loadUsers(); if (activeUser) loadMessages(false);
        });
        ch.bind('App\\Events\\UserTyping', function(data) {
            if (activeUser && activeUser.id == data.user_id) { document.getElementById('typingIndicator').classList.toggle('d-none', !data.typing); }
        });
    }

    document.getElementById('userList').addEventListener('click', function(e) {
        var card = e.target.closest('.user-card');
        if (card) {
            var uid = parseInt(card.dataset.uid);
            if (uid) {
                for (var i = 0; i < allSections.length; i++) {
                    for (var j = 0; j < allSections[i].users.length; j++) {
                        if (allSections[i].users[j].id === uid) {
                            window.selectUser(allSections[i].users[j]);
                            return;
                        }
                    }
                }
            }
        }
    });

    document.getElementById('messageBox')?.addEventListener('contextmenu', function(e) {
        var b = e.target.closest(canDeleteAny ? '.bubble' : '.bubble.sent');
        if (!b) return;
        e.preventDefault();
        var id = b.id.replace('msg-', '');
        if (id) deleteMessage(id);
    });

    document.getElementById('msgInput')?.addEventListener('focus', function() {
        setTimeout(function() { this.scrollIntoView({ behavior: 'smooth', block: 'center' }); }.bind(this), 300);
    });
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', function() {
            var wrapper = document.querySelector('.chat-wrapper');
            if (!wrapper) return;
            wrapper.style.height = (window.innerHeight - this.height > 80) ? (this.height - 46 + 'px') : '';
        });
    }

    if (window.innerWidth < 992) {
        document.getElementById('chatSidebar').classList.add('open');
        document.getElementById('chatSidebarOverlay').classList.add('show');
        document.getElementById('chatMain').style.display = 'none';
    }
    loadUsers();
    setInterval(loadUsers, 15000);
    initPusher();

})();