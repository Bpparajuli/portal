    <link rel="stylesheet" href="{{ asset('css/university.css') }}">
    {{-- University Filter Section --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4">Find Your University</h5>
            <form method="GET" action="{{ route('guest.universities.index') }}">
                <div class="row g-3">

                    {{-- Search --}}
                    <div class="col-md-4">
                        <label for="search" class="form-label fw-semibold">Search</label>
                        <input type="text" id="search" name="search" value="{{ old('search', request('search')) }}" class="form-control" placeholder="University or Course">
                    </div>

                    {{-- Country --}}
                    <div class="col-md-2">
                        <label for="country" class="form-label fw-semibold">Country</label>
                        <select id="country" name="country" class="form-select" data-cities-url="{{ route('guest.get-cities', ':country') }}">
                            <option value="">All Countries</option>
                            @foreach($countries as $country)
                            <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
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

                {{-- Actions --}}
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('guest.universities.index') }}" class="btn btn-secondary">Clear</a>
                    <button type="submit" class="btn btn-primary">Find</button>
                </div>
            </form>
        </div>
    </div>
