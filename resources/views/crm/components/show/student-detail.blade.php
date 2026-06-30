{{-- resources/views/crm/components/show/student-detail.blade.php --}}
<div class="crm-sidebar">
    <div class="crm-section">
        <div class="crm-section-header">Student Details</div>
        <div class="crm-section-body">

            {{-- Revenue Section --}}
            @include('crm.components.show.revenue')

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
                <div class="val">{{ $student->dob?->format('d M Y') ?? '—' }} <span
                        class="text-muted">({{ $student->age ?? '?' }} yrs)</span>
                </div>
            </div>
            <div class="sidebar-field">
                <label>Gender</label>
                <div class="val">{{ $student->gender ?? '—' }}</div>
            </div>
            <div class="sidebar-field">
                <label>Marital Status</label>
                <div class="val">{{ $student->marital_status ?? '—' }}</div>
            </div>
            <div class="sidebar-field">
                <label>Nationality</label>
                <div class="val">{{ $student->nationality ?? '—' }}</div>
            </div>
            <div class="sidebar-field">
                <label>Passport</label>
                <div class="val">{{ $student->passport_number ?? '—' }} @if ($student->passport_expiry)
                        <span class="text-muted">(exp: {{ $student->passport_expiry->format('d M Y') }})</span>
                    @endif
                </div>
            </div>
            <div class="sidebar-field">
                <label>Education</label>
                <div class="val">
                    {{ $student->qualification ?? '—' }}
                    @if ($student->passed_year)
                        ({{ $student->passed_year }})
                    @endif
                    @if ($student->last_grade)
                        <span class="text-muted">• {{ $student->last_grade }}</span>
                    @endif
                    @if ($student->education_board)
                        <span class="text-muted">• {{ $student->education_board }}</span>
                    @endif
                    @if ($student->education_gap)
                        <span class="text-muted">• Gap: {{ $student->education_gap }}yr</span>
                    @endif
                </div>
            </div>
            <div class="sidebar-field">
                <label>Preferences</label>
                <div class="val">
                    {{ $student->preferred_country ?? '—' }}
                    @if ($student->preferred_city)
                        <span class="text-muted">• {{ $student->preferred_city }}</span>
                    @endif
                    @if ($student->preferred_course)
                        <span class="text-muted">• {{ $student->preferred_course }}</span>
                    @endif
                    @if ($student->preferred_university)
                        <span class="text-muted">• {{ $student->preferred_university }}</span>
                    @endif
                </div>
            </div>
            <div class="sidebar-field">
                <label>Address</label>
                <div class="val">
                    @if ($student->permanent_address)
                        {{ $student->permanent_address }}
                        @if ($student->temporary_address && $student->temporary_address !== $student->permanent_address)
                            <br><span class="text-muted">Temp: {{ $student->temporary_address }}</span>
                        @endif
                    @else
                        {{ $student->temporary_address ?? '—' }}
                    @endif
                </div>
            </div>
            <div class="sidebar-field">
                <label>Source</label>
                <div class="val">{{ $student->source ?? '—' }}</div>
            </div>
            @if ($student->remarks)
                <div class="sidebar-field">
                    <label>Remarks</label>
                    <div class="val small">{{ $student->remarks }}</div>
                </div>
            @endif

        </div>
    </div>
</div>
