@extends('layouts.app')

@section('content')
<div class="uni-page">

    {{-- Filter Section --}}
    <div class="uni-filter-card">
        <form method="GET" action="{{ route('agent.universities.index') }}" class="uni-filter-form">
            <div class="uni-filter-grid">
                {{-- Search --}}
                <div class="uni-filter-field">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" value="{{ old('search', request('search')) }}" class="uni-input" placeholder="University or Course">
                </div>
                {{-- Country --}}
                <div class="uni-filter-field">
                    <label for="country">Country</label>
                    <select id="country" name="country" class="uni-select" data-cities-url="{{ route('admin.get-cities', ':country') }}">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                        <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                            {{ $country }}
                        </option>
                        @endforeach
                    </select>
                </div>
                {{-- City --}}
                <div class="uni-filter-field">
                    <label for="city">City</label>
                    <select id="city" name="city" class="uni-select" data-universities-url="{{ route('admin.get-universities', ':city') }}">
                        <option value="">All Cities</option>
                    </select>
                </div>

                {{-- University --}}
                <div class="uni-filter-field">
                    <label for="university_id">University</label>
                    <select id="university_id" name="university_id" class="uni-select" data-courses-url="{{ route('admin.get-courses', ':universityId') }}">
                        <option value="">All Universities</option>
                    </select>
                </div>

                {{-- Course --}}
                <div class="uni-filter-field">
                    <label for="course_id">Course</label>
                    <select id="course_id" name="course_id" class="uni-select">
                        <option value="">All Courses</option>
                    </select>
                </div>
            </div>
            {{-- Actions --}}
            <div class="uni-filter-actions">
                <a href="{{ route('agent.universities.index') }}" class="uni-btn-clear">Reset</a>
                <button type="submit" class="uni-btn-apply">Filter</button>
            </div>
        </form>
    </div>
    {{-- Universities Cards --}}
    <div class="uni-cards-grid">
        @forelse($universities as $uni)
        <div class="uni-card">
            <div class="uni-card-header">
                @if($uni->university_logo)
                <img src="{{ asset('images/uni_logo/'.$uni->university_logo) }}" class="uni-logo" alt="{{ $uni->name }}">
                @endif
            </div>
            <div class="uni-card-body">
                <h3 class="uni-card-title">{{ $uni->name }}</h3>
                <p class="uni-card-subtitle">{{ $uni->short_name ?? 'N/A' }}</p>
                <p class="uni-card-location">{{ $uni->city ?? 'N/A' }}, {{ $uni->country }}</p>
                <p>
                    @if($uni->website)
                    <a href="{{ $uni->website }}" target="_blank" class="uni-web-link" rel="noopener">{{ $uni->website }}</a>
                    @endif
                </p>
                <p>{{ $uni->contact_email ?? 'N/A' }}</p>
            </div>
            @if($uni->courses->count())
            <div class="uni-card-footer">
                <button class="uni-btn-toggle" onclick="openCourseModal({{ $uni->id }})">
                    View Courses ({{ $uni->courses->count() }})
                </button>
            </div>
            @endif
        </div>

        {{-- Courses Modal --}}
        <div id="courseModal{{ $uni->id }}" class="uni-modal">
            <div class="uni-modal-content">
                <span class="uni-modal-close" onclick="closeCourseModal({{ $uni->id }})">&times;</span>
                <h3>Courses of {{ $uni->name }}</h3>
                <table class="uni-inner-table">
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
            </div>
        </div>

        @empty
        <p class="uni-no-data">No universities found.</p>
        @endforelse
    </div>

    <div class="pagination-wrap">
        {{ $universities->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCourseModal(id) {
        document.getElementById('courseModal' + id).style.display = 'block';
    }

    function closeCourseModal(id) {
        document.getElementById('courseModal' + id).style.display = 'none';
    }
    // Close modal on outside click
    window.onclick = function(event) {
        document.querySelectorAll('.uni-modal').forEach(modal => {
            if (event.target == modal) modal.style.display = "none";
        });
    }

</script>
@endpush
