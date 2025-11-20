@extends('layouts.app')

@section('content')
<div class="student-page container">

    {{-- Filter Section --}}
    <div class="filter-card mb-4 p-3 rounded shadow-sm">
        <form method="GET" action="{{ route('admin.students.index') }}" class="filter-form">
            <div class="row g-3 align-items-end">

                {{-- Search --}}
                <div class="col-md-2">
                    <label for="search">Search by Name</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control">
                </div>

                {{-- Agent --}}
                <div class="col-md-2">
                    <label for="agent">Agent</label>
                    <select name="agent" class="form-select">
                        <option value="">All</option>
                        @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ request('agent') == $agent->id ? 'selected' : '' }}>
                            {{ $agent->business_name ?? $agent->username }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-md-2">
                    <label for="status">Application Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(\App\Models\Student::STATUS as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Sort By --}}
                <div class="col-md-2">
                    <label for="sort_by">Sort By</label>
                    <select name="sort_by" class="form-select">
                        @foreach(['created_at'=>'Created At','first_name'=>'First Name','email'=>'Email'] as $key=>$val)
                        <option value="{{ $key }}" {{ request('sort_by')==$key ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Order --}}
                <div class="col-md-2">
                    <label for="sort_order">Order</label>
                    <select name="sort_order" class="form-select">
                        <option value="ASC" {{ request('sort_order')=='ASC'?'selected':'' }}>Ascending</option>
                        <option value="DESC" {{ request('sort_order','DESC')=='DESC'?'selected':'' }}>Descending</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="col-md-2 mt-2 d-flex gap-2">
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Clear</a>
                    <button type="submit" class="btn btn-success">Search</button>
                </div>

            </div>
        </form>
    </div>

    {{-- Students Table --}}
    <div class="table-card rounded shadow-sm p-3 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5">All Students</h2>
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i> Add Student
            </a>
        </div>
        <div class="table-responsive">
            <table class=" table table-striped align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Email / Contact</th>
                        <th>Agent</th>
                        <th>Latest Application Status</th>
                        <th>No of Applications</th>
                        <th>Document Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    @php
                    // ----------------- Document Status -----------------
                    $predefinedDocuments = [
                    'passport','10th_certificate','10th_transcript','11th_transcript',
                    '12th_certificate','12th_transcript','cv','moi','lor','ielts_pte_language_certificate'
                    ];

                    $uploadedTypes = $student->documents->pluck('document_type')
                    ->map(fn($t) => strtolower($t))
                    ->toArray();

                    $allDocumentsUploaded = count(array_diff($predefinedDocuments, $uploadedTypes)) === 0;

                    $documentStatus = $allDocumentsUploaded
                    ? 'Completed'
                    : (count($uploadedTypes) == 0 ? 'Not Uploaded' : 'Incomplete');

                    $latestApplication = $student->applications->sortByDesc('created_at')->first();
                    @endphp

                    <tr>
                        <td>{{ $student->id }}</td>

                        {{-- Profile --}}
                        <td class="text-center">
                            @if ($student->students_photo && Storage::disk('public')->exists($student->students_photo))
                            <img src="{{ Storage::url($student->students_photo) }}" alt="Profile" class="rounded-circle border" style="width:50px; height:50px; object-fit:cover;">
                            @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center border" style="width:50px; height:50px;">
                                <i class="fa fa-user-circle text-white" style="font-size:24px;"></i>
                            </div>
                            @endif
                        </td>

                        {{-- Name --}}
                        <td>
                            <a href="{{ route('admin.students.show', $student->id) }}">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </a>
                        </td>

                        {{-- Email / Contact --}}
                        <td>
                            <div>{{ $student->email }}</div>
                            <div class="text-sm text-gray-500">{{ $student->phone_number }}</div>
                        </td>

                        {{-- Agent --}}
                        <td>{{ $student->agent?->business_name ?? $student->agent?->username ?? 'N/A' }}</td>

                        {{-- Application Status --}}
                        <td>
                            @if($latestApplication)
                            <a href="{{ route('admin.applications.show', $latestApplication->id) }}">
                                <span class="badge {{ $latestApplication->status_class }}">
                                    {{ $latestApplication->application_status }}
                                </span>
                            </a>
                            @else
                            <span class="badge bg-light text-muted">No Application</span>
                            @endif
                        </td>

                        {{-- No of Applications --}}
                        <td>
                            @if($student->applications->count() > 0)
                            <a href="{{ route('admin.students.applications', $student->id) }}">
                                {{ $student->applications->count() }}
                            </a>
                            @else
                            0
                            @endif
                        </td>

                        {{-- Document Status --}}
                        <td>
                            <a href="{{ route('admin.documents.index', $student->id) }}">
                                @if($documentStatus == 'Not Uploaded')
                                <div class="px-2 py-1 rounded text-xs bg-danger text-white">Not Uploaded</div>
                                @elseif($allDocumentsUploaded)
                                <div class="px-2 py-1 rounded text-xs bg-success text-white">Completed</div>
                                @else
                                <div class="px-2 py-1 rounded text-xs bg-warning text-dark">Incomplete</div>
                                @endif
                            </a>
                        </td>

                        {{-- Actions --}}
                        <td>
                            @if($allDocumentsUploaded && $latestApplication)
                            <a href="{{ route('admin.applications.show', $latestApplication->id) }}" class="p-2 btn btn-sm btn-light p-1">
                                <i class="fa-solid fa-tools me-1"></i> Update Status
                            </a>
                            @else
                            <a href="{{ route('admin.documents.index', $student->id) }}" class="p-2 btn btn-sm btn-outline-secondary mt-1">
                                <i class="fa-solid fa-folder-open me-1"></i> Upload Docs
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-gray-500">No students found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection
