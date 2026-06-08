<div class="chat-sidebar" id="chatSidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center justify-content-between">
            <h5><i class="fas fa-comments"></i>Chats <span id="totalUnreadBadge" class="badge bg-danger ms-1 d-none">0</span></h5>
            <button class="btn btn-sm d-lg-none p-0" onclick="toggleChatSidebar()" style="font-size:18px;color:var(--text-muted);border:none;background:none;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="sidebar-search">
            <i class="fas fa-search"></i>
            <input type="text" id="userSearch" placeholder="Search contacts..." oninput="filterUsers()">
        </div>
    </div>

    @if($showRoleFilter)
    <div class="chat-type-selector">
        <select id="chatTypeFilter" onchange="filterByType(this.value)">
            <option value="all">All Users</option>
            <option value="admin">Admins</option>
            <option value="agent">Agents</option>
            <option value="staff">Team Members</option>
        </select>
    </div>
    @endif

    <div class="user-list" id="userList">
        <div class="text-center mt-4 text-muted small">Loading...</div>
    </div>
</div>
