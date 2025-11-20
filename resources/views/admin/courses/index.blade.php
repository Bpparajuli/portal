@extends('layouts.admin')

@section('title', 'Courses')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Courses</h4>
        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Course
        </a>
    </div>

    {{-- Filter Form --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.courses.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search (Title / Code)</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="e.g. Business Management">
                </div>
                <div class="col-md-3">
                    <label class="form-label">City</label>
                    <input type="text" name="city" value="{{ request('city') }}" class="form-control" placeholder="e.g. London">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" value="{{ request('country') }}" class="form-control" placeholder="e.g. UK">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-undo"></i> Reset
                    </a> <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>

                </div>
            </form>
        </div>
    </div>

    {{-- Courses Table --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class=" table table-striped align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Course Title</th>
                        <th>University</th>
                        <th>City</th>
                        <th>Country</th>
                        <th>Duration</th>
                        <th>Tuition Fee</th>
                        <th class="text-center" style="width: 180px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $index => $course)
                    <tr>
                        <td>{{ $course->id}}</td>
                        <td>
                            <a href="{{ route('admin.courses.show', $course->id) }}">
                                {{ $course->title }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.universities.show', $course->university->id) }}" class="text-blue-600 hover:underline">
                                {{ $course->university->name ?? 'N/A' }}
                            </a>
                        </td>
                        <td>{{ $course->university->city ?? 'N/A' }}</td>
                        <td>{{ $course->university->country ?? 'N/A' }}</td>
                        <td>{{ $course->duration ?? 'N/A' }}</td>
                        <td> {{ $course->fee }}</td>
                        <td class="text-center">
                            {{-- View Button --}}
                            <a href="{{ route('admin.courses.show', $course->id) }}" class="btn btn-sm btn-primary text-white">
                                <i class="fas fa-eye"></i>
                            </a>

                            {{-- Edit Button --}}
                            <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-sm btn-dark text-white">
                                <i class="fas fa-edit"></i>
                            </a>

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="25" class="text-center text-muted">No courses found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-footer">
            {{ $courses->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
