<link rel="stylesheet" href="{{ asset('css/university.css') }}">

<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0">Find Your University</h5>

            @auth
            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
            <div>
                <a href="{{ route('admin.universities.create') }}" class="btn btn-success btn-sm">+ Add University</a>
                <a href="{{ route('admin.courses.create') }}" class="btn btn-success btn-sm">+ Add Course</a>
            </div>
            @endif
            @endauth
        </div>

        @php
        // Determine role-specific routes
        if (auth()->check()) {
        $role = auth()->user()->role;
        if (in_array($role, ['admin', 'superadmin'])) {
        $prefix = 'admin';
        $formAction = route('admin.universities.index');
        } elseif ($role === 'agent') {
        $prefix = 'agent';
        $formAction = route('agent.universities.index');
        } else {
        $prefix = 'guest';
        $formAction = route('guest.universities.index');
        }
        } else {
        $prefix = 'guest';
        $formAction = route('guest.universities.index');
        }

        // URLs for AJAX
        $cityRoute = route($prefix.'.get-cities', ':country');
        $uniRoute = route($prefix.'.get-universities', ':city');
        $typeRoute = route($prefix.'.get-course-types', ':universityId');
        $courseRoute = route($prefix.'.get-courses-by-type', ['universityId'=>':universityId','type'=>':type']);
        @endphp

        <form method="GET" action="{{ $formAction }}">
            <div class="row my-2 g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}">
                </div>
                <div class="col-md-9">
                    <div class="row g-3">

                        {{-- Country --}}
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Country</label>
                            <select id="country" class="form-select" name="country" data-cities-url="{{ $cityRoute }}">
                                <option value="">All</option>
                                @foreach($countries as $country)
                                <option value="{{ $country }}" @selected(request('country')==$country)>{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- City --}}
                        <div class="col-md-2 ">
                            <label class="form-label fw-semibold">City</label>
                            <select id="city" class="form-select" name="city" data-universities-url="{{ $uniRoute }}">
                                <option value="">All</option>
                            </select>
                        </div>

                        {{-- University --}}
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">University</label>
                            <select id="university_id" class="form-select" name="university_id" data-type-url="{{ $typeRoute }}">
                                <option value="">All</option>
                            </select>
                        </div>

                        {{-- Course Type --}}
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Course Type</label>
                            <select id="course_type" class="form-select" name="course_type" data-courses-url="{{ $courseRoute }}">
                                <option value="">All</option>
                            </select>
                        </div>

                        {{-- Courses --}}
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Courses</label>
                            <select id="course_id" name="course_id" class="form-select">
                                <option value="">All</option>
                            </select>
                        </div>

                        {{-- Buttons --}}
                        <div class="col-md-2 mt-5 d-flex gap-2">
                            <a href="{{ $formAction }}" class="btn btn-primary">Clear</a>
                            <button class="btn btn-success">Find</button>
                        </div>

                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/filter.js') }}"></script>
