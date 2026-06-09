@php $role = auth()->user()->role; @endphp
<form method="GET" action="{{ route($role . '.applications.index') }}" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search student, course, university..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="status_filter" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                @foreach($statuses ?? [] as $st)
                <option value="{{ $st->id }}" {{ request('status_filter') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="university_filter" class="form-select form-select-sm">
                <option value="">All Universities</option>
                @foreach($universities ?? [] as $u)
                <option value="{{ $u->id }}" {{ request('university_filter') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        @if(auth()->user()->is_admin || auth()->user()->is_admin_staff)
        <div class="col-md-2">
            <select name="agent_filter" class="form-select form-select-sm">
                <option value="">All Agents</option>
                @foreach($agents ?? [] as $a)
                <option value="{{ $a->id }}" {{ request('agent_filter') == $a->id ? 'selected' : '' }}>{{ $a->business_name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-md-1">
            <button class="btn btn-sm btn-success w-100"><i class="fas fa-search"></i></button>
        </div>
        <div class="col-md-1">
            <a href="{{ route($role . '.applications.index') }}" class="btn btn-sm btn-outline-secondary w-100"><i class="fas fa-undo"></i></a>
        </div>
    </div>
</form>
