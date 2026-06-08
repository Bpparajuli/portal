<div class="card border-0 shadow-sm mb-4" style="border-radius:12px;overflow:hidden;">
    <div class="d-flex justify-content-between align-items-center px-3 py-2 table-card-header">
        <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-table me-2"
                style="color:rgba(255,255,255,0.5);"></i>{{ $title }}</h6>
        <small>{{ $students->total() }}
            student{{ $students->total() !== 1 ? 's' : '' }}</small>
    </div>
    <div class="table-responsive">
        <table class="table student-table align-middle mb-0" style="font-size:0.78rem;">
            <thead>
                <tr>
                    <th
                        style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">
                        Student</th>
                    <th
                        style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">
                        Contact</th>
                    <th
                        style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">
                        Agent</th>
                    <th
                        style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">
                        Status</th>
                    <th
                        style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">
                        Documents</th>
                    <th
                        style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);text-align:right;width:100px;background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">
                        Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    @php
                        $latestApp = $student->applications()->with('status', 'university')->latest()->first();
                        $docStatusColor = match ($student->document_status) {
                            'Completed' => 'success',
                            'Incomplete' => 'warning',
                            default => 'danger',
                        };
                        $fillClass = 'fill-' . $docStatusColor;
                    @endphp
                    <tr style="border-bottom:1px solid #f3f4f6;transition:background 0.15s;"
                        onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                        <td style="padding:0.5rem 0.6rem;vertical-align:middle;">
                            <div style="display:flex;align-items:center;gap:0.65rem;">
                                <div style="position:relative;flex-shrink:0;">
                                    <a href="{{ route('admin.students.show', $student) }}">
                                        @if ($student->students_photo && Storage::disk('public')->exists($student->students_photo))
                                            <img src="{{ Storage::url($student->students_photo) }}"
                                                style="width:38px;height:38px;border-radius:8px;object-fit:cover;display:block;"
                                                alt="">
                                        @else
                                            <div
                                                style="width:38px;height:38px;border-radius:8px;background:var(--primary-soft);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:15px;">
                                                <i class="fa-solid fa-user"></i>
                                            </div>
                                        @endif
                                        <span
                                            style="position:absolute;bottom:-4px;left:50%;transform:translateX(-50%);background:var(--primary);color:#fff;font-size:8px;font-weight:800;padding:1px 5px;border-radius:4px;line-height:1.4;z-index:2;box-shadow:0 1px 3px rgba(0,0,0,0.15);">
                                            {{ $student->id }}
                                        </span>
                                    </a>
                                </div>
                                <div style="min-width:0;">
                                    <a href="{{ route('admin.students.show', $student) }}"
                                        style="font-weight:600;color:#1e293b;text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $student->full_name }}</a>
                                    @if ($student->preferred_country)
                                        <span style="font-size:0.66rem;color:var(--text-muted);"><i
                                                class="fa-solid fa-location-dot me-1"
                                                style="font-size:0.6rem;"></i>{{ $student->preferred_country }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="padding:0.5rem 0.6rem;vertical-align:middle;">
                            @if ($student->email)
                                <a href="mailto:{{ $student->email }}"
                                    style="display:inline-flex;align-items:center;gap:0.3rem;text-decoration:none;font-size:0.72rem;color:var(--text-muted);margin-bottom:0.15rem;"><i
                                        class="fa-solid fa-envelope" style="font-size:0.6rem;flex-shrink:0;"></i><span
                                        style="white-space:nowrap;">{{ Str::limit($student->email, 24) }}</span></a>
                            @endif
                            <br>
                            @if ($student->phone_number)
                                <a href="tel:{{ $student->phone_number }}"
                                    style="display:inline-flex;align-items:center;gap:0.3rem;text-decoration:none;font-size:0.72rem;color:var(--text-muted);"><i
                                        class="fa-solid fa-phone" style="font-size:0.6rem;flex-shrink:0;"></i><span
                                        style="white-space:nowrap;">{{ $student->phone_number }}</span></a>
                            @endif
                        </td>
                        <td style="padding:0.5rem 0.6rem;vertical-align:middle;">
                            @if ($student->agent)
                                <a href="{{ route('admin.users.show', $student->agent->slug) }}"
                                    style="text-decoration:none;font-weight:500;font-size:0.74rem;color:var(--primary);">
                                    {{ $student->agent->business_name ?? ($student->agent->username ?? '—') }}
                                </a>
                            @else
                                <span style="font-size:0.72rem;color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="padding:0.5rem 0.6rem;vertical-align:middle;">
                            @if ($student->applications->count())
                                @foreach ($student->applications as $app)
                                    <a href="{{ route('admin.applications.show', $app) }}"
                                        style="display:inline-block;margin-bottom:0.2rem;">
                                        <span
                                            style="display:inline-flex;align-items:center;gap:3px;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.6rem;font-weight:600;white-space:nowrap;background:{{ $app->status?->bg_color ?? '#6c757d' }}18;color:{{ $app->status?->bg_color ?? '#6c757d' }};border:1px solid {{ $app->status?->bg_color ?? '#6c757d' }}25;">
                                            {{ $app->university?->short_name ?? '' }}
                                            {{ $app->status?->name ?? 'N/A' }}
                                        </span>
                                    </a>
                                @endforeach
                            @else
                                <span
                                    style="display:inline-block;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.6rem;font-weight:600;background:#f3f4f6;color:#9ca3af;">Not
                                    Applied</span>
                            @endif
                        </td>
                        <td style="padding:0.5rem 0.6rem;vertical-align:middle;">
                            <a href="{{ route('admin.documents.index', $student) }}" style="text-decoration:none;">
                                <span
                                    style="font-weight:600;font-size:0.65rem;color:var(--{{ $docStatusColor }});">{{ $student->document_status }}</span>
                                <div
                                    style="height:3px;background:#f0f0f0;border-radius:2px;margin:0.2rem 0;max-width:80px;">
                                    <div
                                        style="height:100%;border-radius:2px;width:{{ $student->document_progress }}%;background:var(--{{ $docStatusColor }});transition:width 0.3s;">
                                    </div>
                                </div>
                                <span
                                    style="font-size:0.62rem;color:var(--text-muted);">{{ $student->uploaded_count }}/{{ $totalRequiredDocs }}</span>
                            </a>
                        </td>
                        <td style="padding:0.5rem 0.6rem;vertical-align:middle;text-align:right;">
                            <div style="display:flex;justify-content:flex-end;gap:0.25rem;">
                                @if ($student->applications->count())
                                    <a href="{{ route('admin.applications.show', $latestApp) }}"
                                        style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;border:1px solid #e5e7eb;background:#fff;color:#10b981;text-decoration:none;font-size:0.72rem;transition:all 0.15s;"
                                        title="View Application"
                                        onmouseover="this.style.background='#f0fdf4';this.style.borderColor='#10b981'"
                                        onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb'"><i
                                            class="fa-solid fa-eye"></i></a>
                                @elseif ($student->uploaded_count >= $totalRequiredDocs)
                                    <a href="{{ route('admin.applications.create', ['student_id' => $student->id]) }}"
                                        style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;border:1px solid #e5e7eb;background:#fff;color:var(--primary);text-decoration:none;font-size:0.72rem;transition:all 0.15s;"
                                        title="Create Application"
                                        onmouseover="this.style.background='var(--primary-soft)';this.style.borderColor='var(--primary)'"
                                        onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb'"><i
                                            class="fa-solid fa-paper-plane"></i></a>
                                @else
                                    <a href="{{ route('admin.documents.index', $student) }}"
                                        style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;border:1px solid #e5e7eb;background:#fff;color:#f59e0b;text-decoration:none;font-size:0.72rem;transition:all 0.15s;"
                                        title="Upload Docs"
                                        onmouseover="this.style.background='#fffbeb';this.style.borderColor='#f59e0b'"
                                        onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb'"><i
                                            class="fa-solid fa-upload"></i></a>
                                @endif
                                <div class="dropdown" style="display:inline-block;">
                                    <button data-bs-toggle="dropdown"
                                        style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;border:1px solid #e5e7eb;background:#fff;color:#6b7280;font-size:0.72rem;cursor:pointer;transition:all 0.15s;padding:0;"
                                        onmouseover="this.style.background='#f9fafb';this.style.borderColor='var(--primary)'"
                                        onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb'"><i
                                            class="fas fa-ellipsis-v" style="font-size:13px;"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0"
                                        style="border-radius:8px;font-size:0.82rem;padding:0.35rem;">
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.students.show', $student) }}"
                                                style="padding:0.4rem 0.7rem;border-radius:4px;font-size:0.78rem;"><i
                                                    class="fas fa-eye me-2" style="color:var(--info);"></i>View</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.students.edit', $student) }}"
                                                style="padding:0.4rem 0.7rem;border-radius:4px;font-size:0.78rem;"><i
                                                    class="fas fa-edit me-2" style="color:var(--warning);"></i>Edit</a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider" style="margin:0.2rem 0;">
                                        </li>
                                        <li><button type="button" class="dropdown-item text-danger btn-delete"
                                                data-url="{{ route('admin.students.destroy', $student->id) }}"
                                                data-name="{{ $student->full_name }}"
                                                style="padding:0.4rem 0.7rem;border-radius:4px;font-size:0.78rem;"><i
                                                    class="fa-solid fa-trash me-2"></i>Delete</button></li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:2.5rem;text-align:center;color:var(--text-muted);"><i
                                class="fa-solid fa-users-slash fa-2x mb-2 d-block" style="opacity:0.3;"></i>No
                            students found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($students->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-center py-3">{{ $students->links() }}</div>
    @endif
</div>
