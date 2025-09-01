@extends('layouts.app')

@section('content')
<div class="university-page">

    {{-- Filter Section --}}
    <div class="filter-card mb-4">
        <form method="GET" action="{{ route('guest.universities.index') }}" class="filter-form">
            <div class="filter-grid">

                {{-- Search --}}
                <div class="filter-field">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" value="{{ old('search', request('search')) }}" class="filter-input" placeholder="University or Course">
                </div>

                {{-- Country --}}
                <div class="filter-field">
                    <label for="country">Country</label>
                    <select id="country" name="country" class="filter-select" data-cities-url="{{ route('guest.get-cities', ':country') }}">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                        <option value="{{ $country }}">{{ $country }}</option>
                        {{ $country }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- City --}}
                <div class="filter-field">
                    <label for="city">City</label>
                    <select id="city" name="city" class="filter-select" data-universities-url="{{ route('guest.get-universities', ':city') }}">
                        <option value="">All Cities</option>
                    </select>
                </div>

                {{-- University --}}
                <div class="filter-field">
                    <label for="university_id">University</label>
                    <select id="university_id" name="university_id" class="filter-select" data-courses-url="{{ route('guest.get-courses', ':universityId') }}">
                        <option value="">All Universities</option>
                    </select>
                </div>

                {{-- Course --}}
                <div class="filter-field">
                    <label for="course_id">Course</label>
                    <select id="course_id" name="course_id" class="filter-select">
                        <option value="">All Courses</option>
                    </select>
                </div>

                {{-- Actions --}}
                <div class="filter-field">
                    <div class="filter-actions">
                        <a href="{{ route('guest.universities.index') }}" class="btn btn-clear">Reset</a>
                        <button type="submit" class="btn btn-apply">Filter</button>
                    </div>
                </div>

            </div>
        </form>
    </div>

    {{-- Universities Table --}}
    <div class="table-card">
        @if($universities->count())
        <table class="student-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Logo</th>
                    <th>Name</th>
                    <th>Short Name</th>
                    <th>Country</th>
                    <th>City</th>
                    <th>Website</th>
                    <th>Contact Email</th>
                    <th>Courses</th>
                </tr>
            </thead>
            <tbody>
                @foreach($universities as $uni)
                <tr>
                    <td>{{ $uni->id }}</td>
                    <td>
                        @if($uni->university_logo)
                        <img src="{{ asset('images/uni_logo/'.$uni->university_logo) }}" width="40" />
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('guest.universities.show', $uni->id) }}">
                            {{ $uni->name }}
                        </a>
                    </td>
                    <td>{{ $uni->short_name ?? 'N/A' }}</td>
                    <td>{{ $uni->country }}</td>
                    <td>{{ $uni->city ?? 'N/A' }}</td>
                    <td>
                        @if($uni->website)
                        <a href="{{ $uni->website }}" target="_blank">{{ $uni->website }}</a>
                        @else N/A @endif
                    </td>
                    <td>{{ $uni->contact_email ?? 'N/A' }}</td>
                    <td>
                        @if($uni->courses->count())
                        <button class="badge bg-success" type="button" data-bs-toggle="collapse" data-bs-target="#coursesCollapse{{ $uni->id }}" aria-expanded="false">
                            View ({{ $uni->courses->count() }})
                        </button>
                        @else No Courses @endif
                    </td>
                </tr>

                <tr class="collapse" id="coursesCollapse{{ $uni->id }}">
                    <td colspan="9">
                        @if($uni->courses->count())
                        <table class="table table-sm table-bordered mt-2">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Duration</th>
                                    <th>Fee</th>
                                    <th>Intakes</th>
                                    <th>MOI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($uni->courses as $course)
                                <tr>
                                    <td>{{ $course->course_code }}</td>
                                    <td>{{ $course->title }}</td>
                                    <td>{{ $course->description ?? 'N/A' }}</td>
                                    <td>{{ $course->duration ?? 'N/A' }}</td>
                                    <td>${{ number_format($course->fee,2) }}</td>
                                    <td>{{ $course->intakes ?? 'N/A' }}</td>
                                    <td>{{ $course->moi_requirement ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else <p>No courses available.</p> @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-wrap">
            {{ $universities->links() }}
        </div>
        @else
        <p class="alert alert-info">No universities found.</p>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/filter.js') }}"></script>
@endpush
