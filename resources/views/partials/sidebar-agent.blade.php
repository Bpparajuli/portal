<div class="nav-section">Main</div>
<a href="{{ route('agent.dashboard') }}" class="nav-link {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
    <i class="fas fa-chart-pie"></i> <span>Dashboard</span>
</a>

<div class="nav-section">Management</div>
<a href="{{ route('agent.students.index') }}" class="nav-link {{ request()->routeIs('agent.students.*') ? 'active' : '' }}">
    <i class="fas fa-user-graduate"></i> <span>Students</span>
</a>
<a href="{{ route('agent.applications.index') }}" class="nav-link {{ request()->routeIs('agent.applications.*') ? 'active' : '' }}">
    <i class="fas fa-file-alt"></i> <span>Applications</span>
</a>
<a href="{{ route('agent.universities.index') }}" class="nav-link {{ request()->routeIs('agent.universities.*') ? 'active' : '' }}">
    <i class="fas fa-university"></i> <span>Universities</span>
</a>
<a href="{{ route('agent.courses.index') }}" class="nav-link {{ request()->routeIs('agent.courses.*') ? 'active' : '' }}">
    <i class="fas fa-book"></i> <span>Courses</span>
</a>

<div class="nav-section">Communication</div>
<a href="{{ route('agent.chat') }}" class="nav-link {{ request()->routeIs('agent.chat') ? 'active' : '' }}">
    <i class="fas fa-comments"></i> <span>Chat</span>
</a>
<a href="{{ route('agent.notifications') }}" class="nav-link {{ request()->routeIs('agent.notifications') ? 'active' : '' }}">
    <i class="fas fa-bell"></i> <span>Notifications</span>
</a>

<div class="nav-section">Team Members & Profile</div>
<a href="{{ route('agent.users.show', Auth::user()->slug) }}" class="nav-link {{ request()->routeIs('agent.users.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> <span>My Profile</span>
</a>
<a href="{{ route('crm.dashboard') }}" class="nav-link">
    <i class="fas fa-tasks"></i> <span>CRM</span>
</a>

<div class="nav-section">AI Tools</div>
<a href="{{ route('ai.assistant') }}" class="nav-link {{ request()->routeIs('ai.*') ? 'active' : '' }}">
    <i class="fas fa-robot"></i> <span>AI Assistant</span>
</a>
