@extends('layouts.admin')
@section('admin-content')
<style>
    .app-grid { max-width: 1400px; margin: 0 auto; }
    .stat-card-app { padding: 1.25rem; border-radius: var(--radius-lg); display: flex; justify-content: space-between; align-items: center; transition: all var(--transition-bounce); text-decoration: none; border: 1px solid var(--border); background: var(--bg-card); }
    .stat-card-app:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .stat-card-app .num { font-size: 2rem; font-weight: 800; line-height: 1; }
    .stat-card-app .lbl { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem; }
    .avatar-wrap { width: 40px; height: 40px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; color: #fff; flex-shrink: 0; overflow: hidden; position: relative; background: var(--gradient-primary); }
    .avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .avatar-wrap .overlay-id { position: absolute; inset: 0; background: rgba(0,0,0,0.6); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; opacity: 0; transition: opacity var(--transition-fast); }
    .avatar-wrap:hover .overlay-id { opacity: 1; }
    .filter-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.25rem; }
    .app-row { transition: all var(--transition-fast); }
    .app-row:hover { background: var(--bg-hover); }
</style>

<div class="app-grid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-file-alt text-primary me-2"></i>Applications</h4>
            <p class="text-muted small mb-0">Manage all student applications to universities</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('admin.exports.index') }}"><i class="fas fa-download me-1"></i>Export</a>
            <a class="btn btn-primary" href="{{ route('admin.applications.create') }}"><i class="fas fa-plus me-1"></i>New Application</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="{{ route('admin.applications.index') }}" class="stat-card-app" style="{{ !request('status') ? 'border-color:var(--primary);box-shadow:var(--shadow-glow);' : '' }}">
                <div><div class="lbl" style="color:var(--text-muted);">Total</div><div class="num" style="color:var(--primary);">{{ $applications->total() }}</div></div>
                <i class="fas fa-file-alt fa-2x" style="color:var(--primary);opacity:0.4;"></i>
            </a>
        </div>
        @foreach($statuses as $st)
        <div class="col-md-3">
            <a href="{{ route('admin.applications.index', array_merge(request()->except('status'), request('status') == $st->id ? [] : ['status' => $st->id])) }}" class="stat-card-app" style="{{ request('status') == $st->id ? 'border-color:'.$st->bg_color.';box-shadow:0 0 20px rgba(0,0,0,0.08);' : '' }}">
                <div><div class="lbl" style="color:var(--text-muted);">{{ $st->name }}</div><div class="num" style="color:{{ $st->bg_color ?? 'var(--primary)' }};">{{ $st->applications_count }}</div></div>
                <div style="width:32px;height:32px;border-radius:8px;background:{{ $st->bg_color }}20;display:flex;align-items:center;justify-content:center;"><i class="fas fa-tag" style="color:{{ $st->bg_color }};font-size:14px;"></i></div>
            </a>
        </div>
        @endforeach
    </div>

    <div class="filter-card mb-4">
        <form method="GET" action="{{ route('admin.applications.index') }}" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Name, email..." onkeyup="if(this.value.length>2||!this.value)this.form.submit()">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Country</label>
                    <select name="country_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->name }}" {{ request('country_filter') == $country->name ? 'selected' : '' }}>{{ $country->name }} ({{ $country->count }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">University</label>
                    <select name="university_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach ($universities as $uni)
                            <option value="{{ $uni->id }}" {{ request('university_filter') == $uni->id ? 'selected' : '' }}>{{ $uni->name }} ({{ $uni->applications_count }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Agent</label>
                    <select name="agent_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}" {{ request('agent_filter') == $agent->id ? 'selected' : '' }}>{{ $agent->business_name ?? $agent->username }} ({{ $agent->students_count }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}" {{ request('status_filter') == $status->id ? 'selected' : '' }}>{{ $status->name }} ({{ $status->applications_count }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    @if (request()->hasAny(['search','status','status_filter','agent_filter','university_filter','country_filter']))
                        <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-outline-danger w-100">Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="card mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:50px;">ID</th>
                            <th>Student / Agent</th>
                            <th>Course</th>
                            <th>University</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                            <tr class="app-row">
                                <td class="fw-bold text-muted" style="font-size:0.8rem;">#{{ $app->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-wrap">
                                            @if ($app->student->students_photo && Storage::disk('public')->exists($app->student->students_photo))
                                                <img src="{{ Storage::url($app->student->students_photo) }}" alt="">
                                            @else
                                                {{ strtoupper(substr($app->student->first_name, 0, 1)) }}{{ strtoupper(substr($app->student->last_name, 0, 1)) }}
                                            @endif
                                            <div class="overlay-id">{{ $app->student->id }}</div>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.students.show', $app->student->id) }}" class="fw-semibold text-dark text-decoration-none">{{ $app->student->first_name }} {{ $app->student->last_name }}</a>
                                            <br>
                                            <small class="text-muted"><i class="fas fa-user-tie me-1"></i><a href="{{ route('admin.users.show', $app->agent->id) }}" class="text-muted">{{ $app->agent->business_name ?? $app->agent->username }}</a></small>
                                        </div>
                                    </div>
                                </td>
                                <td><div class="fw-semibold small">{{ Str::limit($app->course->title ?? 'N/A', 30) }}</div></td>
                                <td><span class="small">{{ Str::limit($app->university->name ?? 'N/A', 25) }}</span></td>
                                <td>
                                    <span class="badge" style="background:{{ $app->status?->bg_color ?? '#6c757d' }};color:#fff;font-size:11px;">
                                        {{ $app->status?->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $app->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-ghost" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('admin.applications.show', $app->id) }}"><i class="fas fa-eye text-info me-2"></i>View</a></li>
                                            <li><a class="dropdown-item" href="{{ route('admin.applications.edit', $app->id) }}"><i class="fas fa-edit text-warning me-2"></i>Edit</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="{{ route('admin.students.show', $app->student->id) }}"><i class="fas fa-user-graduate me-2"></i>Student Profile</a></li>
                                            <li><a class="dropdown-item" href="mailto:{{ $app->student->email }}"><i class="fas fa-envelope me-2"></i>Send Email</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button class="dropdown-item text-danger btn-delete" data-url="{{ route('admin.applications.destroy', $app->id) }}" data-name="Application #{{ $app->id }}"><i class="fas fa-trash-alt me-2"></i>Delete</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5"><i class="fas fa-inbox fa-3x text-muted" style="opacity:0.3;"></i><h5 class="text-muted mt-3">No applications found</h5><a href="{{ route('admin.applications.create') }}" class="btn btn-primary mt-2"><i class="fas fa-plus me-1"></i>Create Application</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($applications->hasPages())
            <div class="card-footer bg-white">{{ $applications->appends(request()->query())->links() }}</div>
        @endif
    </div>
</div>
@endsection
