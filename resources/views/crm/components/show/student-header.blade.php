{{-- resources/views/crm/components/show/student-header.blade.php --}}
<div class="crm-student-header-modern">
    {{-- Main Card --}}
    <div class="student-header-card">
        <div class="student-header-content">
            {{-- Left Column: Avatar & Identity --}}
            <div class="header-left-col">
                <div class="student-avatar-wrapper">
                    @if ($student->students_photo && Storage::disk('public')->exists($student->students_photo))
                        <img src="{{ Storage::url($student->students_photo) }}" class="student-avatar-img" alt="Photo">
                    @else
                        <div class="student-avatar-placeholder">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                    @endif
                </div>

                <div class="student-identity">
                    <h1 class="student-name">
                        {{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}
                    </h1>
                    <div class="student-badges">
                        <span class="badge-applying">
                            <i class="fa-regular fa-file-lines"></i> {{ $student->applying_for ?? 'Not specified' }}
                        </span>
                        <span class="badge-country">
                            <i class="fa-solid fa-globe"></i> {{ $student->preferred_country ?? 'Not specified' }}
                        </span>
                    </div>
                    <div class="student-contact-info">
                        <span><i class="fa-solid fa-phone"></i> {{ $student->phone_number ?? '—' }}</span>
                        <span><i class="fa-regular fa-envelope"></i> {{ $student->email ?? '—' }}</span>
                    </div>
                    <div class="student-agent-info">
                        @if ($student->agent && $student->agent->business_logo && Storage::disk('public')->exists($student->agent->business_logo))
                            <img src="{{ Storage::url($student->agent->business_logo) }}" alt="Logo"
                                class="agent-logo-sm">
                        @else
                            <i class="fa-regular fa-building"></i>
                        @endif
                        <span>Student of:
                            <strong>{{ $student->agent?->business_name ?? (\Illuminate\Support\Str::words($student->agent?->name ?? '—', 2, '')) }}</strong></span>
                    </div>
                    @php $rating = (int)($student->rating ?? 0); @endphp
                    <div class="star-rating mb-1" id="studentStarRating">
                        <input type="radio" name="sr" value="3" id="sr3"{{ $rating == 3 ? ' checked' : '' }}>
                        <label for="sr3" onclick="updateStudentRating(3)">&#9733;</label>
                        <input type="radio" name="sr" value="2" id="sr2"{{ $rating == 2 ? ' checked' : '' }}>
                        <label for="sr2" onclick="updateStudentRating(2)">&#9733;</label>
                        <input type="radio" name="sr" value="1" id="sr1"{{ $rating == 1 ? ' checked' : '' }}>
                        <label for="sr1" onclick="updateStudentRating(1)">&#9733;</label>
                    </div>
                    @if ($canEdit)
                        <button class="btn-quick-edit" onclick="openMiniEditModal()">
                            <i class="fa-regular fa-pen-to-square"></i> Quick Edit
                        </button>
                    @endif
                </div>

            </div>

            {{-- Middle Column: Tags & Actions --}}
            <div class="header-middle-col">
                <div class="tags-section">
                    <div class="tags-header">
                        <span><i class="fa-solid fa-tags"></i> Student Tags</span>

                    </div>
                    <div class="tags-container" id="studentTagsList">
                        @if ($student->tags && is_array($student->tags))
                            @foreach ($student->tags as $tag)
                                <div class="tag-item">
                                    <span>🏷️ {{ $tag }}</span>
                                    <button type="button" class="remove-tag-btn"
                                        data-tag="{{ $tag }}">×</button>
                                </div>
                            @endforeach
                        @else
                            <div class="no-tags">No tags added yet</div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-purple mt-2" onclick="openTagModal({{ $student->id }})">
                        <i class="fa-solid fa-plus"></i> Add Tags
                    </button>
                </div>


            </div>

            {{-- Right Column: Revenue Card --}}
            <div class="header-right-col">
                <div class="revenue-card-modern">
                    <div class="revenue-header">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Revenue Overview</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="stat-label">Expected</span>
                        <strong class="stat-value">${{ number_format($expectedRevenue, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="stat-label">Received</span>
                        <strong class="stat-value text-success">${{ number_format($collectedRevenue, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="stat-label">Pending</span>
                        <strong class="stat-value text-warning">${{ number_format($remainingDue, 2) }}</strong>
                    </div>
                    <button class="btn-add-revenue" onclick="openRevenueModal({{ $student->id }})">
                        <i class="fa-solid fa-plus-circle"></i> Add Revenue
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if ($revenues instanceof \Illuminate\Pagination\LengthAwarePaginator && $revenues->hasPages())
        <div class="pagination-wrapper">
            {{ $revenues->links() }}
        </div>
    @endif
</div>
