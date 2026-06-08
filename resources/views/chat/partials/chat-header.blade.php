<div class="chat-header">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm d-lg-none p-0" onclick="toggleChatSidebar()" style="font-size:16px;color:var(--secondary);border:none;background:none;">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="chat-avatar" id="headerAvatar"></div>
        <div class="chat-header-info">
            <h6 id="headerName">---</h6>
            <small id="headerStatus"></small>
            <div id="typingIndicator" class="typing-indicator d-none">typing...</div>
        </div>
    </div>
    <div class="dropdown">
        <button class="btn btn-sm p-0" data-bs-toggle="dropdown" style="font-size:18px;color:var(--text-muted);border:none;background:none;width:32px;height:32px;">
            <i class="fas fa-ellipsis-v"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><a class="dropdown-item text-danger small" href="javascript:void(0)" onclick="clearChat()"><i class="fas fa-trash-alt me-2"></i>Clear Conversation</a></li>
        </ul>
    </div>
</div>
