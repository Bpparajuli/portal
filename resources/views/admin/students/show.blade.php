@extends('layouts.admin')

@section('title', 'Student Profile: ' . $student->full_name)
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush
@section('admin-content')
    <div>
        {{-- Header --}}
        <div class="profile-header mb-4">
            <div
                style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;position:relative;z-index:1;">
                <div>
                    <h1 style="font-size:1.35rem;font-weight:700;color:#fff;margin-bottom:0.25rem;">
                        {{ $student->full_name }}
                    </h1>
                    <div style="display:flex;gap:0.6rem;flex-wrap:wrap;align-items:center;">
                        @if ($student->email)
                            <a href="mailto:{{ $student->email }}"
                                style="display:inline-flex;align-items:center;gap:0.35rem;background:rgba(255,255,255,0.12);color:#fff;padding:0.35rem 0.75rem;border-radius:8px;font-size:0.75rem;text-decoration:none;border:1px solid rgba(255,255,255,0.15);transition:all 0.2s;"
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.12)'">
                                <i class="fa-solid fa-envelope"></i> {{ Str::limit($student->email, 30) }}
                            </a>
                        @endif
                        @if ($student->phone_number)
                            <a href="tel:{{ $student->phone_number }}"
                                style="display:inline-flex;align-items:center;gap:0.35rem;background:rgba(255,255,255,0.12);color:#fff;padding:0.35rem 0.75rem;border-radius:8px;font-size:0.75rem;text-decoration:none;border:1px solid rgba(255,255,255,0.15);transition:all 0.2s;"
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.12)'">
                                <i class="fa-solid fa-phone"></i> {{ $student->phone_number }}
                            </a>
                        @endif
                    </div>
                </div>
                <div style="display:flex;gap:0.5rem;">
                    <a href="{{ route('admin.students.index') }}"
                        style="display:inline-flex;align-items:center;gap:0.35rem;background:rgba(255,255,255,0.12);color:#fff;padding:0.35rem 0.85rem;border-radius:8px;font-size:0.75rem;text-decoration:none;border:1px solid rgba(255,255,255,0.2);transition:all 0.2s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.12)'">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <a href="{{ route('admin.students.edit', $student) }}"
                        style="display:inline-flex;align-items:center;gap:0.35rem;background:var(--accent);color:var(--primary);padding:0.35rem 0.85rem;border-radius:8px;font-size:0.75rem;font-weight:600;text-decoration:none;transition:all 0.2s;"
                        onmouseover="this.style.background='var(--accent-dark)'"
                        onmouseout="this.style.background='var(--accent)'">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Left Sidebar – Profile Card --}}
            <div class="col-lg-4">
                <div class="sidebar-card p-4">
                    {{-- Agent --}}
                    <div style="text-align:center;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.75rem;">
                        @if ($student->agent)
                            <a href="{{ route('admin.users.show', $student->agent->slug) }}"
                                style="color:var(--primary);font-weight:500;text-decoration:none;">
                                {{ $student->agent->business_name ?? ($student->agent->username ?? '—') }}
                            </a>
                        @else
                            N/A
                        @endif
                    </div>

                    {{-- Photo --}}
                    <div style="text-align:center;margin-bottom:1rem;">
                        <div style="position:relative;display:inline-block;">
                            @if ($student->students_photo && Storage::disk('public')->exists($student->students_photo))
                                <img src="{{ Storage::url($student->students_photo) }}" alt="" class="student-img">
                            @else
                                <div
                                    style="width:140px;height:140px;border-radius:16px;background:var(--primary-soft);display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-user-graduate fa-4x" style="color:var(--primary);opacity:0.5;"></i>
                                </div>
                            @endif
                            @if ($student->preferred_country)
                                <span
                                    style="position:absolute;bottom:-8px;left:50%;transform:translateX(-50%);background:var(--accent);color:var(--primary);font-size:0.65rem;font-weight:700;padding:0.15rem 0.7rem;border-radius:20px;white-space:nowrap;">
                                    <i class="fas fa-map-pin me-1"
                                        style="font-size:0.55rem;"></i>{{ $student->preferred_country }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <h3
                        style="font-size:1.05rem;font-weight:700;color:var(--primary);text-align:center;margin-bottom:0.75rem;">
                        {{ $student->full_name }}</h3>

                    {{-- Quick Stats --}}
                    <hr style="border-color:#f0f0f0;margin:0.5rem 0;opacity:1;">
                    <div class="row g-2" style="margin:0.5rem 0;">
                        <div class="col-6" style="text-align:center;">
                            <div style="font-size:0.62rem;color:var(--text-muted);"><i class="fas fa-folder-open me-1"
                                    style="color:var(--info);"></i>Documents</div>
                            <div style="font-weight:700;font-size:1.1rem;color:var(--primary);">
                                {{ $student->documents->count() }}</div>
                        </div>
                        <div class="col-6" style="text-align:center;">
                            <div style="font-size:0.62rem;color:var(--text-muted);"><i class="fas fa-file-alt me-1"
                                    style="color:var(--secondary);"></i>Applications</div>
                            <div style="font-weight:700;font-size:1.1rem;color:var(--primary);">
                                {{ $student->applications->count() }}</div>
                        </div>
                    </div>

                    {{-- Revenue Summary --}}
                    <hr style="border-color:#f0f0f0;margin:0.5rem 0;opacity:1;">
                    <div style="margin:0.5rem 0;">
                        <div style="font-size:0.72rem;font-weight:600;color:var(--success);margin-bottom:0.4rem;"><i
                                class="fas fa-coins me-1"></i>Revenue Summary</div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div style="padding:0.35rem 0.5rem;border-radius:8px;background:#f9fafb;text-align:center;">
                                    <div style="font-size:0.6rem;color:var(--text-muted);">Expected</div>
                                    <div style="font-weight:700;font-size:0.85rem;">
                                        {{ number_format((float) ($student->expected_revenue ?? 0), 2) }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div style="padding:0.35rem 0.5rem;border-radius:8px;background:#f9fafb;text-align:center;">
                                    <div style="font-size:0.6rem;color:var(--text-muted);">Received</div>
                                    <div style="font-weight:700;font-size:0.85rem;color:var(--success);">
                                        {{ number_format((float) ($student->received_revenue ?? 0), 2) }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                @php $pct = (float)($student->expected_revenue ?? 0) > 0 ? round(((float)($student->received_revenue ?? 0) / (float)($student->expected_revenue ?? 0)) * 100) : 0; @endphp
                                <div
                                    style="padding:0.35rem 0.5rem;border-radius:8px;text-align:center;{{ $pct >= 100 ? 'background:#d1fae5;color:#059669;' : 'background:#fef3c7;color:#d97706;' }}">
                                    <div style="font-size:0.6rem;color:var(--text-muted);">Collected</div>
                                    <div style="font-weight:700;font-size:0.85rem;">{{ $pct }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Remarks --}}
                    <hr style="border-color:#f0f0f0;margin:0.5rem 0;opacity:1;">
                    <div>
                        <div style="font-size:0.72rem;font-weight:600;color:var(--primary);margin-bottom:0.4rem;"><i
                                class="fas fa-pen me-1"></i>Remarks</div>
                        <div
                            style="padding:0.5rem 0.7rem;background:#f9fafb;border-radius:8px;font-size:0.78rem;color:#374151;word-wrap:break-word;">
                            @php
                                $notes = $student->remarks;
                                if ($notes) {
                                    $decoded = json_decode($notes, true);
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        $notes = is_array($decoded)
                                            ? implode("\n", array_column($decoded, 'text') ?: [$notes])
                                            : $notes;
                                    }
                                }
                            @endphp
                            {{ $notes ?: 'No remarks added.' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Main Content --}}
            <div class="col-lg-8">
                <div style="background:#fff;border-radius:14px;border:1px solid #f0f0f0;overflow:hidden;">
                    {{-- Tabs --}}
                    <div style="border-bottom:1px solid #f0f0f0;">
                        <ul class="nav custom-tabs" id="studentTab" role="tablist" style="padding:0 0.5rem;">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview">
                                    <i class="fas fa-id-card me-1"></i> Overview
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#applications">
                                    <i class="fas fa-university me-1"></i> Applications
                                    <span class="badge bg-primary ms-1"
                                        style="font-size:0.6rem;">{{ $student->applications->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents">
                                    <i class="fas fa-folder-open me-1"></i> Documents
                                    <span class="badge bg-info ms-1"
                                        style="font-size:0.6rem;">{{ $student->documents->count() }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div style="padding:1.25rem;">
                        <div class="tab-content">
                            {{-- Overview Tab --}}
                            <div class="tab-pane fade show active" id="overview">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <h6 style="color:var(--primary);"><i class="fas fa-user"></i> Personal</h6>
                                            <hr>
                                            <p><strong>DOB:</strong>
                                                {{ $student->dob ? $student->dob->format('M d, Y') : 'N/A' }}<br>
                                                <strong>Gender:</strong> {{ $student->gender ?? 'N/A' }}<br>
                                                <strong>Nationality:</strong> {{ $student->nationality ?? 'N/A' }}<br>
                                                <strong>Marital Status:</strong> {{ $student->marital_status ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <h6 style="color:var(--success);"><i class="fas fa-passport"></i> Passport
                                            </h6>
                                            <hr>
                                            <p><strong>Number:</strong> {{ $student->passport_number ?? 'N/A' }}<br>
                                                <strong>Expiry:</strong>
                                                {{ $student->passport_expiry ? $student->passport_expiry->format('M d, Y') : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <h6 style="color:var(--accent);"><i class="fas fa-graduation-cap"></i>
                                                Education</h6>
                                            <hr>
                                            <p><strong>Qualification:</strong> {{ $student->qualification ?? 'N/A' }}<br>
                                                <strong>Passed Year:</strong> {{ $student->passed_year ?? 'N/A' }}<br>
                                                <strong>Gap:</strong> {{ $student->gap ?? '0' }} years<br>
                                                <strong>Grades:</strong> {{ $student->last_grades ?? 'N/A' }}<br>
                                                <strong>Board:</strong> {{ $student->education_board ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <h6 style="color:var(--info);"><i class="fas fa-globe"></i> Study Preferences
                                            </h6>
                                            <hr>
                                            <p><strong>Country:</strong> {{ $student->preferred_country ?? 'N/A' }}<br>
                                                <strong>City:</strong> {{ $student->preferred_city ?? 'N/A' }}<br>
                                                <strong>Course:</strong> {{ $student->preferred_course ?? 'N/A' }}<br>
                                                <strong>University:</strong> {{ $student->preferred_university ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="info-card">
                                            <h6 style="color:var(--secondary);"><i class="fas fa-home"></i> Addresses</h6>
                                            <hr>
                                            <p><strong>Permanent:</strong> {{ $student->permanent_address ?? 'N/A' }}<br>
                                                <strong>Temporary:</strong> {{ $student->temporary_address ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Applications Tab --}}
                            <div class="tab-pane fade" id="applications">
                                <div
                                    style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
                                    <h5 style="font-size:0.9rem;font-weight:700;color:var(--primary);margin:0;">
                                        <i class="fas fa-file-alt me-1"></i> Applications & Communication
                                    </h5>
                                    <a href="{{ route('admin.applications.create', ['student_id' => $student->id]) }}"
                                        style="display:inline-flex;align-items:center;gap:0.35rem;background:var(--primary);color:#fff;padding:0.35rem 0.85rem;border-radius:8px;font-size:0.75rem;font-weight:500;text-decoration:none;transition:background 0.2s;"
                                        onmouseover="this.style.background='var(--primary-dark)'"
                                        onmouseout="this.style.background='var(--primary)'">
                                        <i class="fas fa-plus"></i> New Application
                                    </a>
                                </div>

                                @if ($student->applications->isNotEmpty())
                                    <div class="row g-3">
                                        @foreach ($student->applications as $app)
                                            <div class="col-12">
                                                <div class="app-card">
                                                    <div class="app-header"
                                                        style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
                                                        <span><i class="fas fa-ticket-alt me-1"></i><strong>Application
                                                                #{{ $app->application_number ?? $app->id }}</strong></span>
                                                        <span style="font-size:0.72rem;opacity:0.85;"><i
                                                                class="far fa-calendar-alt me-1"></i>{{ $app->created_at->format('M d, Y') }}</span>
                                                    </div>
                                                    <div style="padding:1rem;">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <div style="margin-bottom:0.5rem;">
                                                                    <div
                                                                        style="font-size:0.65rem;text-transform:uppercase;font-weight:600;color:var(--text-muted);">
                                                                        University</div>
                                                                    <div style="font-weight:600;font-size:0.82rem;">
                                                                        {{ $app->university->name ?? 'N/A' }}</div>
                                                                </div>
                                                                <div class="row g-2">
                                                                    <div class="col-6">
                                                                        <div
                                                                            style="font-size:0.65rem;text-transform:uppercase;font-weight:600;color:var(--text-muted);">
                                                                            Country</div>
                                                                        <div style="font-size:0.78rem;">
                                                                            {{ $app->university->country ?? 'N/A' }}</div>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <div
                                                                            style="font-size:0.65rem;text-transform:uppercase;font-weight:600;color:var(--text-muted);">
                                                                            City</div>
                                                                        <div style="font-size:0.78rem;">
                                                                            {{ $app->university->city ?? 'N/A' }}</div>
                                                                    </div>
                                                                </div>
                                                                <div style="margin-top:0.5rem;">
                                                                    <div
                                                                        style="font-size:0.65rem;text-transform:uppercase;font-weight:600;color:var(--text-muted);">
                                                                        Course</div>
                                                                    <div style="font-size:0.78rem;">
                                                                        {{ $app->course->title ?? 'N/A' }}</div>
                                                                </div>
                                                                <div class="row g-2" style="margin-top:0.3rem;">
                                                                    <div class="col-6">
                                                                        <div
                                                                            style="font-size:0.65rem;text-transform:uppercase;font-weight:600;color:var(--text-muted);">
                                                                            Duration</div>
                                                                        <div style="font-size:0.78rem;">
                                                                            {{ $app->course->duration ?? 'N/A' }}</div>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <div
                                                                            style="font-size:0.65rem;text-transform:uppercase;font-weight:600;color:var(--text-muted);">
                                                                            Fee</div>
                                                                        <div style="font-size:0.78rem;">
                                                                            {{ $app->course->fee ?? 'N/A' }}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div
                                                                    style="display:flex;gap:0.6rem;flex-wrap:wrap;margin-bottom:0.75rem;">
                                                                    <div
                                                                        style="flex:1;padding:0.6rem;background:#f9fafb;border-radius:8px;text-align:center;min-width:100px;">
                                                                        <div
                                                                            style="font-size:0.6rem;color:var(--text-muted);">
                                                                            Status</div>
                                                                        <span class="show-status-badge"
                                                                            style="background:{{ $app->status?->bg_color ?? '#6c757d' }}18;color:{{ $app->status?->bg_color ?? '#6c757d' }};border:1px solid {{ $app->status?->bg_color ?? '#6c757d' }}25;">
                                                                            {{ $app->status?->name ?? 'N/A' }}
                                                                        </span>
                                                                    </div>
                                                                    <div
                                                                        style="flex:1;padding:0.6rem;background:#f9fafb;border-radius:8px;text-align:center;min-width:100px;">
                                                                        <div
                                                                            style="font-size:0.6rem;color:var(--text-muted);">
                                                                            SOP</div>
                                                                        @if ($app->sop_file)
                                                                            <a href="{{ Storage::url($app->sop_file) }}"
                                                                                target="_blank"
                                                                                style="display:inline-flex;align-items:center;gap:0.25rem;font-size:0.72rem;color:var(--primary);text-decoration:none;">
                                                                                <i class="fas fa-file-pdf"></i> View
                                                                            </a>
                                                                        @else
                                                                            <a href="{{ route('admin.applications.edit', $app->id) }}"
                                                                                style="display:inline-flex;align-items:center;gap:0.25rem;font-size:0.72rem;color:var(--text-muted);text-decoration:none;">
                                                                                <i class="fas fa-upload"></i> Upload
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div
                                                                    style="display:flex;gap:0.4rem;justify-content:flex-end;">
                                                                    <a href="{{ route('admin.applications.show', $app->id) }}"
                                                                        style="padding:0.25rem 0.7rem;border-radius:20px;border:1px solid #e5e7eb;font-size:0.72rem;color:var(--text-color);text-decoration:none;transition:all 0.15s;"
                                                                        onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'"
                                                                        onmouseout="this.style.borderColor='#e5e7eb';this.style.color='var(--text-color)'"><i
                                                                            class="fas fa-eye me-1"></i>View</a>
                                                                    <a href="{{ route('admin.applications.edit', $app->id) }}"
                                                                        style="padding:0.25rem 0.7rem;border-radius:20px;border:1px solid #e5e7eb;font-size:0.72rem;color:var(--text-color);text-decoration:none;transition:all 0.15s;"
                                                                        onmouseover="this.style.borderColor='var(--success)';this.style.color='var(--success)'"
                                                                        onmouseout="this.style.borderColor='#e5e7eb';this.style.color='var(--text-color)'"><i
                                                                            class="fas fa-edit me-1"></i>Edit</a>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Messages --}}
                                                        <div style="margin-top:1rem;">
                                                            <div
                                                                style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                                                                <span style="font-weight:600;font-size:0.78rem;"><i
                                                                        class="fas fa-comment-dots me-1"></i>
                                                                    Conversation</span>
                                                                <button
                                                                    style="border:none;background:none;color:var(--text-muted);font-size:0.72rem;cursor:pointer;"
                                                                    type="button" data-bs-toggle="collapse"
                                                                    data-bs-target="#messages-{{ $app->id }}">
                                                                    <i class="fas fa-chevron-down"></i>
                                                                </button>
                                                            </div>
                                                            <div class="collapse show" id="messages-{{ $app->id }}">
                                                                <div
                                                                    style="border:1px solid #f0f0f0;border-radius:8px;padding:0.75rem;background:#fafafa;max-height:300px;overflow-y:auto;">
                                                                    @forelse($app->messages as $msg)
                                                                        <div
                                                                            style="display:flex;margin-bottom:0.6rem;{{ $msg->type === 'admin' ? '' : 'justify-content:flex-end;' }}">
                                                                            <div
                                                                                class="msg-bubble {{ $msg->type === 'admin' ? 'msg-admin' : 'msg-user' }}">
                                                                                <p style="margin:0 0 0.25rem 0;">
                                                                                    {{ $msg->message }}</p>
                                                                                <small
                                                                                    style="opacity:0.75;font-size:0.65rem;">
                                                                                    <i
                                                                                        class="far fa-user-circle me-1"></i>{{ $msg->user->name ?? 'System' }}
                                                                                    •
                                                                                    {{ $msg->created_at->timezone('Asia/Kathmandu')->format('d M Y, H:i') }}
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                    @empty
                                                                        <p
                                                                            style="text-align:center;color:var(--text-muted);font-size:0.75rem;margin:0;">
                                                                            No messages yet.</p>
                                                                    @endforelse
                                                                </div>
                                                                <form method="POST"
                                                                    action="{{ route('admin.applications.addMessage', $app) }}"
                                                                    style="margin-top:0.6rem;">
                                                                    @csrf
                                                                    <div style="display:flex;gap:0.4rem;">
                                                                        <textarea name="message" rows="1" placeholder="Type your message..." required
                                                                            style="flex:1;padding:0.35rem 0.6rem;font-size:0.78rem;border:1px solid #e5e7eb;border-radius:6px;outline:none;font-family:inherit;resize:none;"
                                                                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#e5e7eb'"></textarea>
                                                                        <button type="submit"
                                                                            style="padding:0.35rem 0.75rem;background:var(--primary);color:#fff;border:none;border-radius:6px;font-size:0.75rem;cursor:pointer;transition:background 0.2s;white-space:nowrap;"
                                                                            onmouseover="this.style.background='var(--primary-dark)'"
                                                                            onmouseout="this.style.background='var(--primary)'">
                                                                            <i class="fas fa-paper-plane"></i> Send
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div
                                        style="text-align:center;padding:2.5rem 1rem;background:#f9fafb;border-radius:12px;">
                                        <i class="fas fa-inbox fa-3x"
                                            style="color:var(--text-muted);opacity:0.3;margin-bottom:0.75rem;display:block;"></i>
                                        <p style="color:var(--text-muted);font-size:0.82rem;">No applications yet.</p>
                                        <a href="{{ route('admin.applications.create', ['student_id' => $student->id]) }}"
                                            style="display:inline-flex;align-items:center;gap:0.35rem;background:var(--primary);color:#fff;padding:0.35rem 0.85rem;border-radius:8px;font-size:0.75rem;text-decoration:none;">
                                            Create First Application
                                        </a>
                                    </div>
                                @endif
                            </div>

                            {{-- Documents Tab --}}
                            <div class="tab-pane fade" id="documents">
                                @php
                                    $statusColor = match ($documentStats['status']) {
                                        'Completed' => 'success',
                                        'Incomplete' => 'warning',
                                        default => 'danger',
                                    };
                                    $icon = match ($documentStats['status']) {
                                        'Completed' => 'fa-check-circle text-success',
                                        'Incomplete' => 'fa-hourglass-half text-warning',
                                        default => 'fa-circle-exclamation text-danger',
                                    };
                                @endphp

                                {{-- Header --}}
                                <div
                                    style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;">
                                    <h5 style="font-size:0.9rem;font-weight:700;color:var(--primary);margin:0;">
                                        <i class="fas fa-folder-open me-1"></i> Student Documents
                                    </h5>
                                    <div style="display:flex;align-items:center;gap:0.6rem;">
                                        <i class="fa-solid {{ $icon }}"></i>
                                        <span
                                            style="font-weight:600;font-size:0.72rem;color:var(--{{ $statusColor }});">{{ $documentStats['status'] }}</span>
                                    </div>
                                </div>

                                {{-- Progress --}}
                                <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-bottom:1rem;">
                                    <div style="flex:1;min-width:200px;">
                                        <div
                                            style="display:flex;justify-content:space-between;font-size:0.65rem;color:var(--text-muted);margin-bottom:0.2rem;">
                                            <span>Document Progress</span>
                                            <span>{{ $documentStats['uploaded_count'] }} / {{ $totalRequiredDocs }}</span>
                                        </div>
                                        <div style="height:6px;background:#f0f0f0;border-radius:3px;overflow:hidden;">
                                            <div
                                                style="height:100%;border-radius:3px;width:{{ $documentStats['progress'] }}%;background:var(--{{ $statusColor }});transition:width 0.3s;">
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display:flex;gap:0.4rem;align-items:flex-end;">
                                        @if ($documentStats['status'] === 'Completed')
                                            <a href="{{ route('admin.applications.create', ['student_id' => $student->id]) }}"
                                                style="display:inline-flex;align-items:center;gap:0.35rem;background:var(--success);color:#fff;padding:0.35rem 0.75rem;border-radius:8px;font-size:0.72rem;text-decoration:none;transition:background 0.2s;"
                                                onmouseover="this.style.background='var(--success-dark)'"
                                                onmouseout="this.style.background='var(--success)'">
                                                <i class="fas fa-paper-plane"></i> Apply Now
                                            </a>
                                        @else
                                            <a href="{{ route('admin.documents.index', $student->id) }}"
                                                style="display:inline-flex;align-items:center;gap:0.35rem;background:var(--warning);color:#fff;padding:0.35rem 0.75rem;border-radius:8px;font-size:0.72rem;text-decoration:none;">
                                                <i class="fa-solid fa-folder-open"></i> Upload Docs
                                            </a>
                                        @endif
                                        @if ($documentStats['status'] === 'Completed')
                                            <a href="{{ route('admin.documents.index', $student->id) }}"
                                                style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.35rem 0.75rem;border-radius:8px;font-size:0.72rem;border:1px solid #e5e7eb;color:var(--text-muted);text-decoration:none;">
                                                + Add more
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                @if ($student->documents->isNotEmpty())
                                    <div class="row g-3">
                                        @foreach ($student->documents as $doc)
                                            @php
                                                $ext = pathinfo($doc->file_name, PATHINFO_EXTENSION);
                                                $isImage = in_array(strtolower($ext), [
                                                    'jpg',
                                                    'jpeg',
                                                    'png',
                                                    'gif',
                                                    'webp',
                                                ]);
                                                $fileUrl = Storage::url($doc->file_path);
                                            @endphp
                                            <div class="col-md-4 col-lg-4">
                                                <div class="doc-card">
                                                    <a href="#" data-preview="{{ $fileUrl }}">
                                                        <div style="position:relative;">
                                                            @if ($isImage)
                                                                <img src="{{ $fileUrl }}"
                                                                    style="width:100%;height:140px;object-fit:cover;display:block;"
                                                                    alt="Document">
                                                            @else
                                                                <div
                                                                    style="height:140px;background:#f9fafb;display:flex;align-items:center;justify-content:center;">
                                                                    <i class="fas fa-file-pdf fa-4x text-danger"></i>
                                                                </div>
                                                            @endif
                                                            <span
                                                                style="position:absolute;top:0.5rem;right:0.5rem;background:rgba(0,0,0,0.7);color:#fff;font-size:0.6rem;font-weight:600;padding:0.1rem 0.45rem;border-radius:4px;">{{ strtoupper($ext) }}</span>
                                                        </div>
                                                    </a>
                                                    <div style="padding:0.6rem 0.75rem;">
                                                        <div
                                                            style="font-weight:600;font-size:0.76rem;margin-bottom:0.25rem;">
                                                            {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</div>
                                                        <div
                                                            style="font-size:0.65rem;color:var(--text-muted);margin-bottom:0.4rem;">
                                                            <i
                                                                class="far fa-calendar-alt me-1"></i>{{ $doc->created_at->format('M d, Y') }}
                                                        </div>
                                                        <div style="display:flex;gap:0.35rem;">
                                                            <a href="{{ route('admin.documents.download', ['student' => $student->id, 'document' => $doc->id]) }}"
                                                                style="flex:1;padding:0.25rem;border-radius:20px;border:1px solid var(--success);color:var(--success);text-decoration:none;text-align:center;font-size:0.72rem;transition:all 0.15s;"
                                                                onmouseover="this.style.background='#f0fdf4'"
                                                                onmouseout="this.style.background='transparent'">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                            <form
                                                                action="{{ route('admin.documents.destroy', [$student->id, $doc->id]) }}"
                                                                method="POST" style="flex:1;"
                                                                onsubmit="return confirm('Delete this document?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit"
                                                                    style="width:100%;padding:0.25rem;border-radius:20px;border:1px solid var(--danger);color:var(--danger);background:transparent;font-size:0.72rem;cursor:pointer;transition:all 0.15s;"
                                                                    onmouseover="this.style.background='#fef2f2'"
                                                                    onmouseout="this.style.background='transparent'">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div
                                        style="text-align:center;padding:2.5rem 1rem;background:#f9fafb;border-radius:12px;">
                                        <i class="fas fa-folder-open fa-3x"
                                            style="color:var(--text-muted);opacity:0.3;margin-bottom:0.75rem;display:block;"></i>
                                        <p style="color:var(--text-muted);font-size:0.82rem;">No documents uploaded yet.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
