@extends('layouts.admin')
@section('admin-content')
    <x-page-header title="{{ request('group') ? ucfirst(request('group')) . ' Settings' : 'System Settings' }}" subtitle="User authority, feature toggles, and core configuration">
        <x-slot:actions>
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSettingModal">
                <i class="fas fa-plus me-1"></i>Add Setting
            </button>
        </x-slot:actions>
    </x-page-header>

    <ul class="nav nav-pills gap-2 mb-4" role="tablist" style="background:#f8fafc;padding:6px;border-radius:12px;display:inline-flex;">
        @foreach ($modules as $tabKey => $module)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == $tabKey ? 'active' : '' }} py-2 px-3 rounded-pill"
                    onclick="window.location.href='{{ route('admin.settings.index', ['tab' => $tabKey]) }}'"
                    type="button" style="font-size:13px;font-weight:500;{{ $activeTab == $tabKey ? 'background:#1a0262;color:#fff;' : 'color:#64748b;' }}">
                    <i class="fas fa-{{ $module['icon'] }} me-1"></i>{{ $module['name'] }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @foreach ($modules as $tabKey => $module)
            <div class="tab-pane {{ $activeTab == $tabKey ? 'show active' : '' }}" id="tab-{{ $tabKey }}">
                @if ($tabKey === 'roles')
                    {{-- ═══════════════ ROLES & ACCESS ═══════════════ --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small">Users & Permissions</span>
                            <span class="badge bg-light text-muted">{{ $users->count() }} users</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size:var(--text-sm);">
                                <thead class="table-light small text-muted">
                                    <tr>
                                        <th class="ps-3">User</th>
                                        <th style="width:110px;">Role</th>
                                        <th style="width:90px;">Status</th>
                                        <th style="width:100px;">CRM Access</th>
                                        <th style="width:80px;">Plan</th>
                                        <th class="pe-3 text-end" style="width:80px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:28px;height:28px;">
                                                        <span class="fw-bold text-primary" style="font-size:11px;">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold d-block" style="line-height:1.2;">{{ $user->name }}</span>
                                                        <span class="text-muted" style="font-size:10px;">{{ $user->email }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <form method="POST" action="{{ route('admin.settings.role.update', $user) }}" class="d-inline">
                                                    @csrf @method('PUT')
                                                    <select name="role" class="form-select form-select-sm" style="font-size:11px;width:100px;" onchange="this.form.submit()">
                                                        @foreach ($roles as $role)
                                                            <option value="{{ $role }}" {{ $user->role === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <form method="POST" action="{{ route('admin.settings.user.toggle-status', $user) }}" class="d-inline">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="btn btn-sm border-0 p-0" onclick="return confirm('Toggle status for {{ $user->name }}?')">
                                                        <span class="badge {{ $user->active ? 'bg-success' : 'bg-secondary' }}" style="font-size:10px;">
                                                            {{ $user->active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <form method="POST" action="{{ route('admin.settings.user.toggle-crm', $user) }}" class="d-inline">
                                                    @csrf @method('PUT')
                                                    <div class="form-check form-switch mb-0">
                                                        <input type="checkbox" class="form-check-input" role="switch"
                                                            {{ $user->paid_crm ? 'checked' : '' }}
                                                            onchange="this.form.submit()">
                                                    </div>
                                                </form>
                                            </td>
                                            <td><span class="badge bg-light text-muted" style="font-size:10px;">{{ $user->subscription_plan ?? '—' }}</span></td>
                                            <td class="pe-3 text-end">
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-ghost py-0">
                                                    <i class="fas fa-edit text-muted" style="font-size:12px;"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    {{-- ═══════════════ SETTINGS FORM ═══════════════ --}}
                    @php $groupSettings = $settings[$tabKey] ?? collect(); @endphp
                    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="group" value="{{ $tabKey }}">
                        <div class="card" style="border:1px solid rgba(26,1,98,0.08);box-shadow:0 1px 3px rgba(26,1,98,0.04),0 4px 12px rgba(26,1,98,0.04),0 0 0 1px rgba(26,1,98,0.02);border-radius:12px;overflow:hidden;">
                            <div class="position-relative" style="height:4px;background:linear-gradient(90deg,#1a0262,#6366f1,#06b6d4,#22c55e);"></div>
                            <div class="card-header bg-white px-4 py-3 border-0 d-flex justify-content-between align-items-center">
                                <span class="fw-semibold" style="font-size:14px;color:#1a0262;">{{ $module['name'] }}</span>
                                <span class="badge rounded-pill" style="background:#f1f5f9;color:#64748b;font-size:10px;">{{ $groupSettings->count() }} settings</span>
                            </div>
                            @if ($groupSettings->count())
                                <div class="list-group list-group-flush">
                                    @foreach ($groupSettings as $setting)
                                        <div class="list-group-item px-4 py-3 d-flex align-items-center gap-3" style="border-color:#f1f5f9;">
                                            <div class="flex-grow-1 min-width-0">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <label class="fw-semibold mb-0" style="font-size:var(--text-sm);white-space:nowrap;">
                                                        {{ Str::title(str_replace(['_', '.'], ' ', $setting->key)) }}
                                                    </label>
                                                    @if ($setting->description)
                                                        <i class="fas fa-info-circle text-muted" title="{{ $setting->description }}" style="font-size:10px;cursor:help;"></i>
                                                    @endif
                                                </div>
                                                @if ($setting->type === 'boolean')
                                                    <div class="form-check form-switch mb-0">
                                                        <input type="checkbox" name="{{ $setting->key }}" class="form-check-input" value="1" role="switch"
                                                            {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                                    </div>
                                                @elseif($setting->type === 'color')
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <input type="color" name="{{ $setting->key }}" class="form-control form-control-color p-0 border-0" style="width:32px;height:32px;" value="{{ $setting->value ?? '#000000' }}">
                                                        <input type="text" class="form-control form-control-sm" style="width:80px;font-size:11px;" value="{{ $setting->value ?? '#000000' }}" oninput="this.previousElementSibling.value=this.value">
                                                    </div>
                                                @elseif($setting->type === 'image')
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img id="setPrev{{ $setting->id }}" src="{{ $setting->value ? Storage::url($setting->value) : '' }}" alt="" style="width:36px;height:36px;object-fit:cover;border-radius:4px;{{ $setting->value ? '' : 'display:none;' }}">
                                                        <input type="file" name="{{ $setting->key }}" class="form-control form-control-sm" style="max-width:200px;font-size:11px;" accept="image/*">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0" style="font-size:10px;" data-gallery-target="{{ $setting->key }}" data-gallery-preview="setPrev{{ $setting->id }}" data-gallery-hidden="setHidden{{ $setting->id }}"><i class="fas fa-images me-1"></i>Gallery</button>
                                                        <input type="hidden" name="{{ $setting->key }}_selected" id="setHidden{{ $setting->id }}" data-gallery-field="{{ $setting->key }}">
                                                    </div>
                                                @elseif($setting->type === 'text')
                                                    <textarea name="{{ $setting->key }}" class="form-control form-control-sm" rows="2" style="font-size:12px;">{{ $setting->value }}</textarea>
                                                @elseif($setting->type === 'number')
                                                    <input type="number" name="{{ $setting->key }}" class="form-control form-control-sm" style="width:120px;font-size:12px;" value="{{ $setting->value }}">
                                                @elseif(str_contains($setting->key, 'password'))
                                                    <input type="password" name="{{ $setting->key }}" class="form-control form-control-sm" style="max-width:300px;font-size:12px;" value="{{ $setting->value }}" placeholder="Leave empty to keep current">
                                                @else
                                                    <input type="text" name="{{ $setting->key }}" class="form-control form-control-sm" style="max-width:300px;font-size:12px;" value="{{ $setting->value }}">
                                                @endif
                                            </div>
                                            <x-confirm-delete action="admin.settings.destroy" :id="$setting->id" label="" icon="true" title="Delete Setting?" message="This will permanently delete this setting." mode="swal" class="btn btn-sm btn-ghost text-danger py-0" />
                                        </div>
                                    @endforeach
                                </div>
                                <div class="card-footer bg-white px-4 py-3 text-end" style="border-top:1px solid #f1f5f9;">
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4"><i class="fas fa-save me-1"></i>Save</button>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-muted small mb-2">No settings in this group yet.</p>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSettingModal">
                                        <i class="fas fa-plus me-1"></i>Add Setting
                                    </button>
                                </div>
                            @endif
                        </div>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
@endsection
