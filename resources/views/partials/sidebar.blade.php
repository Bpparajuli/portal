{{-- Sidebar navigation for the application --}}
<div data-component="sidebar">
    <div class="sidebar">
        <ul class="list-group flex-column d-inline-block first-menu">
            @if ($user?->is_admin)
            <li class="list-group-item pl-3 py-2 {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fa fa-house" aria-hidden="true"><span class="ml-2 align-middle">Dashboard</span></i>
                </a>
            </li>
            <li class="list-group-item pl-3 py-2 {{ request()->is('student/index') ? 'active' : '' }}">
                <a href="{{ route('admin.students.index') }}">
                    <i class="fa fa-users" aria-hidden="true"><span class="ml-2 align-middle">Students</span></i>
                </a>
            </li>
            <li class="list-group-item pl-3 py-2 {{ request()->is('universities/index') ? 'active' : '' }}">
                <a href="{{ route('admin.universities.index') }}">
                    <i class="fa fa-calendar-check" aria-hidden="true"><span class="ml-2 align-middle">Universities</span></i>
                </a>
            </li>
            <li class="list-group-item pl-3 py-2 {{ request()->is('user/index') ? 'active' : '' }}">
                <a href="{{ route('admin.users.index') }}">
                    <i class="fa fa-sliders" aria-hidden="true"><span class="ml-2 align-middle">Users</span></i>
                </a>
            </li>
            @php
            $totalWaitingUsers = \App\Models\User::where('active', 0)->count();
            @endphp
            <li class="list-group-item pl-3 py-2 {{ request()->is('user/waiting') ? 'active' : '' }}">
                <a href="{{ route('admin.users.waiting') }}">
                    <i class="fa fa-question" aria-hidden="true">
                        @if($totalWaitingUsers > 0)
                        <span class="badge bg-danger rounded-pill ml-1">{{ $totalWaitingUsers }}</span>
                        @endif
                        <span class="ml-2 align-middle">Waiting Users</span>
                    </i>
                </a>
            </li>

            @elseif ($user?->is_agent)
            <li class="list-group-item pl-3 py-2 {{ request()->is('agent/dashboard') ? 'active' : '' }}">
                <a href="{{ route('agent.dashboard') }}">
                    <i class="fa fa-house" aria-hidden="true"><span class="ml-2 align-middle">Dashboard</span></i>
                </a>
            </li>
            <li class="list-group-item pl-3 py-2 {{ request()->is('student/index') ? 'active' : '' }}">
                <a href="{{ route('agent.students.index') }}">
                    <i class="fa fa-users" aria-hidden="true"><span class="ml-2 align-middle">Students</span></i>
                </a>
            </li>
            <li class="list-group-item pl-3 py-2 {{ request()->is('university/index') ? 'active' : '' }}">
                <a href="{{ route('agent.universities.index') }}">
                    <i class="fa fa-calendar-check" aria-hidden="true"><span class="ml-2 align-middle">Universities</span></i>
                </a>
            </li>
            <li class="list-group-item pl-3 py-2 {{ request()->is('user/list') ? 'active' : '' }}">
                <a href="{{ route('agent.students.index') }}">
                    <i class="fa fa-sliders" aria-hidden="true"><span class="ml-2 align-middle">Applied List</span></i>
                </a>
            </li>

            @else
            <li class="list-group-item pl-3 py-2 {{ request()->is('/') ? 'active' : '' }}">
                <a href="{{ url('/') }}">
                    <i class="fa fa-house" aria-hidden="true"><span class="ml-2 align-middle">Dashboard</span></i>
                </a>
            </li>
            <li class="list-group-item pl-3 py-2">
                <a href="https://ideaconsultancyservices.com/">
                    <i class="fa fa-globe" aria-hidden="true"><span class="ml-2 align-middle">Webpage</span></i>
                </a>
            </li>
            <li class="list-group-item pl-3 py-2 {{ request()->is('university/index') ? 'active' : '' }}">
                <a href="{{ route('guest.universities.index') }}">
                    <i class="fa fa-calendar-check" aria-hidden="true"><span class="ml-2 align-middle">Universities</span></i>
                </a>
            </li>
            <li class="list-group-item pl-3 py-2 {{ request()->is('contact/show') ? 'active' : '' }}">
                <a href="{{ route('auth.contact') }}">
                    <i class="fa fa-envelope" aria-hidden="true"><span class="ml-2 align-middle">Contact Us</span></i>
                </a>
            </li>
            <li class="list-group-item pl-3 py-2 {{ request()->is('login') ? 'active' : '' }}">
                <a href="{{ route('login') }}">
                    <i class="fa fa-sign-in" aria-hidden="true"><span class="ml-2 align-middle">Login</span></i>
                </a>
            </li>
            <li class="list-group-item pl-3 py-2 {{ request()->is('register') ? 'active' : '' }}">
                <a href="{{ route('register') }}">
                    <i class="fa fa-user-plus" aria-hidden="true"><span class="ml-2 align-middle">Register</span></i>
                </a>
            </li>
            @endif

            @if ($user)
            <li class="list-group-item pl-3 py-2">
                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="fa fa-sign-out" aria-hidden="true"><span class="ml-2 align-middle">Logout</span></i>
                    </a>
                </form>
            </li>
            @endif
        </ul>
    </div>
</div>
</nav>
