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
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Courses Table --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Course Title</th>
                        <th>Course Code</th>
                        <th>University</th>
                        <th>City</th>
                        <th>Country</th>
                        <th>Duration</th>
                        <th>Tuition Fee</th>
                        <th>Created At</th>
                        <th class="text-center" style="width: 180px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $index => $course)
                    <tr>
                        <td>{{ $courses->firstItem() + $index }}</td>
                        <td>{{ $course->title }}</td>
                        <td>{{ $course->course_code }}</td>
                        <td>{{ $course->university->name ?? 'N/A' }}</td>
                        <td>{{ $course->university->city ?? 'N/A' }}</td>
                        <td>{{ $course->university->country ?? 'N/A' }}</td>
                        <td>{{ $course->duration ?? 'N/A' }}</td>
                        <td>
                            @if($course->tuition_fee)
                            ${{ number_format($course->tuition_fee, 2) }}
                            @else
                            N/A
                            @endif
                        </td>
                        <td>{{ $course->created_at->format('Y-m-d') }}</td>
                        <td class="text-center">
                            {{-- View Button --}}
                            <a href="{{ route('admin.courses.show', $course->id) }}" class="btn btn-sm btn-info text-white">
                                <i class="fas fa-eye"></i>
                            </a>

                            {{-- Edit Button --}}
                            <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>

                            {{-- Delete Button (Admin ID = 1 only) --}}
                            @if(Auth::id() === 1)
                            <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
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
