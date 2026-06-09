@props([
    'student',
    'routePrefix' => 'admin',
    'totalRequiredDocs' => 0,
])
@php
    $uploadedDocs = $student->documents->count();
    $docProgress = $totalRequiredDocs > 0 ? min(100, round(($uploadedDocs / $totalRequiredDocs) * 100)) : 0;
@endphp
<tr>
    <td>
        <div class="d-flex align-items-center gap-2">
            <div class="avatar-initials rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                style="width:32px;height:32px;font-size:0.75rem;font-weight:600;flex-shrink:0;">
                {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}{{ strtoupper(substr($student->last_name ?? '', 0, 1)) }}
            </div>
            <div>
                <a href="{{ route($routePrefix . '.students.show', $student) }}" class="fw-semibold text-decoration-none small">{{ $student->full_name }}</a>
                <div class="text-muted" style="font-size:0.7rem;">{{ $student->email ?? '—' }}</div>
            </div>
        </div>
    </td>
    <td class="small">{{ $student->phone_number ?? '—' }}</td>
    <td class="small">{{ $student->country ?? '—' }}</td>
    <td class="small">{{ $student->course?->title ?? '—' }}</td>
    <td class="small">{{ $student->agent?->business_name ?? '—' }}</td>
    <td>
        <div class="doc-progress-wrap">
            <div class="d-flex justify-content-between small">
                <span>{{ $uploadedDocs }}/{{ $totalRequiredDocs }}</span>
                <span>{{ $docProgress }}%</span>
            </div>
            <div class="doc-progress-bar">
                <div class="doc-progress-fill {{ $docProgress >= 100 ? 'fill-success' : 'fill-warning' }}" style="width:{{ $docProgress }}%"></div>
            </div>
        </div>
    </td>
    <td class="text-end">
        <div class="dropdown">
            <button class="btn btn-sm btn-ghost dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><a class="dropdown-item" href="{{ route($routePrefix . '.students.show', $student) }}"><i class="fas fa-eye me-2"></i>View</a></li>
                @can('update', $student)
                <li><a class="dropdown-item" href="{{ route($routePrefix . '.students.edit', $student) }}"><i class="fas fa-edit me-2"></i>Edit</a></li>
                @endcan
                @can('delete', $student)
                <li><hr class="dropdown-divider"></li>
                <li>
                    <x-confirm-delete
                        url="{{ route($routePrefix . '.students.destroy', $student->id) }}"
                        label="Delete"
                        title="Delete {{ $student->full_name }}?"
                        message="This will permanently delete this student."
                        mode="native"
                        class="dropdown-item text-danger"
                    />
                </li>
                @endcan
            </ul>
        </div>
    </td>
</tr>
