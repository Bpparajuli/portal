@extends('layouts.app')

@php $role = auth()->user()->role; @endphp

@section('title', 'Notifications')

@section('content')
<style>
.notif-page { max-width: 900px; margin: 0 auto; }
.notif-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
}
.notif-stats { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
.notif-stat {
    flex: 1; background: var(--bg-card); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 1.25rem; text-align: center;
    transition: all var(--transition-fast);
}
.notif-stat:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.notif-stat .num { font-size: 2rem; font-weight: 800; line-height: 1; }
.notif-stat .label {
    font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.05em; color: var(--text-muted); margin-top: 0.35rem;
}
.notif-tabs {
    display: flex; gap: 0; margin-bottom: 1.5rem; background: var(--gray-100);
    border-radius: var(--radius-md); padding: 4px;
}
.notif-tab {
    flex: 1; padding: 0.65rem 1rem; text-align: center; border: none;
    background: transparent; border-radius: var(--radius-sm); font-weight: 600;
    font-size: 0.85rem; color: var(--text-muted); cursor: pointer;
    transition: all var(--transition-fast);
}
.notif-tab.active { background: var(--bg-card); box-shadow: var(--shadow-sm); color: var(--primary); }
.notif-tab:hover:not(.active) { color: var(--text-color); }
.notif-list { display: flex; flex-direction: column; gap: 2px; }
.notif-item {
    display: flex; gap: 1rem; padding: 1rem 1.25rem; background: var(--bg-card);
    border-radius: var(--radius-md); transition: all var(--transition-fast);
    cursor: pointer; border: 1px solid transparent; position: relative;
}
.notif-item:hover { border-color: var(--border); box-shadow: var(--shadow-sm); }
.notif-item.unread { background: var(--primary-soft); border-left: 3px solid var(--primary); }
.notif-icon {
    width: 42px; height: 42px; border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.notif-content { flex: 1; min-width: 0; }
.notif-title { font-weight: 600; font-size: 0.9rem; color: var(--text-color); margin-bottom: 0.2rem; }
.notif-text { font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; }
.notif-time {
    font-size: 0.72rem; color: var(--text-muted); white-space: nowrap; flex-shrink: 0;
}
.notif-actions { display: flex; gap: 0.5rem; margin-top: 0.5rem; }
.notif-badge {
    display: inline-flex; align-items: center; gap: 0.25rem;
    padding: 0.15rem 0.5rem; border-radius: var(--radius-full);
    font-size: 0.65rem; font-weight: 600;
}
@media (max-width: 768px) {
    .notif-stats { flex-direction: column; }
    .notif-item { flex-wrap: wrap; }
    .notif-time { width: 100%; text-align: left; margin-top: 0.25rem; }
}
</style>

<div class="container-fluid px-3 py-3">
    <div class="notif-page">
        <div class="notif-header">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="fas fa-bell text-primary me-2"></i>Notifications
                </h3>
                <p class="text-muted mb-0 small">Stay updated with the latest activity</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="markAllRead()">
                    <i class="fas fa-check-double me-1"></i>Mark All Read
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="clearAll()">
                    <i class="fas fa-trash-alt me-1"></i>Clear All
                </button>
            </div>
        </div>

        @if(isset($messages))
        <div class="notif-stats">
            <div class="notif-stat">
                <div class="num" style="color:var(--primary);">{{ $notifications->total() + $messages->total() }}</div>
                <div class="label">Total</div>
            </div>
            <div class="notif-stat">
                <div class="num" style="color:var(--warning);">
                    {{ $notifications->whereNull('read_at')->count() + $messages->whereNull('read_at')->count() }}
                </div>
                <div class="label">Unread</div>
            </div>
            <div class="notif-stat">
                <div class="num" style="color:var(--success);">{{ $notifications->total() }}</div>
                <div class="label">Notifications</div>
            </div>
            <div class="notif-stat">
                <div class="num" style="color:var(--info);">{{ $messages->total() }}</div>
                <div class="label">Messages</div>
            </div>
        </div>
        @endif

        @php
            $unreadNotifications = $notifications->whereNull('read_at')->count() ?? 0;
            $unreadMessages = isset($messages) ? $messages->whereNull('read_at')->count() : 0;
            $totalUnread = $unreadNotifications + $unreadMessages;
        @endphp

        <div class="notif-tabs">
            <button class="notif-tab active" onclick="switchTab('all', this)">
                All
                @if($totalUnread > 0)
                    <span class="badge bg-primary rounded-pill ms-1" style="font-size:0.65rem;">{{ $totalUnread }}</span>
                @endif
            </button>
            <button class="notif-tab" onclick="switchTab('notifications', this)">
                Notifications
                @if($unreadNotifications > 0)
                    <span class="badge bg-warning rounded-pill ms-1" style="font-size:0.65rem;">{{ $unreadNotifications }}</span>
                @endif
            </button>
            @if(isset($messages))
            <button class="notif-tab" onclick="switchTab('messages', this)">
                Messages
                @if($unreadMessages > 0)
                    <span class="badge bg-info rounded-pill ms-1" style="font-size:0.65rem;">{{ $unreadMessages }}</span>
                @endif
            </button>
            @endif
            <button class="notif-tab" onclick="switchTab('unread', this)">
                Unread
                @if($totalUnread > 0)
                    <span class="badge bg-danger rounded-pill ms-1" style="font-size:0.65rem;">{{ $totalUnread }}</span>
                @endif
            </button>
        </div>

        <div class="notif-list" id="notifList">
            @php
                $allItems = collect();

                $items = $notifications ?? collect();
                foreach ($items as $n) {
                    $data = $n->data ?? [];
                    $allItems->push([
                        'id' => $n->id,
                        'type' => 'notification',
                        'is_unread' => is_null($n->read_at),
                        'icon' => match(true) {
                            str_contains($data['type'] ?? '', 'application') => ['fa-file-alt', 'var(--warning)', 'var(--accent-soft)'],
                            str_contains($data['type'] ?? '', 'student') => ['fa-user-graduate', 'var(--success)', 'var(--success-soft)'],
                            str_contains($data['type'] ?? '', 'document') => ['fa-folder', 'var(--info)', 'var(--info-soft)'],
                            str_contains($data['type'] ?? '', 'payment') => ['fa-credit-card', 'var(--danger)', 'var(--danger-soft)'],
                            default => ['fa-bell', 'var(--primary)', 'var(--primary-soft)'],
                        },
                        'title' => $data['type'] ? ucfirst(str_replace('_', ' ', $data['type'])) : 'System Update',
                        'text' => $data['message'] ?? ($data['title'] ?? 'New notification'),
                        'time' => $n->created_at?->diffForHumans() ?? '',
                        'url' => $data['link'] ?? (!empty($data['application']['id']) ? route($role . '.applications.show', $data['application']['id']) : (!empty($data['student']['id']) ? route($role . '.students.show', $data['student']['id']) : (!empty($data['agent']['id']) && $role === 'admin' ? route('admin.users.show', $data['agent']['id']) : '#'))),
                        'created_at' => $n->created_at,
                    ]);
                }

                $msgs = $messages ?? collect();
                foreach ($msgs as $m) {
                    $data = $m->data ?? [];
                    $allItems->push([
                        'id' => $m->id,
                        'type' => 'message',
                        'is_unread' => is_null($m->read_at),
                        'icon' => ['fa-envelope', 'var(--info)', 'var(--info-soft)'],
                        'title' => $data['added_by']['name'] ?? ($data['sender_name'] ?? 'System Message'),
                        'text' => $data['message'] ?? 'New message',
                        'time' => $m->created_at?->diffForHumans() ?? '',
                        'url' => !empty($data['application']['id']) ? route($role . '.applications.show', $data['application']['id']) : '#',
                        'created_at' => $m->created_at,
                    ]);
                }

                $allItems = $allItems->sortByDesc('created_at');
            @endphp

            @forelse($allItems as $item)
            <div class="notif-item {{ $item['is_unread'] ? 'unread' : '' }}"
                 data-type="{{ $item['type'] }}" data-unread="{{ $item['is_unread'] ? '1' : '0' }}">
                <div class="notif-icon" style="background:{{ $item['icon'][2] }};color:{{ $item['icon'][1] }};">
                    <i class="fas {{ $item['icon'][0] }}"></i>
                </div>
                <div class="notif-content">
                    <div class="notif-title">
                        {{ $item['title'] }}
                        @if($item['is_unread'])
                            <span class="notif-badge bg-primary text-white">New</span>
                        @endif
                        <span class="notif-badge" style="background:var(--gray-100);color:var(--text-muted);">{{ $item['type'] }}</span>
                    </div>
                    <div class="notif-text">{!! Str::limit(strip_tags($item['text']), 120) !!}</div>
                    <div class="notif-actions">
                        @if($item['url'] && $item['url'] !== '#')
                            <form action="{{ route($role . '.notifications.markRead', $item['id']) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="redirect" value="{{ $item['url'] }}">
                                <button type="submit" class="btn btn-xs btn-primary">View</button>
                            </form>
                        @endif
                        <button class="btn btn-xs btn-ghost" onclick="toggleRead('{{ $item['id'] }}', {{ $item['is_unread'] ? 'true' : 'false' }})">
                            <i class="fas {{ $item['is_unread'] ? 'fa-envelope-open' : 'fa-envelope' }}"></i>
                        </button>
                        <button class="btn btn-xs btn-ghost text-danger" onclick="deleteNotif('{{ $item['id'] }}')">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="notif-time" title="{{ $item['created_at']?->format('M d, Y h:i A') ?? '' }}">
                    <i class="far fa-clock me-1"></i>{{ $item['time'] }}
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-4x text-muted" style="opacity:0.3;"></i>
                <h5 class="text-muted mt-3">All caught up!</h5>
                <p class="text-muted small">No new notifications</p>
            </div>
            @endforelse
        </div>

        @if(isset($notifications) && $notifications->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $notifications->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.notif-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.notif-item').forEach(item => {
        if (tab === 'all') { item.style.display = 'flex'; return; }
        if (tab === 'notifications') { item.style.display = item.dataset.type === 'notification' ? 'flex' : 'none'; return; }
        if (tab === 'messages') { item.style.display = item.dataset.type === 'message' ? 'flex' : 'none'; return; }
        if (tab === 'unread') { item.style.display = item.dataset.unread === '1' ? 'flex' : 'none'; return; }
    });
}

async function markAllRead() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route($role . ".notifications.markAll") }}';
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
    document.body.appendChild(form); form.submit();
}

async function toggleRead(id, isUnread) {
    const url = isUnread
        ? '{{ url("/") }}/' + '{{ $role }}' + '/notifications/' + id + '/read'
        : '{{ url("/") }}/' + '{{ $role }}' + '/notifications/' + id + '/unread';
    const form = document.createElement('form');
    form.method = 'POST'; form.action = url;
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
    document.body.appendChild(form); form.submit();
}

async function deleteNotif(id) {
    const result = await Swal.fire({
        title: 'Delete notification?', text: 'This cannot be undone.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545', confirmButtonText: 'Delete'
    });
    if (result.isConfirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url("/") }}/' + '{{ $role }}' + '/notifications/' + id;
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">';
        document.body.appendChild(form); form.submit();
    }
}

async function clearAll() {
    const result = await Swal.fire({
        title: 'Clear all notifications?', text: 'This will delete everything.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545', confirmButtonText: 'Clear All'
    });
    if (result.isConfirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route($role . ".notifications.deleteAll") }}';
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">';
        document.body.appendChild(form); form.submit();
    }
}
</script>
@endsection
