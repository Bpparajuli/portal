@auth
    @php
        $__wUser = auth()->user();
        $__isChatPage = request()->routeIs('admin.chat', 'agent.chat', 'staff.chat.index');
        $__cwBase = $__wUser->is_admin ? 'admin/chat' : ($__wUser->is_agent ? 'agent/chat' : 'staff/chat');
    @endphp

    @unless ($__isChatPage)
        <style>
            .cw-launcher {
                position: fixed; right: 20px; bottom: 20px; z-index: 1085;
                width: 58px; height: 58px; border-radius: 50%; border: none; cursor: pointer;
                background: linear-gradient(135deg, var(--primary, #1a0262), var(--secondary, #820b5c));
                color: #fff; font-size: 22px; box-shadow: 0 6px 18px rgba(0, 0, 0, .28);
                display: flex; align-items: center; justify-content: center; transition: transform .15s;
            }
            .cw-launcher:hover { transform: scale(1.05); }
            .cw-launcher .cw-badge {
                position: absolute; top: -4px; right: -4px; background: var(--danger, #ef4444); color: #fff;
                font-size: 11px; font-weight: 700; min-width: 20px; height: 20px; border-radius: 10px;
                display: flex; align-items: center; justify-content: center; padding: 0 5px; border: 2px solid #fff;
            }
            .cw-panel {
                position: fixed; right: 20px; bottom: 90px; z-index: 1085;
                width: 350px; height: 470px; max-height: calc(100vh - 120px);
                background: #fff; border-radius: 14px; box-shadow: 0 12px 40px rgba(0, 0, 0, .28);
                display: flex; flex-direction: column; overflow: hidden;
            }
            .cw-header {
                display: flex; align-items: center; gap: 8px; padding: 10px 12px;
                background: linear-gradient(135deg, var(--primary, #1a0262), var(--secondary, #820b5c));
                color: #fff; font-size: 14px; font-weight: 600; flex-shrink: 0;
            }
            .cw-header button, .cw-header a { background: none; border: none; color: #fff; font-size: 14px; cursor: pointer; padding: 4px; }
            .cw-header .cw-title { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .cw-body { flex: 1; overflow-y: auto; }
            .cw-conv { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-bottom: 1px solid #f1f1f5; cursor: pointer; }
            .cw-conv:hover { background: #faf9fc; }
            .cw-avatar { width: 38px; height: 38px; border-radius: 50%; background: #ede5f8; color: #1a0262; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; overflow: hidden; }
            .cw-avatar img { width: 100%; height: 100%; object-fit: cover; }
            .cw-conv-main { flex: 1; min-width: 0; }
            .cw-conv-name { font-size: 13px; font-weight: 600; color: #1f2937; display: flex; align-items: center; gap: 6px; }
            .cw-conv-sub { font-size: 11px; color: #6b7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .cw-thread { display: flex; flex-direction: column; height: 100%; }
            .cw-messages { flex: 1; overflow-y: auto; padding: 12px; background: #f7f6fb; }
            .cw-bubble { max-width: 78%; padding: 7px 11px; border-radius: 12px; font-size: 13px; line-height: 1.35; margin-bottom: 6px; word-wrap: break-word; white-space: pre-wrap; }
            .cw-bubble.sent { background: var(--primary, #1a0262); color: #fff; margin-left: auto; border-bottom-right-radius: 3px; }
            .cw-bubble.received { background: #fff; color: #1f2937; border-bottom-left-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,.06); }
            .cw-bubble img { max-width: 100%; border-radius: 8px; display: block; }
            .cw-bubble .cw-file { color: inherit; text-decoration: underline; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; }
            .cw-bubble .cw-time { display: block; font-size: 9px; opacity: .65; margin-top: 3px; text-align: right; }
            .cw-form { display: flex; align-items: center; gap: 6px; padding: 8px; border-top: 1px solid #f1f1f5; background: #fff; flex-shrink: 0; }
            .cw-form input[type=text] { flex: 1; border: 1px solid #e5e7eb; border-radius: 20px; padding: 8px 12px; font-size: 13px; outline: none; }
            .cw-form button { border: none; background: none; color: var(--primary, #1a0262); font-size: 16px; cursor: pointer; padding: 4px; }
            .cw-empty { text-align: center; color: #9ca3af; font-size: 13px; padding: 30px 10px; }
            @media (max-width: 576px) {
                .cw-panel { inset: 0; width: 100%; height: 100%; max-height: 100%; border-radius: 0; bottom: 0; right: 0; }
            }
        </style>

        <button id="cwLauncher" class="cw-launcher" type="button" title="Chat">
            <i class="fas fa-comment-dots"></i>
            <span id="chatUnreadBadge" class="cw-badge" style="display:none;">0</span>
        </button>

        <div id="cwPanel" class="cw-panel" style="display:none;">
            <div class="cw-header">
                <button id="cwBack" type="button" style="display:none;" title="Back"><i class="fas fa-arrow-left"></i></button>
                <i id="cwHeaderIcon" class="fas fa-comments"></i>
                <span class="cw-title" id="cwTitle">Messages</span>
                <a id="cwFullLink" href="{{ url($__cwBase) }}" title="Open full chat page"><i class="fas fa-expand"></i></a>
                <button id="cwMinimize" type="button" title="Minimize"><i class="fas fa-minus"></i></button>
            </div>

            <div class="cw-body" id="cwListView">
                <div id="cwList"><div class="cw-empty">Loading…</div></div>
            </div>

            <div class="cw-thread" id="cwThreadView" style="display:none;">
                <div class="cw-messages" id="cwMessages"></div>
                <form class="cw-form" id="cwForm">
                    <input type="file" id="cwFileInput" style="display:none;">
                    <button type="button" id="cwAttach" title="Attach"><i class="fas fa-paperclip"></i></button>
                    <input type="text" id="cwInput" placeholder="Type a message…" autocomplete="off">
                    <button type="submit" title="Send"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>

        <script>
            window.CW_CONFIG = {
                base: @json($__cwBase),
                fullUrl: @json(url($__cwBase)),
                authId: {{ (int) $__wUser->id }},
                csrf: @json(csrf_token()),
                conversationsUrl: @json(route('chat.conversations')),
            };
        </script>
        <script src="{{ asset('js/chat-widget.js') }}"></script>
    @endunless
@endauth
