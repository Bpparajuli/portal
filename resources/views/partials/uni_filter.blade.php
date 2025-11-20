<link rel="stylesheet" href="{{ asset('css/university.css') }}">

<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0">Find Your University</h5>
            @auth
            @if(auth()->user()->is_admin)
            <div>
                <a href="{{ route('admin.universities.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-university me-1"></i> + Add University
                </a>
                <a href="{{ route('admin.courses.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-book-open me-1"></i> + Add Course
                </a>
            </div>
            @endif
            @endauth
        </div>

        @php
        if (auth()->check()) {
        if (auth()->user()->is_admin) {
        $formAction = route('admin.universities.index');
        } elseif (auth()->user()->is_agent) {
        $formAction = route('agent.universities.index');
        } else {
        $formAction = route('guest.universities.index');
        }
        } else {
        $formAction = route('guest.universities.index');
        }
        @endphp

        <form method="GET" action="{{ $formAction }}">
            <div class="row g-3">
                {{-- Search --}}
                <div class="col-md-4">
                    <label for="search" class="form-label fw-semibold">Search</label>
                    <input type="text" id="search" name="search" class="form-control" placeholder="University or Course">
                </div>

                {{-- Country --}}
                <div class="col-md-2">
                    <label for="country" class="form-label fw-semibold">Country</label>
                    <select id="country" name="country" class="form-select" data-cities-url="{{ route('guest.get-cities', ':country') }}">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                        <option value="{{ $country }}">{{ $country }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- City --}}
                <div class="col-md-2">
                    <label for="city" class="form-label fw-semibold">City</label>
                    <select id="city" name="city" class="form-select" data-universities-url="{{ route('guest.get-universities', ':city') }}">
                        <option value="">All Cities</option>
                    </select>
                </div>

                {{-- University --}}
                <div class="col-md-2">
                    <label for="university_id" class="form-label fw-semibold">University</label>
                    <select id="university_id" name="university_id" class="form-select" data-courses-url="{{ route('guest.get-courses', ':universityId') }}">
                        <option value="">All Universities</option>
                    </select>
                </div>

                {{-- Course --}}
                <div class="col-md-2">
                    <label for="course_id" class="form-label fw-semibold">Course</label>
                    <select id="course_id" name="course_id" class="form-select">
                        <option value="">All Courses</option>
                    </select>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="mt-2 d-flex justify-content-end gap-2">
                <a href="{{ route('guest.universities.index') }}" class="btn btn-secondary">Clear</a>
                <button type="submit" class="btn btn-success">Find</button>
            </div>
        </form>
    </div>
</div>

{{-- jQuery and JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/filter.js') }}"></script>
