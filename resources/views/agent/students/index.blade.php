@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/student-index.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

@section('content')
<div class="student-page container">

    {{-- Filter Section --}}
    <div class="filter-card mb-4 p-2 rounded shadow-sm">
        <form method="GET" action="{{ route('agent.students.index') }}" class="filter-form">
            <div class="row g-3 align-items-end">
                {{-- Search --}}
                <div class="col-md-2">
                    <label for="search">Search by Name</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control">
                </div>

                {{-- Country --}}
                <div class="col-md-2">
                    <label for="country">Country</label>
                    <select name="country" class="form-select">
                        <option value="">All</option>
                        @foreach($countries as $country)
                        <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                            {{ $country }}
                        </option>
                        @endforeach
                    </select>
                </div>
                {{-- university --}}
                <div class="col-md-2">
                    <label for="university">University</label>
                    <select name="university" id="university" class="form-select">
                        <option value="">All</option>
                        @foreach($universities as $university)
                        <option value="{{ $university->id }}" {{ request('university') == $university->id ? 'selected' : '' }}>
                            {{ $university->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Application Status --}}
                <div class="col-md-2">
                    <label for="application_status">Application Status</label>
                    <select name="application_status" class="form-select">
                        <option value="">All</option>
                        @foreach(\App\Models\Application::STATUSES as $status)
                        <option value="{{ $status }}" {{ request('application_status') == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Document Status --}}
                <div class="col-md-2">
                    <label for="document_status">Document Status</label>
                    <select name="document_status" class="form-select">
                        <option value="">All</option>
                        <option value="Incomplete" {{ request('document_status') == 'Incomplete' ? 'selected' : '' }}>Incomplete</option>
                        <option value="Completed" {{ request('document_status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Not Uploaded" {{ request('document_status') == 'Not Uploaded' ? 'selected' : '' }}>Not Uploaded</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="col-md-2 mt-2 d-flex gap-2">
                    <a href="{{ route('agent.students.index') }}" class="btn btn-secondary">Clear</a>
                    <button type="submit" class="btn btn-success">Find</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Students Table --}}
    <div class="table-card rounded shadow-sm p-3 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5">My Students</h2>
            <a href="{{ route('agent.students.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i> Add Student
            </a>
        </div>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>Email / Contact</th>
                    <th>Application Status</th>
                    <th>No of Applications</th>
                    <th>Preferred Country</th>
                    <th>Qualification</th>
                    <th>Document Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                @php
                // Document status calculation
                $requiredDocumentTypes = ['passport','id','transcript','financial','other'];
                $uploadedTypes = $student->documents->pluck('document_type')
                ->map(fn($t) => strtolower(str_replace(' ', '', $t)))
                ->toArray();
                $allDocumentsUploaded = count(array_diff($requiredDocumentTypes, $uploadedTypes)) === 0;
                $completedDocsCount = $student->documents->where('status','completed')->count();

                $documentStatus = ($allDocumentsUploaded && $completedDocsCount == count($requiredDocumentTypes))
                ? 'Completed'
                : (count($uploadedTypes) == 0 ? 'Not Uploaded' : 'Incomplete');

                // Application status
                $latestApplication = $student->applications->sortByDesc('created_at')->first();
                @endphp

                <tr>
                    <td>{{ $student->id }}</td>
                    {{-- Profile --}}
                    <td class="text-center">
                        <a href="{{ route('agent.students.show', $student->id) }}">
                            @if ($student->students_photo && Storage::disk('public')->exists($student->students_photo))
                            <img src="{{ Storage::url($student->students_photo) }}" alt="Profile" class="rounded-circle border" style="width:50px; height:50px; object-fit:cover;">
                            @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center border" style="width:50px; height:50px;">
                                <i class="fa fa-user-circle text-white" style="font-size:24px;"></i>
                            </div>
                            @endif
                        </a>
                    </td>


                    {{-- Name --}}
                    <td>
                        <a href="{{ route('agent.students.show', $student->id) }}">
                            {{ $student->first_name }} {{ $student->last_name }}
                        </a>
                    </td>

                    {{-- Email / Contact --}}
                    <td>
                        <div>{{ $student->email }}</div>
                        <div class="text-sm text-gray-500">{{ $student->phone_number }}</div>
                    </td>

                    {{-- Application Status --}}
                    <td>
                        @if($latestApplication)
                        <a href="{{ route('agent.applications.show', $latestApplication->id) }}">
                            @else
                            <a href="#">
                                @endif
                                <div class="px-2 py-1 rounded text-xs">
                                    @if($latestApplication)
                                    <span class="badge {{ $latestApplication->status_class }}">
                                        {{ $latestApplication->application_status }}
                                    </span>
                                    @else
                                    <span class="badge bg-light text-muted">No Application</span>
                                    @endif
                                </div>
                            </a>

                    </td>


                    {{-- No of Applications --}}
                    <td>
                        <a href="{{ route('agent.students.show', $student->id) }}#application">
                            {{ $student->applications->count() }}
                        </a>
                    </td>

                    {{-- Preferred Country --}}
                    <td>{{ $student->preferred_country ?? 'N/A' }}</td>

                    {{-- Qualification --}}
                    <td>{{ $student->qualification ?? 'N/A' }}</td>

                    {{-- Document Status --}}
                    <td>
                        <a href="{{ route('agent.documents.index', $student->id) }}">
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
                    <td class="d-flex flex-column gap-1">
                        @if($allDocumentsUploaded)
                        <a href="{{ route('agent.applications.create') }}?student_id={{ $student->id }}" class="btn btn-sm btn-success mt-1">
                            <i class="fa-solid fa-paper-plane me-1"></i> Apply Now
                        </a>
                        @else
                        <a href="{{ route('agent.documents.index', $student->id) }}" class="btn btn-sm btn-outline-secondary mt-1">
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

        <div class="mt-3">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection
