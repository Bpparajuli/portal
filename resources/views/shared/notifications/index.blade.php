@php
    $__user = auth()->user();
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAgent ? 'layouts.agent' : ($__isStaff ? 'layouts.staff' : 'layouts.admin');
    $__section = $__isAgent ? 'agent-content' : ($__isStaff ? 'staff-content' : 'admin-content');
    $role = $__user->is_admin ? 'admin' : ($__user->is_agent ? 'agent' : 'staff');
@endphp

@extends($__layout)
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section($__section)

<style>
    .nc-wrap { max-width: 880px; margin: 0 auto; }
    .nc-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
    .nc-title { font-size: 18px; font-weight: 800; color: var(--text-color); display: flex; align-items: center; gap: 8px; }
    .nc-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
    .nc-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .nc-btn { display: inline-flex; align-items: center; gap: 6px; padding: 6px 11px; border-radius: 20px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-muted); font-size: 11.5px; font-weight: 600; cursor: pointer; transition: all .12s; text-decoration: none; }
    .nc-btn:hover { color: var(--primary); border-color: var(--primary); }
    .nc-btn.danger:hover { color: var(--danger); border-color: var(--danger); }

    .nc-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px; }
    .nc-tab { padding: 6px 12px; border-radius: 20px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-muted); font-size: 12px; font-weight: 600; cursor: pointer; }
    .nc-tab.active { background: var(--primary); border-color: var(--primary); color: #fff; }

    .nc-cats { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
    .nc-chip { padding: 4px 10px; border-radius: 20px; border: 1px dashed var(--border); background: transparent; color: var(--text-muted); font-size: 11px; font-weight: 600; cursor: pointer; }
    .nc-chip.active { background: var(--primary-soft, #ede5f8); border-color: var(--primary); color: var(--primary); border-style: solid; }

    .nc-list { display: flex; flex-direction: column; gap: 8px; }
    .nc-item { display: flex; gap: 12px; align-items: flex-start; padding: 12px 14px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; transition: box-shadow .12s, border-color .12s; }
    .nc-item:hover { box-shadow: var(--shadow-sm); }
    .nc-item.unread { border-left: 3px solid var(--primary); background: var(--primary-soft, #f7f4fd); }
    .nc-ic { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
    .nc-body { flex: 1; min-width: 0; }
    .nc-row1 { display: flex; align-items: center; gap: 7px; flex-wrap: wrap; }
    .nc-name { font-size: 13px; font-weight: 700; color: var(--text-color); }
    .nc-cat { font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 10px; background: var(--gray-100, #f1f5f9); color: var(--text-muted); }
    .nc-new { font-size: 9.5px; font-weight: 700; padding: 1px 6px; border-radius: 10px; background: var(--primary); color: #fff; }
    .nc-text { font-size: 12px; color: var(--text-muted); margin-top: 2px; line-height: 1.45; }
    .nc-meta { font-size: 10.5px; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; gap: 6px; }
    .nc-tools { display: flex; gap: 4px; flex-shrink: 0; }
    .nc-ibtn { width: 28px; height: 28px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; font-size: 11px; cursor: pointer; padding: 0; }
    .nc-ibtn:hover { color: var(--primary); border-color: var(--primary); }
    .nc-ibtn.danger:hover { color: var(--danger); border-color: var(--danger); }
    .nc-empty { text-align: center; padding: 50px 20px; color: var(--text-muted); }
    .nc-empty i { font-size: 42px; opacity: .25; }
    @media (max-width: 640px) {
        .nc-item { flex-wrap: wrap; }
        .nc-tools { width: 100%; justify-content: flex-end; margin-top: 4px; }
    }
</style>

<div class="container-fluid px-3 py-3">
    <div class="nc-wrap">
        @php
            $unreadNotifications = $notifications->whereNull('read_at')->count() ?? 0;
            $unreadMessages = isset($messages) ? $messages->whereNull('read_at')->count() : 0;
            $totalUnread = $unreadNotifications + $unreadMessages;
        @endphp

        <div class="nc-head">
            <div>
                <div class="nc-title"><i class="fas fa-bell" style="color: var(--primary);"></i> Notifications</div>
                <div class="nc-sub">{{ $totalUnread }} unread • {{ $notifications->total() + (isset($messages) ? $messages->total() : 0) }} total</div>
            </div>
            <div class="nc-actions">
                <button class="nc-btn" onclick="markAllRead()"><i class="fas fa-check-double"></i> Mark all read</button>
                <x-confirm-delete
                    url="{{ route($role . '.notifications.deleteAll') }}"
                    label="Clear all"
                    title="Clear all notifications?"
                    message="This will delete everything."
                    class="nc-btn danger"
                />
            </div>
        </div>

        <div class="nc-tabs">
            <button class="nc-tab active" onclick="switchTab('all', this)">All</button>
            <button class="nc-tab" onclick="switchTab('unread', this)">Unread</button>
            @if (isset($messages))
                <button class="nc-tab" onclick="switchTab('messages', this)">Messages</button>
            @endif
        </div>

        <div class="nc-list" id="notifList">
            @php
                $allItems = collect();

                foreach (($notifications ?? collect()) as $n) {
                    $data = $n->data ?? [];
                    $allItems->push([
                        'id' => $n->id,
                        'type' => 'notification',
                        'category' => $data['category'] ?? 'Other',
                        'is_unread' => is_null($n->read_at),
                        'icon' => match (true) {
                            str_contains($data['type'] ?? '', 'application') => ['fa-file-alt', 'var(--warning)', 'var(--accent-soft)'],
                            str_contains($data['type'] ?? '', 'student') => ['fa-user-graduate', 'var(--success)', 'var(--success-soft)'],
                            str_contains($data['type'] ?? '', 'document') => ['fa-folder', 'var(--info)', 'var(--info-soft)'],
                            str_contains($data['type'] ?? '', 'enquir') => ['fa-question-circle', 'var(--info)', 'var(--info-soft)'],
                            str_contains($data['type'] ?? '', 'task') => ['fa-tasks', 'var(--primary)', 'var(--primary-soft)'],
                            str_contains($data['type'] ?? '', 'payment') || str_contains($data['type'] ?? '', 'revenue') => ['fa-credit-card', 'var(--danger)', 'var(--danger-soft)'],
                            default => ['fa-bell', 'var(--primary)', 'var(--primary-soft)'],
                        },
                        'title' => $data['title'] ?? (isset($data['type']) && $data['type'] ? ucfirst(str_replace('_', ' ', $data['type'])) : 'System Update'),
                        'text' => $data['message'] ?? ($data['title'] ?? 'New notification'),
                        'time' => $n->created_at?->diffForHumans() ?? '',
                        'url' => $data['link'] ?? (!empty($data['application']['id']) ? route($role . '.applications.show', $data['application']['id']) : (!empty($data['student']['id']) ? route($role . '.students.show', $data['student']['id']) : (!empty($data['agent']['id']) && $role === 'admin' ? route('admin.users.show', $data['agent']['id']) : '#'))),
                        'created_at' => $n->created_at,
                    ]);
                }

                foreach (($messages ?? collect()) as $m) {
                    $data = $m->data ?? [];
                    $allItems->push([
                        'id' => $m->id,
                        'type' => 'message',
                        'category' => 'Messages',
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
                $categoryList = $allItems->where('type', 'notification')->pluck('category')->filter()->unique()->values();
            @endphp

            @if ($categoryList->count())
                <div class="nc-cats">
                    <button class="nc-chip active" onclick="switchCategory('', this)">All categories</button>
                    @foreach ($categoryList as $cat)
                        <button class="nc-chip" onclick="switchCategory('{{ $cat }}', this)">{{ $cat }}</button>
                    @endforeach
                </div>
            @endif

            @forelse ($allItems as $item)
                <div class="nc-item {{ $item['is_unread'] ? 'unread' : '' }}"
                    data-type="{{ $item['type'] }}" data-category="{{ $item['category'] ?? '' }}"
                    data-unread="{{ $item['is_unread'] ? '1' : '0' }}">
                    <div class="nc-ic" style="background:{{ $item['icon'][2] }};color:{{ $item['icon'][1] }};">
                        <i class="fas {{ $item['icon'][0] }}"></i>
                    </div>
                    <div class="nc-body">
                        <div class="nc-row1">
                            <span class="nc-name">{{ $item['title'] }}</span>
                            @if ($item['is_unread'])
                                <span class="nc-new">New</span>
                            @endif
                            @if (!empty($item['category']))
                                <span class="nc-cat">{{ $item['category'] }}</span>
                            @endif
                        </div>
                        <div class="nc-text">{!! Str::limit(strip_tags($item['text']), 140) !!}</div>
                        <div class="nc-meta"><i class="far fa-clock"></i>{{ $item['time'] }}</div>
                    </div>
                    <div class="nc-tools">
                        @if ($item['url'] && $item['url'] !== '#')
                            <form action="{{ route($role . '.notifications.markRead', $item['id']) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="redirect" value="{{ $item['url'] }}">
                                <button type="submit" class="nc-ibtn" title="View"><i class="fas fa-eye"></i></button>
                            </form>
                        @endif
                        <button class="nc-ibtn" title="{{ $item['is_unread'] ? 'Mark read' : 'Mark unread' }}"
                            onclick="toggleRead('{{ $item['id'] }}', {{ $item['is_unread'] ? 'true' : 'false' }})">
                            <i class="fas {{ $item['is_unread'] ? 'fa-envelope-open' : 'fa-envelope' }}"></i>
                        </button>
                        <x-confirm-delete
                            url="{{ route($role . '.notifications.delete', $item['id']) }}"
                            label=""
                            title="Delete notification?"
                            message="This cannot be undone."
                            class="nc-ibtn danger"
                        />
                    </div>
                </div>
            @empty
                <div class="nc-empty">
                    <i class="fas fa-bell-slash"></i>
                    <div class="mt-3 fw-bold">All caught up</div>
                    <div style="font-size: 12px;">No notifications yet.</div>
                </div>
            @endforelse
        </div>

        @if (isset($notifications) && $notifications->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $notifications->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    let activeType = 'all';
    let activeCategory = '';

    function applyFilter() {
        document.querySelectorAll('.nc-item').forEach(item => {
            let show = true;
            if (activeType === 'unread') show = item.dataset.unread === '1';
            else if (activeType === 'messages') show = item.dataset.type === 'message';
            else if (activeType === 'category') show = item.dataset.type === 'notification' && item.dataset.category === activeCategory;
            item.style.display = show ? 'flex' : 'none';
        });
    }

    function switchTab(tab, btn) {
        document.querySelectorAll('.nc-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        activeType = tab;
        activeCategory = '';

        const allCat = document.querySelector('.nc-chip');
        if (allCat) {
            document.querySelectorAll('.nc-chip').forEach(c => c.classList.remove('active'));
            allCat.classList.add('active');
        }
        applyFilter();
    }

    function switchCategory(cat, btn) {
        document.querySelectorAll('.nc-chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');

        activeType = cat === '' ? 'all' : 'category';
        activeCategory = cat;
        applyFilter();
    }

    async function markAllRead() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route($role . ".notifications.markAll") }}';
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
        document.body.appendChild(form);
        form.submit();
    }

    async function toggleRead(id, isUnread) {
        const url = isUnread
            ? '{{ route($role . ".notifications.markRead", "__ID__") }}'.replace('__ID__', id)
            : '{{ route($role . ".notifications.markUnread", "__ID__") }}'.replace('__ID__', id);
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endsection
