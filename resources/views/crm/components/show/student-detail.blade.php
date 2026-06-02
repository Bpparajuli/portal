{{-- resources/views/crm/components/show/student-detail.blade.php --}}
<div class="crm-sidebar">
    <div class="crm-section">
        <div class="crm-section-header">Student Details</div>
        <div class="crm-section-body">
            <div class="sidebar-field">
                <label>Full Name</label>
                <div class="val">
                    {{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}</div>
            </div>
            <div class="sidebar-field">
                <label>Stage</label>
                <div class="val">
                    @if ($currentStage)
                        <span
                            style="background:{{ $currentStage->color }}20;color:{{ $currentStage->color }};border-radius:20px;padding:.2rem .65rem;font-size:.8rem;font-weight:600;display:inline-block">
                            {{ $currentStage->name }}
                        </span>
                        <div class="text-muted mt-1" style="font-size:.72rem">
                            {{ $student->days_in_current_stage ?? 0 }} days in this stage</div>
                    @else
                        <span class="text-muted">Not assigned</span>
                    @endif
                </div>
            </div>
            <div class="sidebar-field">
                <label>Date of Birth</label>
                <div class="val">{{ $student->dob?->format('d M Y') ?? '—' }} ({{ $student->age ?? '?' }} yrs)
                </div>
            </div>
            <div class="sidebar-field">
                <label>Gender</label>
                <div class="val">{{ $student->gender ?? '—' }}</div>
            </div>
            <div class="sidebar-field">
                <label>Nationality</label>
                <div class="val">{{ $student->nationality ?? '—' }}</div>
            </div>
            <div class="sidebar-field">
                <label>Passport</label>
                <div class="val">{{ $student->passport_number ?? '—' }}</div>
            </div>
            <div class="sidebar-field">
                <label>Qualification</label>
                <div class="val">{{ $student->qualification ?? '—' }} ({{ $student->passed_year ?? '—' }})
                </div>
            </div>
            <div class="sidebar-field">
                <label>Preferred Country</label>
                <div class="val">{{ $student->preferred_country ?? '—' }}</div>
            </div>
            <div class="sidebar-field">
                <label>Remarks</label>
                <div class="val small">{{ $student->remarks ?? '—' }}</div>
            </div>
        </div>

        {{-- Revenue Section --}}
        @include('crm.components.show.revenue')
    </div>
</div>
