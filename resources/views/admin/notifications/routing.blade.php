@extends('layouts.admin')
@section('admin-content')
    <x-page-header title="Notification Routing"
        subtitle="Choose which staff receive each category. Admins always receive everything.">
        <x-slot:actions>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:12px;">
                <i class="fas fa-bell me-1"></i> Notifications
            </a>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="alert alert-success py-2" style="font-size:13px;">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.notifications.routing.update') }}">
        @csrf

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="font-size:12.5px;">
                        <thead style="background:#f9fafb;">
                            <tr>
                                <th style="min-width:180px;">Staff</th>
                                @foreach ($categories as $cat)
                                    <th class="text-center">{{ $cat }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="background:#f9fafb;">
                                <td class="fw-semibold"><i class="fas fa-user-shield me-1"></i>Admins</td>
                                @foreach ($categories as $cat)
                                    <td class="text-center text-success" style="font-size:11px;">All ✓</td>
                                @endforeach
                            </tr>

                            @forelse ($staff as $u)
                                <tr>
                                    <td>
                                        <div class="fw-semibold" style="font-size:12.5px;">{{ $u->name }}</div>
                                        <div class="text-muted" style="font-size:11px;">{{ $u->email }}</div>
                                    </td>
                                    @foreach ($categories as $cat)
                                        @php $c = $current[$cat][$u->id] ?? []; @endphp
                                        <td class="text-center">
                                            <label class="d-block" style="font-size:10px;cursor:pointer;white-space:nowrap;">
                                                <input type="checkbox"
                                                    name="staff[{{ $u->id }}][{{ $cat }}][inapp]" value="1"
                                                    {{ !empty($c['inapp']) ? 'checked' : '' }}> In-app
                                            </label>
                                            <label class="d-block" style="font-size:10px;cursor:pointer;white-space:nowrap;">
                                                <input type="checkbox"
                                                    name="staff[{{ $u->id }}][{{ $cat }}][email]" value="1"
                                                    {{ !empty($c['email']) ? 'checked' : '' }}> Email
                                            </label>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($categories) + 1 }}" class="text-center text-muted py-4">
                                        No admin staff found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary rounded-pill px-4" style="font-size:13px;">
                <i class="fas fa-save me-1"></i> Save Routing
            </button>
            <span class="text-muted" style="font-size:12px;">
                Admins always receive every category. Staff receive only what you tick here.
            </span>
        </div>
    </form>
@endsection
