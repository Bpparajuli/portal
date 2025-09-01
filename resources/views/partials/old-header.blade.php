@php
$user = auth()->user();
@endphp
<main>
    {{-- Main navigation header for the application --}}
    <nav class="main-menu">
        <div class="row d-flex flex-wrap justify-content-between align-items-center p-2">
            <div class="col-md-1 d-flex align-items-center gap-3">
                <img src="https://ideaconsultancyservices.com/wp-content/uploads/2023/10/Logos.png" alt="Idea Consultancy" width="80%" />
            </div>
            <div class="col-md-8 justify-content-center">
                <div class="d-flex justify-content-center align-items-center">
                    <h1 class="text-secondary ">
                        @if ($user?->is_admin)
                        Admin Portal<br> Idea Consultancy
                        @elseif ($user?->is_agent)
                        Agent Portal <br>Idea Consultancy
                        @else
                        Idea Consultancy
                        @endif
                    </h1>
                </div>
            </div>
            <div class="col-md-3 justify-content-between">
                <div class="notification d-flex align-item-center justify-content-between gap-2">
                    <!-- Inside your navbar layout for Notification  -->
                    @auth
                    @php
                    $notifications = auth()->user()->unreadNotifications;
                    @endphp
                    @if ($user?->is_admin)
                    <li class="notification-dropdown">
                        <a class="notification-toggle" href="#" data-dropdown-toggle="admin-notif-menu">
                            🔔
                            @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="notification-badge">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                            @endif
                        </a>
                        <ul id="admin-notif-menu" class="notification-menu">
                            @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                            <li>
                                <a class="notification-item" href="{{ $notification->data['link'] ?? '#' }}">
                                    {{ $notification->data['message'] ?? 'New Notification' }}
                                    <small class="notification-timestamp">{{ $notification->created_at->diffForHumans() }}</small>
                                </a>
                            </li>
                            @empty
                            <li><span class="notification-item-text">No new notifications</span></li>
                            @endforelse
                            <li>
                                <hr class="notification-divider">
                            </li>
                            <li>
                                <a class="notification-item notification-center-text" href="{{ route('admin.notifications') }}">
                                    View All Notifications
                                </a>
                            </li>
                        </ul>
                    </li>
                    @elseif ($user?->is_agent)
                    <li class="notification-dropdown">
                        <a class="notification-toggle" href="#" data-dropdown-toggle="agent-notif-menu">
                            🔔
                            @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="notification-badge">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                            @endif
                        </a>
                        <ul id="agent-notif-menu" class="notification-menu">
                            @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                            <li>
                                <a class="notification-item" href="#">
                                    {{ $notification->data['message'] ?? 'New Notification' }}
                                    <small class="notification-timestamp">{{ $notification->created_at->diffForHumans() }}</small>
                                </a>
                            </li>
                            @empty
                            <li><span class="notification-item-text">No new notifications</span></li>
                            @endforelse
                            <li>
                                <hr class="notification-divider">
                            </li>
                            <li>
                                <a class="notification-item notification-center-text" href="{{ route('agent.notifications') }}">
                                    View All Notifications
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                    @endauth

                    @if ($user)
                    </a>
                    <div class="welcome text-primary">
                        Welcome,<br> <strong>{{ $user->name }}</strong>!
                    </div>
                    <div class="user-avatar">
                        <img style="width:60px; border-radius:20%;" src="{{ asset('images/Agents_logo/' . $user->business_logo) }}" alt="user" />
                    </div>
                    @endif
                </div>
            </div>
            <div class="row-nav">
                <div class="text-center">
                    <ul class="mt-2 bg-primary nav-list">
                        @if ($user?->is_admin)
                        <li class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fa fa-house nav-icon"></i>
                                <p class="nav-text">Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('student/index') ? 'active' : '' }}">
                            <a href="{{ route('admin.students.index') }}">
                                <i class="fa fa-users nav-icon"></i>
                                <p class="nav-text">Students</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('universities/index') ? 'active' : '' }}">
                            <a href="{{ route('admin.universities.index') }}">
                                <i class="fa fa-calendar-check nav-icon"></i>
                                <p class="nav-text">Universities</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('user/index') ? 'active' : '' }}">
                            <a href="{{ route('admin.users.index') }}">
                                <i class="fa fa-sliders nav-icon"></i>
                                <p class="nav-text">Users</p>
                            </a>
                        </li>
                        @php
                        $totalWaitingUsers = \App\Models\User::where('active', 0)->count();
                        @endphp
                        <li class="nav-item {{ request()->is('user/waiting') ? 'active' : '' }}">
                            <a href="{{ route('admin.users.waiting') }}" class="d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fa fa-question nav-icon"> @if($totalWaitingUsers > 0)
                                        <span class="badge bg-danger rounded-pill ms-2">{{$totalWaitingUsers}}</span>
                                        @endif</i>
                                    <p class="nav-text">Waiting Users</p>
                                </div>
                            </a>
                        </li>
                        @elseif ($user?->is_agent)
                        <li class="nav-item {{ request()->is('agent/dashboard') ? 'active' : '' }}">
                            <a href="{{ route('agent.dashboard') }}">
                                <i class="fa fa-house nav-icon"></i>
                                <p class="nav-text">Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('student/index') ? 'active' : '' }}">
                            <a href="{{ route('agent.students.index') }}">
                                <i class="fa fa-users nav-icon"></i>
                                <p class="nav-text">Students</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('university/index') ? 'active' : '' }}">
                            <a href="{{ route('agent.universities.index') }}">
                                <i class="fa fa-calendar-check nav-icon"></i>
                                <p class="nav-text">Universities</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('user/list') ? 'active' : '' }}">
                            <a href="{{ route('agent.students.index') }}">
                                <i class="fa fa-sliders nav-icon"></i>
                                <p class="nav-text">Applied List</p>
                            </a>
                        </li>
                        @else
                        {{-- guest-style simpler navbar --}}
                        <li class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                            <a href="{{ url('/') }}">
                                <i class="fa fa-house nav-icon"></i>
                                <p class="nav-text">Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a href="https://ideaconsultancyservices.com/">
                                <i class="fa fa-globe nav-icon"></i>
                                <p class="nav-text"> Webpage</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('guest/universities') ? 'active' : '' }}">
                            <a href="{{ route('guest.universities.index') }}">
                                <i class="fa fa-calendar-check nav-icon"></i>
                                <p class="nav-text">Universities</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('contact/show') ? 'active' : '' }}">
                            <a href="{{ route('auth.contact') }}">
                                <i class="fa fa-envelope nav-icon"></i>
                                <p class="nav-text">Contact Us</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('login') ? 'active' : '' }}">
                            <a href="{{ route('login') }}">
                                <i class="fa fa-sign-in nav-icon"></i>
                                <p class="nav-text">Login</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('register') ? 'active' : '' }}">
                            <a href="{{ route('register') }}">
                                <i class="fa fa-user-plus nav-icon"></i>
                                <p class="nav-text">Register</p>
                            </a>
                        </li>
                        @endif

                        {{-- universal logout if authenticated --}}
                        @if ($user)
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                                @csrf
                                <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <p class="nav-text">Logout</p>
                                </a>
                            </form>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        </div>
    </nav>
