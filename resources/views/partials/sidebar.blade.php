@php $user = auth()->user(); @endphp
<aside class="sidebar" id="appSidebar">
    <div class="sidebar-brand">
        @auth
            @if($user->business_logo)
                <img src="{{ Storage::url($user->business_logo) }}" alt="Logo">
            @else
                <div class="sidebar-user-avatar">{{ substr($user->business_name ?? 'IC', 0, 2) }}</div>
            @endif
            <span>{{ $user->business_name ?? 'Dashboard' }}</span>
        @else
            <img src="{{ asset('images/logo.png') }}" alt="Idea Consultancy">
            <span>Idea Consultancy</span>
        @endauth
    </div>

    <ul class="sidebar-menu">
        @auth
            @if($user->is_admin)
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->is('admin/students*') && !request()->is('admin/students/export*') ? 'active' : '' }}">
                        <i class="fas fa-user-graduate"></i><span>Students</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.universities.index') }}" class="nav-link {{ request()->is('admin/universities*') ? 'active' : '' }}">
                        <i class="fas fa-university"></i><span>Universities</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.courses.index') }}" class="nav-link {{ request()->is('admin/courses*') ? 'active' : '' }}">
                        <i class="fas fa-book-open"></i><span>Courses</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->is('admin/users*') && !request()->is('admin/users/waiting*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i><span>Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.users.waiting') }}" class="nav-link {{ request()->is('admin/users/waiting*') ? 'active' : '' }}">
                        <i class="fas fa-user-clock"></i><span>Waiting Users</span>
                        @php $waitingCount = \App\Models\User::where('active', 0)->count(); @endphp
                        @if($waitingCount > 0)
                            <span class="badge bg-danger rounded-pill ms-auto">{{ $waitingCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.applications.index') }}" class="nav-link {{ request()->is('admin/applications*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i><span>Applications</span>
                    </a>
                </li>
                <li class="nav-divider"><hr></li>
                <li class="nav-item">
                    <a href="{{ route('admin.emails.inbox') }}" class="nav-link {{ request()->is('admin/emails*') ? 'active' : '' }}">
                        <i class="fas fa-envelope"></i><span>Email</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.chat') }}" class="nav-link {{ request()->is('admin/chat*') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i><span>Chat</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.exports.index') }}" class="nav-link {{ request()->is('admin/exports*') ? 'active' : '' }}">
                        <i class="fas fa-download"></i><span>Export Data</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.revenues.index') }}" class="nav-link {{ request()->is('admin/revenues*') ? 'active' : '' }}">
                        <i class="fas fa-coins"></i><span>Revenue</span>
                    </a>
                </li>
                <li class="nav-divider"><hr></li>
                <li class="nav-item">
                    <a href="{{ route('admin.pages.index') }}" class="nav-link {{ request()->is('admin/pages*') ? 'active' : '' }}">
                        <i class="fas fa-file"></i><span>Page Editor</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i><span>Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.enquiries.index') }}" class="nav-link {{ request()->is('admin/enquiries*') ? 'active' : '' }}">
                        <i class="fas fa-question-circle"></i><span>Enquiries</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ request()->is('admin/testimonials*') ? 'active' : '' }}">
                        <i class="fas fa-star"></i><span>Testimonials</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.activities.index') }}" class="nav-link {{ request()->is('admin/activities*') ? 'active' : '' }}">
                        <i class="fas fa-history"></i><span>Activity Log</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.backup.index') }}" class="nav-link {{ request()->is('admin/backups*') ? 'active' : '' }}">
                        <i class="fas fa-shield-alt"></i><span>Backup</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.qr-code') }}" class="nav-link {{ request()->is('admin/qr-code*') ? 'active' : '' }}">
                        <i class="fas fa-qrcode"></i><span>QR Code</span>
                    </a>
                </li>
                <li class="nav-divider"><hr></li>
                <li class="nav-item">
                    <a href="{{ route('crm.dashboard') }}" class="nav-link {{ request()->is('crm*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i><span>CRM</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('ai.assistant') }}" class="nav-link {{ request()->is('ai*') ? 'active' : '' }}">
                        <i class="fas fa-robot"></i><span>AI Assistant</span>
                    </a>
                </li>

            @elseif($user->is_agent)
                <li class="nav-item">
                    <a href="{{ route('agent.dashboard') }}" class="nav-link {{ request()->is('agent/dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('agent.students.index') }}" class="nav-link {{ request()->is('agent/students*') ? 'active' : '' }}">
                        <i class="fas fa-user-graduate"></i><span>Students</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('agent.universities.index') }}" class="nav-link {{ request()->is('agent/universities*') ? 'active' : '' }}">
                        <i class="fas fa-university"></i><span>Universities</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('agent.applications.index') }}" class="nav-link {{ request()->is('agent/applications*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i><span>Applications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('agent.chat') }}" class="nav-link {{ request()->is('agent/chat*') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i><span>Chat</span>
                    </a>
                </li>

            @elseif($user->is_staff)
                <li class="nav-item">
                    <a href="{{ route('crm.dashboard') }}" class="nav-link {{ request()->is('crm*') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                    </a>
                </li>
            @endif
        @else
            <li class="nav-item">
                <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                    <i class="fas fa-home"></i><span>Home</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="https://ideaconsultancyservices.com/" class="nav-link" target="_blank">
                    <i class="fas fa-globe"></i><span>Website</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('guest.universities.index') }}" class="nav-link {{ request()->is('guest/universities*') ? 'active' : '' }}">
                    <i class="fas fa-university"></i><span>Universities</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('auth.register') }}" class="nav-link {{ request()->is('auth/register') ? 'active' : '' }}">
                    <i class="fas fa-user-plus"></i><span>Register</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('auth.login') }}" class="nav-link {{ request()->is('auth/login') ? 'active' : '' }}">
                    <i class="fas fa-sign-in-alt"></i><span>Login</span>
                </a>
            </li>
        @endauth
    </ul>

    @auth
    <div class="sidebar-user">
        @if($user->business_logo)
            <img src="{{ Storage::url($user->business_logo) }}" alt="" class="sidebar-user-avatar">
        @else
            <div class="sidebar-user-avatar">{{ substr($user->name ?? 'U', 0, 1) }}</div>
        @endif
        <div class="sidebar-user-info">
            <div class="sidebar-user-name">{{ $user->name ?? ($user->business_name ?? 'User') }}</div>
            <div class="sidebar-user-role">
                {{ $user->is_admin ? 'Admin' : ($user->is_agent ? 'Agent' : ($user->is_staff ? 'Team Member' : '')) }}
            </div>
        </div>
    </div>
    @endauth
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
