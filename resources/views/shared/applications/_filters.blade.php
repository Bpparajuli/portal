@php $role = auth()->user()->is_admin_staff ? 'admin' : auth()->user()->role; @endphp
<form method="GET" action="{{ route($role . '.applications.index') }}" class="mb-4">
    <div class="app-filter-card shadow-sm">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Student, course, university..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status_filter" class="form-select">
                        <option value="">All</option>
                        @foreach($statuses ?? [] as $st)
                        <option value="{{ $st->id }}" {{ request('status_filter') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">University</label>
                    <select name="university_filter" class="form-select">
                        <option value="">All</option>
                        @foreach($universities ?? [] as $u)
                        <option value="{{ $u->id }}" {{ request('university_filter') == $u->id ? 'selected' : '' }}>{{ $u->short_name ?? $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user()->is_admin || auth()->user()->is_admin_staff)
                <div class="col-md-2">
                    <label class="form-label">Agent</label>
                    <select name="agent_filter" class="form-select">
                        <option value="">All</option>
                        @foreach($agents ?? [] as $a)
                        <option value="{{ $a->id }}" {{ request('agent_filter') == $a->id ? 'selected' : '' }}>{{ $a->business_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-1">
                    <button class="btn btn-success w-100" style="font-size:0.82rem;padding:7px 12px;border-radius:8px;"><i class="fas fa-search"></i></button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route($role . '.applications.index') }}" class="btn btn-outline-secondary w-100" style="font-size:0.82rem;padding:7px 12px;border-radius:8px;"><i class="fas fa-undo"></i></a>
                </div>
            </div>
        </div>
    </div>
</form>