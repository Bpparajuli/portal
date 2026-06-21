<div class="nav-section">Main</div>
<a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="fas fa-chart-pie"></i> <span>Dashboard</span>
</a>

<div class="nav-section">Management</div>
<a href="{{ route('admin.students.index') }}"
    class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
    <i class="fas fa-user-graduate"></i> <span>Students</span>
</a>
<a href="{{ route('admin.applications.index') }}"
    class="nav-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
    <i class="fas fa-file-alt"></i> <span>Applications</span>
</a>
<a href="{{ route('admin.application-status.index') }}"
    class="nav-link {{ request()->routeIs('admin.application-status.*') ? 'active' : '' }}">
    <i class="fas fa-tags"></i> <span>Application Status</span>
</a>
<a href="{{ route('admin.universities.index') }}"
    class="nav-link {{ request()->routeIs('admin.universities.*') ? 'active' : '' }}">
    <i class="fas fa-university"></i> <span>Universities</span>
</a>
<a href="{{ route('admin.courses.index') }}"
    class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
    <i class="fas fa-book"></i> <span>Courses</span>
</a>
<a href="{{ route('admin.testimonials.index') }}"
    class="nav-link {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}">
    <i class="fas fa-star"></i><span>Testimonials</span>
</a>
<a href="{{ route('admin.exports.index') }}"
    class="nav-link {{ request()->routeIs('admin.exports.*') ? 'active' : '' }}">
    <i class="fas fa-download"></i> <span>Export Data</span>
</a>
<a href="{{ route('admin.revenues.index') }}"
    class="nav-link {{ request()->routeIs('admin.revenues.*') ? 'active' : '' }}">
    <i class="fas fa-coins"></i> <span>Revenue</span>
</a>

<div class="nav-section">Communication</div>
<a href="{{ route('admin.emails.inbox') }}"
    class="nav-link {{ request()->routeIs('admin.emails.*') ? 'active' : '' }}">
    <i class="fas fa-envelope"></i> <span>Emails</span>
</a>
<a href="{{ route('admin.chat') }}" class="nav-link {{ request()->routeIs('admin.chat') ? 'active' : '' }}">
    <i class="fas fa-comments"></i> <span>Chat</span>
</a>
<a href="{{ route('admin.notifications.index') }}"
    class="nav-link {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
    <i class="fas fa-bell"></i> <span>Notifications</span>
</a>
<a href="{{ route('admin.enquiries.index') }}"
    class="nav-link {{ request()->routeIs('admin.enquiries.*') ? 'active' : '' }}">
    <i class="fas fa-question-circle"></i> <span>Enquiries</span>
</a>

<div class="nav-section">Settings</div>
<a href="{{ route('admin.content') }}" class="nav-link {{ request()->routeIs('admin.content') ? 'active' : '' }}">
    <i class="fas fa-edit"></i> <span>Content Manager</span>
</a>
<a href="{{ route('admin.settings.index', ['group' => 'global']) }}" class="nav-link {{ request()->routeIs('admin.settings.*') && request('group') === 'global' ? 'active' : '' }}">
    <i class="fas fa-globe"></i> <span>Global</span>
</a>
<a href="{{ route('admin.settings.index', ['group' => 'appearance']) }}" class="nav-link {{ request()->routeIs('admin.settings.*') && request('group') === 'appearance' ? 'active' : '' }}">
    <i class="fas fa-palette"></i> <span>Appearance</span>
</a>

<div class="nav-section">System</div>
<a href="{{ route('admin.activities.index') }}"
    class="nav-link {{ request()->routeIs('admin.activities.*') ? 'active' : '' }}">
    <i class="fas fa-history"></i> <span>Activity Log</span>
</a>
<a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
    <i class="fas fa-users-cog"></i> <span>Users &amp; Permissions</span>
</a>
<a href="{{ route('admin.users.waiting') }}"
    class="nav-link {{ request()->routeIs('admin.users.waiting') ? 'active' : '' }}">
    <i class="fas fa-clock"></i> <span>Waiting List</span>
</a>
<a href="{{ route('admin.trash.index') }}"
    class="nav-link {{ request()->routeIs('admin.trash.*') ? 'active' : '' }}">
    <i class="fas fa-trash-alt"></i> <span>Recycle Bin</span>
</a>
<a href="{{ route('admin.backup.index') }}"
    class="nav-link {{ request()->routeIs('admin.backup.*') ? 'active' : '' }}">
    <i class="fas fa-shield-alt"></i> <span>Backup</span>
</a>
<a href="{{ route('crm.dashboard') }}" class="nav-link">
    <i class="fas fa-tasks"></i> <span>CRM</span>
</a>
