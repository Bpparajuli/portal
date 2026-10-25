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

    if (!launcher || !panel) return;

    var state = { userId: null, userName: '', userAvatar: '', lastId: 0, ids: new Set(), poll: null, listPoll: null };

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : s;
        return d.innerHTML;
    }

    function initials(n) {
        if (!n) return '?';
        return n.trim().split(/\s+/).map(function (w) { return w[0]; }).join('').substring(0, 2).toUpperCase();
    }

    function avatarHtml(avatar, name) {
        if (avatar) return '<img src="/storage/' + esc(avatar) + '" alt="">';
        return esc(initials(name));
    }

    function fmtTime(d) {
        if (!d) return '';
        var dt = new Date(String(d).endsWith('Z') ? d : d + 'Z');
        if (isNaN(dt)) return '';
        return dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // ---------- persistence ----------
    function save() {
        try {
            sessionStorage.setItem('cw_open', panel.style.display !== 'none' ? '1' : '0');
            sessionStorage.setItem('cw_uid', state.userId || '');
            sessionStorage.setItem('cw_uname', state.userName || '');
            sessionStorage.setItem('cw_uavatar', state.userAvatar || '');
        } catch (e) {}
    }

    // ---------- view switches ----------
    function showPanel() { panel.style.display = 'flex'; save(); }
    function hidePanel() { panel.style.display = 'none'; save(); }
    function showList() {
        stopThreadPoll();
        listView.style.display = 'block';
        threadView.style.display = 'none';
        backBtn.style.display = 'none';
        titleEl.textContent = 'Messages';
        save();
    }
    function showThread() {
        listView.style.display = 'none';
        threadView.style.display = 'flex';
        backBtn.style.display = '';
        titleEl.textContent = state.userName || 'Chat';
    }

    // ---------- conversations list ----------
    function loadConversations() {
        fetch(cfg.conversationsUrl, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (d) { renderList(d && d.conversations ? d.conversations : []); })
            .catch(function () {});
    }

    function renderList(convos) {
        if (!convos.length) {
            listEl.innerHTML = '<div class="cw-empty">No conversations yet.</div>';
            return;
        }
        listEl.innerHTML = convos.map(function (c) {
            var badge = c.unread > 0 ? '<span class="badge bg-danger" style="font-size:10px;">' + c.unread + '</span>' : '';
            return '<div class="cw-conv" data-id="' + c.id + '" data-name="' + esc(c.name) + '" data-avatar="' + esc(c.avatar || '') + '">'
                + '<div class="cw-avatar">' + avatarHtml(c.avatar, c.name) + '</div>'
                + '<div class="cw-conv-main">'
                + '<div class="cw-conv-name"><span class="text-truncate">' + esc(c.name) + '</span>' + badge + '</div>'
                + '<div class="cw-conv-sub">' + esc(c.last_message || 'No messages yet') + (c.time ? ' · ' + esc(c.time) : '') + '</div>'
                + '</div></div>';
        }).join('');
    }

    listEl.addEventListener('click', function (e) {
        var row = e.target.closest('.cw-conv');
        if (!row) return;
        openThread(parseInt(row.dataset.id), row.dataset.name, row.dataset.avatar);
    });

    // ---------- thread ----------
    function openThread(id, name, avatar) {
        state.userId = id;
        state.userName = name || '';
        state.userAvatar = avatar || '';
        state.lastId = 0;
        state.ids = new Set();
        messagesEl.innerHTML = '';
        showThread();
        save();
        loadMessages();
        startThreadPoll();
    }

    function renderMessage(m) {
        if (state.ids.has(m.id)) return;
        state.ids.add(m.id);
        if (m.id > state.lastId) state.lastId = m.id;

        var sent = parseInt(m.sender_id) === authId;
        var html = '<div class="cw-bubble ' + (sent ? 'sent' : 'received') + '">';
        if (m.file) {
            var url = '/storage/' + m.file;
            if (/\.(jpg|jpeg|png|gif|webp)$/i.test(m.file)) {
                html += '<a href="' + url + '" target="_blank" rel="noopener"><img src="' + url + '" loading="lazy" alt=""></a>';
            } else {
                html += '<a class="cw-file" href="' + url + '" target="_blank" rel="noopener"><i class="fas fa-file-alt"></i>' + esc(m.file.split('/').pop()) + '</a>';
            }
        }
        if (m.message) html += '<span>' + esc(m.message) + '</span>';
        html += '<span class="cw-time">' + fmtTime(m.created_at) + '</span></div>';
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
                loadConversations();
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

    function startThreadPoll() {
        stopThreadPoll();
        state.poll = setInterval(pollNew, 4000);
    }
    function stopThreadPoll() {
        if (state.poll) { clearInterval(state.poll); state.poll = null; }
    }

    // ---------- send ----------
    function sendMessage() {
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
            if (state.userId) { showThread(); showPanel(); loadMessages(); startThreadPoll(); }
            else { showList(); showPanel(); }
            loadConversations();
        } else {
            hidePanel();
        }
    });

    // ---------- init / restore ----------
    function init() {
        loadConversations();
        state.listPoll = setInterval(function () {
            loadConversations();
            if (panel.style.display !== 'none' && !state.userId) { /* list visible */ }
        }, 15000);

        var open = '0', uid = '', uname = '', uavatar = '';
        try {
            open = sessionStorage.getItem('cw_open') || '0';
            uid = sessionStorage.getItem('cw_uid') || '';
            uname = sessionStorage.getItem('cw_uname') || '';
            uavatar = sessionStorage.getItem('cw_uavatar') || '';
        } catch (e) {}

        if (open === '1') {
            showPanel();
            if (uid) openThread(parseInt(uid), uname, uavatar);
            else showList();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
