<div class="nav-section">Main</div>
<a href="{{ route('staff.dashboard') }}" class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
    <i class="fas fa-chart-pie"></i> <span>Dashboard</span>
</a>

<a href="{{ route('staff.users.show', Auth::user()->slug) }}" class="nav-link {{ request()->routeIs('staff.users.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> <span>My Profile</span>
</a>

<div class="nav-section">Management</div>
<a href="{{ route('staff.students.index') }}" class="nav-link {{ request()->routeIs('staff.students.*') ? 'active' : '' }}">
    <i class="fas fa-user-graduate"></i> <span>Students</span>
</a>
<a href="{{ route('staff.applications.index') }}" class="nav-link {{ request()->routeIs('staff.applications.*') ? 'active' : '' }}">
    <i class="fas fa-file-alt"></i> <span>Applications</span>
</a>
<a href="{{ route('staff.universities') }}" class="nav-link {{ request()->routeIs('staff.universities') ? 'active' : '' }}">
    <i class="fas fa-university"></i> <span>Universities</span>
</a>
<a href="{{ route('staff.courses') }}" class="nav-link {{ request()->routeIs('staff.courses') ? 'active' : '' }}">
    <i class="fas fa-book"></i> <span>Courses</span>
</a>

<div class="nav-section">Communication</div>
<a href="{{ route('staff.chat.index') }}" class="nav-link {{ request()->routeIs('staff.chat.*') ? 'active' : '' }}">
    <i class="fas fa-comments"></i> <span>Chat</span>
</a>
<a href="{{ route('staff.notifications.index') }}" class="nav-link {{ request()->routeIs('staff.notifications.*') ? 'active' : '' }}">
    <i class="fas fa-bell"></i> <span>Notifications</span>
</a>

<div class="nav-section">CRM</div>
<a href="{{ route('crm.dashboard') }}" class="nav-link">
    <i class="fas fa-tasks"></i> <span>CRM</span>
</a>

<div class="nav-section">AI Tools</div>
<a href="{{ route('ai.assistant') }}" class="nav-link {{ request()->routeIs('ai.*') ? 'active' : '' }}">
    <i class="fas fa-robot"></i> <span>AI Assistant</span>
</a>
