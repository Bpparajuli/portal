@php
$user = auth()->user();
@endphp

{{-- Main navigation header for the application --}}
<main class="p-2">
    <nav class="main-menu">
        <div class="row d-flex flex-wrap justify-content-between align-items-center p-2">
            <div class="col-md-1 d-flex align-items-center gap-3">
                <img src="https://ideaconsultancyservices.com/wp-content/uploads/2023/08/Untitled_design-removebg-preview.png" alt="Idea Consultancy" width="80%" />
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
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="{{ route('notifications.index') }}">
                            🔔 @if($notifications->count())
                            <span class="badge bg-danger">{{ $notifications->count() }}</span>
                            @endif
                        </a>


                    </li>
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
            <div class="row">
                <div class="text-center">
                    <ul class="mt-2 bg-primary nav-list">
                        @if ($user?->is_admin)
                        <li class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fa fa-house nav-icon"></i>
                                <p class="nav-text">Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('student/list') ? 'active' : '' }}">
                            <a href="{{ route('student.list') }}">
                                <i class="fa fa-users nav-icon"></i>
                                <p class="nav-text">Students</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('university/list') ? 'active' : '' }}">
                            <a href="{{ route('university.list') }}">
                                <i class="fa fa-calendar-check nav-icon"></i>
                                <p class="nav-text">Universities</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('user/list') ? 'active' : '' }}">
                            <a href="{{ route('user.list') }}">
                                <i class="fa fa-sliders nav-icon"></i>
                                <p class="nav-text">Users</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('user/waiting') ? 'active' : '' }}">
                            <a href="{{ route('user.waiting') }}">
                                <i class="fa fa-question nav-icon"></i>
                                <p class="nav-text">Waiting Users</p>
                            </a>
                        </li>
                        @elseif ($user?->is_agent)
                        <li class="nav-item {{ request()->is('agent/dashboard') ? 'active' : '' }}">
                            <a href="{{ route('agent.dashboard') }}">
                                <i class="fa fa-house nav-icon"></i>
                                <p class="nav-text">Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('student/list') ? 'active' : '' }}">
                            <a href="{{ route('student.list') }}">
                                <i class="fa fa-users nav-icon"></i>
                                <p class="nav-text">Students</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('university/list') ? 'active' : '' }}">
                            <a href="{{ route('university.list') }}">
                                <i class="fa fa-calendar-check nav-icon"></i>
                                <p class="nav-text">Universities</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('user/list') ? 'active' : '' }}">
                            <a href="{{ route('user.list') }}">
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
                        <li class="nav-item ">
                            <a href="https://ideaconsultancyservices.com/">
                                <i class="fa fa-globe nav-icon"></i>
                                <p class="nav-text"> Webpage</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('contact') ? 'active' : '' }}">
                            <a href="{{ route('contact') }}">
                                <i class="fa fa-envelope nav-icon"></i>
                                <p class="nav-text">Contact Us</p>
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
