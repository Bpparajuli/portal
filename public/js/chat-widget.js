(function () {
    'use strict';

    var cfg = window.CW_CONFIG;
    if (!cfg || !cfg.base) return;

    var authId = parseInt(cfg.authId) || 0;
    var base = cfg.base;

    var launcher = document.getElementById('cwLauncher');
    var panel = document.getElementById('cwPanel');
    var listView = document.getElementById('cwListView');
    var threadView = document.getElementById('cwThreadView');
    var listEl = document.getElementById('cwList');
    var messagesEl = document.getElementById('cwMessages');
    var inputEl = document.getElementById('cwInput');
    var formEl = document.getElementById('cwForm');
    var fileInput = document.getElementById('cwFileInput');
    var backBtn = document.getElementById('cwBack');
    var titleEl = document.getElementById('cwTitle');
    var onlineDot = document.getElementById('cwOnlineDot');
    var searchEl = document.getElementById('cwUserSearch');
    var onlineOnlyEl = document.getElementById('cwOnlineOnly');

    if (!launcher || !panel) return;

    var state = {
        userId: null, userName: '', userAvatar: '', userOnline: false,
        lastId: 0, ids: new Set(),
        poll: null, tickPoll: null, listPoll: null,
        users: [], search: '', onlineOnly: false
    };

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : s;
        return d.innerHTML;
    }

    function initials(n) {
        if (!n) return '?';
        return n.trim().split(/\s+/).map(function (w) { return w[0]; }).join('').substring(0, 2).toUpperCase();
    }

    // Show "Person Name (Business Name)" — business name in parentheses.
    function displayName(u) {
        var n = (u && u.name) || '';
        var b = (u && u.business_name) || '';
        if (!n) return b || 'User';
        if (b && b !== n) return n + ' (' + b + ')';
        return n;
    }

    function avatarHtml(avatar, name, online) {
        return '<div class="cw-avatar">'
            + (avatar ? '<img src="/storage/' + esc(avatar) + '" alt="">' : esc(initials(name)))
            + '<span class="cw-status-dot ' + (online ? 'online' : '') + '"></span>'
            + '</div>';
    }

    function fmtTime(d) {
        if (!d) return '';
        var dt = new Date(String(d).endsWith('Z') ? d : d + 'Z');
        if (isNaN(dt)) return '';
        return dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function fmtListTime(d) {
        if (!d) return '';
        var dt = new Date(String(d).endsWith('Z') ? d : d + 'Z');
        if (isNaN(dt)) return '';
        var now = new Date();
        if (dt.toDateString() === now.toDateString()) return dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        var y = new Date(); y.setDate(y.getDate() - 1);
        if (dt.toDateString() === y.toDateString()) return 'Yesterday';
        return dt.toLocaleDateString([], { month: 'short', day: 'numeric' });
    }

    function tickIcon(readAt) {
        return readAt
            ? '<i class="fas fa-check-double" style="color:#53bdeb;"></i>'
            : '<i class="fas fa-check" style="color:#9ca3af;"></i>';
    }

    // ---------- persistence ----------
    function save() {
        try {
            sessionStorage.setItem('cw_open', panel.style.display !== 'none' ? '1' : '0');
            sessionStorage.setItem('cw_uid', state.userId || '');
            sessionStorage.setItem('cw_uname', state.userName || '');
            sessionStorage.setItem('cw_uavatar', state.userAvatar || '');
            sessionStorage.setItem('cw_uonline', state.userOnline ? '1' : '0');
        } catch (e) {}
    }

    // ---------- view switches ----------
    function showPanel() { panel.style.display = 'flex'; save(); }
    function hidePanel() { panel.style.display = 'none'; stopThreadPoll(); stopTickPoll(); save(); }
    function showList() {
        stopThreadPoll(); stopTickPoll();
        listView.style.display = 'block';
        threadView.style.display = 'none';
        backBtn.style.display = 'none';
        titleEl.textContent = 'Messages';
        onlineDot.style.display = 'none';
        save();
        loadUsers();
    }
    function showThread() {
        listView.style.display = 'none';
        threadView.style.display = 'flex';
        backBtn.style.display = '';
        titleEl.textContent = state.userName || 'Chat';
        onlineDot.style.display = state.userOnline ? '' : 'none';
        onlineDot.classList.toggle('online', !!state.userOnline);
    }

    // ---------- people list (all users + online) ----------
    function loadUsers() {
        fetch(cfg.usersUrl, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var flat = [];
                (d.sections || []).forEach(function (sec) {
                    (sec.users || []).forEach(function (u) {
                        u._role = sec.role;
                        flat.push(u);
                    });
                });
                state.users = flat;
                renderList();
            })
            .catch(function () {});
    }

    function renderList() {
        var q = state.search.toLowerCase();

        var users = state.users.filter(function (u) {
            if (state.onlineOnly && !u.is_online) return false;
            if (!q) return true;
            var hay = ((u.business_name || u.name || '') + ' ' + (u.name || '') + ' ' + (u.email || '') + ' ' + (u.last_message || '')).toLowerCase();
            return hay.indexOf(q) !== -1;
        });

        users.sort(function (a, b) {
            if ((!!a.is_online) !== (!!b.is_online)) return a.is_online ? -1 : 1;
            var ua = a.unread_count || 0, ub = b.unread_count || 0;
            if (ua !== ub) return ub - ua;
            var ta = a.last_message_time ? new Date(a.last_message_time).getTime() : 0;
            var tb = b.last_message_time ? new Date(b.last_message_time).getTime() : 0;
            return tb - ta;
        });

        if (!users.length) {
            listEl.innerHTML = '<div class="cw-empty">No people found.</div>';
            return;
        }

        listEl.innerHTML = users.map(function (u) {
            var display = displayName(u);
            var badge = (u.unread_count > 0) ? '<span class="badge bg-danger" style="font-size:10px;">' + u.unread_count + '</span>' : '';
            var sub = u.last_message || (u.is_online ? 'Active now' : 'Tap to start a chat');
            return '<div class="cw-conv" data-id="' + u.id + '" data-name="' + esc(display) + '"'
                + ' data-avatar="' + esc(u.business_logo || '') + '" data-online="' + (u.is_online ? '1' : '0') + '">'
                + avatarHtml(u.business_logo, u.name || u.business_name || 'User', u.is_online)
                + '<div class="cw-conv-main">'
                + '<div class="cw-conv-name"><span class="text-truncate">' + esc(display) + '</span>' + badge + '</div>'
                + '<div class="cw-conv-sub">' + esc(sub) + (u.last_message_time ? ' · ' + esc(fmtListTime(u.last_message_time)) : '') + '</div>'
                + '</div></div>';
        }).join('');
    }

    if (searchEl) {
        searchEl.addEventListener('input', function () { state.search = this.value; renderList(); });
    }
    if (onlineOnlyEl) {
        onlineOnlyEl.addEventListener('change', function () { state.onlineOnly = this.checked; renderList(); });
    }

    listEl.addEventListener('click', function (e) {
        var row = e.target.closest('.cw-conv');
        if (!row) return;
        openThread(parseInt(row.dataset.id), row.dataset.name, row.dataset.avatar, row.dataset.online === '1');
    });

    // ---------- thread ----------
    function openThread(id, name, avatar, online) {
        state.userId = id;
        state.userName = name || '';
        state.userAvatar = avatar || '';
        state.userOnline = !!online;
        state.lastId = 0;
        state.ids = new Set();
        messagesEl.innerHTML = '';
        showThread();
        save();
        loadMessages();
        startThreadPoll();
        startTickPoll();
    }

    function renderMessage(m) {
        if (state.ids.has(m.id)) return;
        state.ids.add(m.id);
        if (m.id > state.lastId) state.lastId = m.id;

        var sent = parseInt(m.sender_id) === authId;
        var html = '<div class="cw-bubble ' + (sent ? 'sent' : 'received') + '" id="cw-msg-' + m.id + '">';

        if (m.file) {
            var url = '/storage/' + m.file;
            if (/\.(jpg|jpeg|png|gif|webp)$/i.test(m.file)) {
                html += '<a href="' + url + '" target="_blank" rel="noopener"><img src="' + url + '" loading="lazy" alt=""></a>';
            } else {
                html += '<a class="cw-file" href="' + url + '" target="_blank" rel="noopener"><i class="fas fa-file-alt"></i>' + esc(m.file.split('/').pop()) + '</a>';
            }
        }
        if (m.message) html += '<span>' + esc(m.message) + '</span>';

        html += '<span class="cw-time">' + fmtTime(m.created_at);
        if (sent) {
            html += '<span class="cw-tick" data-tick-id="' + m.id + '">' + tickIcon(m.read_at) + '</span>';
            html += '<button type="button" class="cw-del" data-del-id="' + m.id + '" title="Delete">✕</button>';
        }
        html += '</span></div>';

        messagesEl.insertAdjacentHTML('beforeend', html);
    }

    function scrollBottom() { messagesEl.scrollTop = messagesEl.scrollHeight; }

    function loadMessages() {
        fetch('/' + base + '/messages/' + state.userId, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (msgs) {
                if (!Array.isArray(msgs)) return;
                messagesEl.innerHTML = '';
                state.ids = new Set();
                state.lastId = 0;
                msgs.forEach(renderMessage);
                scrollBottom();
                loadUsers();
            })
            .catch(function () {});
    }

    function pollNew() {
        if (!state.userId) return;
        fetch('/' + base + '/new?user_id=' + state.userId + '&last_message_id=' + state.lastId, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (msgs) {
                if (!Array.isArray(msgs) || !msgs.length) return;
                msgs.forEach(renderMessage);
                scrollBottom();
            })
            .catch(function () {});
    }

    // Refresh sent-message ticks (seen/unseen) without rebuilding the DOM.
    function refreshTicks() {
        if (!state.userId) return;
        fetch('/' + base + '/messages/' + state.userId, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (msgs) {
                if (!Array.isArray(msgs)) return;
                var map = {};
                msgs.forEach(function (m) { map[m.id] = m; });
                messagesEl.querySelectorAll('[data-tick-id]').forEach(function (el) {
                    var m = map[el.getAttribute('data-tick-id')];
                    if (!m) return;
                    var icon = tickIcon(m.read_at);
                    if (el.innerHTML !== icon) el.innerHTML = icon;
                });
            })
            .catch(function () {});
    }

    function startThreadPoll() { stopThreadPoll(); state.poll = setInterval(pollNew, 2000); }
    function stopThreadPoll() { if (state.poll) { clearInterval(state.poll); state.poll = null; } }
    function startTickPoll() { stopTickPoll(); state.tickPoll = setInterval(refreshTicks, 10000); }
    function stopTickPoll() { if (state.tickPoll) { clearInterval(state.tickPoll); state.tickPoll = null; } }

    // Delete own message
    messagesEl.addEventListener('click', function (e) {
        var btn = e.target.closest('.cw-del');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        if (!confirm('Delete this message?')) return;

        var id = parseInt(btn.getAttribute('data-del-id'));
        fetch('/' + base + '/delete/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d && d.status === 'success') {
                    var el = document.getElementById('cw-msg-' + id);
                    if (el) el.remove();
                }
            })
            .catch(function () {});
    });

    // ---------- send ----------
    function sendMessage() {
        if (!state.userId) return;
        var text = inputEl.value.trim();
        var file = fileInput.files && fileInput.files[0];
        if (!text && !file) return;

        var fd = new FormData();
        fd.append('receiver_id', state.userId);
        fd.append('message', text);
        if (file) fd.append('file', file);

        inputEl.value = '';
        fileInput.value = '';

        fetch('/' + base + '/send', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': cfg.csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: fd
        })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d && d.success && d.data) { renderMessage(d.data); scrollBottom(); }
                else if (d && d.error) { alert(d.error); }
            })
            .catch(function () {});
    }

    formEl.addEventListener('submit', function (e) { e.preventDefault(); sendMessage(); });
    document.getElementById('cwAttach').addEventListener('click', function () { fileInput.click(); });
    fileInput.addEventListener('change', function () { if (fileInput.files.length) sendMessage(); });

    // ---------- header buttons ----------
    backBtn.addEventListener('click', showList);
    document.getElementById('cwMinimize').addEventListener('click', hidePanel);

    launcher.addEventListener('click', function () {
        if (panel.style.display === 'none') {
            showPanel();
            if (state.userId) {
                showThread();
                loadMessages();
                startThreadPoll();
                startTickPoll();
            } else {
                showList();
            }
        } else {
            hidePanel();
        }
    });

    // ---------- init / restore ----------
    function init() {
        // Keep the user "online" and keep the people list fresh.
        loadUsers();
        state.listPoll = setInterval(loadUsers, 20000);

        var open = '0', uid = '', uname = '', uavatar = '', uonline = '0';
        try {
            open = sessionStorage.getItem('cw_open') || '0';
            uid = sessionStorage.getItem('cw_uid') || '';
            uname = sessionStorage.getItem('cw_uname') || '';
            uavatar = sessionStorage.getItem('cw_uavatar') || '';
            uonline = sessionStorage.getItem('cw_uonline') || '0';
        } catch (e) {}

        if (open === '1') {
            showPanel();
            if (uid) openThread(parseInt(uid), uname, uavatar, uonline === '1');
            else showList();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
