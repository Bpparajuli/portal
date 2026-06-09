@extends('layouts.app')

@php $role = auth()->user()->role; @endphp

@section('title', 'Applications')

@section('content')
    <div class="container-fluid px-3 py-3">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h4 class="fw-bold mb-0"><i class="fas fa-file-alt text-primary me-2"></i>Applications</h4>
            <div class="d-flex gap-2">
                @can('create', App\Models\Application::class)
                    <a href="{{ route($role . '.applications.create') }}" class="btn btn-primary btn-sm"><i
                            class="fas fa-plus me-1"></i>New Application</a>
                @endcan
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-3 col-6">
                <div class="stat-card-app p-3 border rounded bg-white">
                    <div class="lbl small text-muted text-uppercase">Total</div>
                    <div class="num fs-3 fw-bold text-primary">{{ $applications->total() }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card-app p-3 border rounded bg-white">
                    <div class="lbl small text-muted text-uppercase">Accepted</div>
                    <div class="num fs-3 fw-bold text-success">{{ $acceptedCount ?? 0 }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card-app p-3 border rounded bg-white">
                    <div class="lbl small text-muted text-uppercase">Rejected</div>
                    <div class="num fs-3 fw-bold text-danger">{{ $rejectedCount ?? 0 }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card-app p-3 border rounded bg-white">
                    <div class="lbl small text-muted text-uppercase">Lost</div>
                    <div class="num fs-3 fw-bold text-secondary">{{ $lostCount ?? 0 }}</div>
                </div>
            </div>
        </div>

        @include('shared.applications._filters')

        <div class="table-responsive bg-white rounded border">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Agent</th>
                        <th>University</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                        <tr>
                            <td class="text-muted small">{{ $app->id ?? $app->application_number }}</td>
                            <td><a href="{{ route($role . '.students.show', $app->student_id) }}"
                                    class="text-decoration-none fw-semibold">{{ $app->student?->full_name ?? '—' }}</a></td>
                            <td>{{ $app->agent?->business_name ?? '—' }}</td>
                            <td>{{ $app->university?->short_name ?? ($app->university?->name ?? '—') }}</td>
                            <td class="small">{{ $app->course?->title ?? '—' }}</td>
                            <td>
                                <a class="dropdown-item" href="{{ route($role . '.applications.show', $app->id) }}">
                                    <span class="badge"
                                        style="background:{{ $app->status?->bg_color ?? '#6c757d' }};">{{ $app->status?->name ?? '—' }}</span>
                                </a>
                            </td>
                            <td class="small text-muted">{{ $app->created_at->format('d M Y') }}</td>
                            <td class="text-end">

                                <div class="dropdown" style="display:inline-block;">
                                    <button data-bs-toggle="dropdown"
                                        style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;border:1px solid #e5e7eb;background:#fff;color:#6b7280;font-size:0.72rem;cursor:pointer;transition:all 0.15s;padding:0;"
                                        onmouseover="this.style.background='#f9fafb';this.style.borderColor='var(--primary)'"
                                        onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb'"><i
                                            class="fas fa-ellipsis-v" style="font-size:13px;"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0"
                                        style="border-radius:8px;font-size:0.82rem;padding:0.35rem;">
                                        <li><a class="dropdown-item"
                                                href="{{ route($role . '.applications.show', $app->id) }}"
                                                style="padding:0.4rem 0.7rem;border-radius:4px;font-size:0.78rem;"><i
                                                    class="fas fa-eye me-2" style="color:var(--info);"></i>View</a></li>
                                        @can('update', $app)
                                        <li><a class="dropdown-item"
                                                href="{{ route($role . '.applications.edit', $app->id) }}"
                                                style="padding:0.4rem 0.7rem;border-radius:4px;font-size:0.78rem;"><i
                                                    class="fas fa-edit me-2" style="color:var(--warning);"></i>Edit</a>
                                        </li>
                                        @endcan
                                        @can('withdraw', $app)
                                        <li>
                                            <form action="{{ route($role . '.applications.withdraw', $app) }}" method="POST" style="display:inline;">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="dropdown-item text-danger"
                                                        style="padding:0.4rem 0.7rem;border-radius:4px;font-size:0.78rem;"
                                                        onclick="return confirm('Withdraw application for {{ $app->student?->full_name ?? '—' }}?')">
                                                    <i class="fas fa-ban me-2" style="color:var(--danger);"></i>Withdraw
                                                </button>
                                            </form>
                                        </li>
                                        @endcan
                                        @can('delete', $app)
                                        <li>
                                            <hr class="dropdown-divider" style="margin:0.2rem 0;">
                                        </li>
                                        <li>
                                            <x-confirm-delete action="{{ $role }}.applications.destroy"
                                                :id="$app->id" label="Delete"
                                                title="Delete Application of: {{ $app->student?->full_name ?? '—' }} for {{ $app->course?->title ?? '—' }} at {{ $app->university?->short_name ?? ($app->university?->name ?? '—') }}"
                                                message="This will permanently delete this Application."
                                                class="btn btn-sm btn-outline-danger" />
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No applications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $applications->appends(request()->query())->links() }}</div>
    </div>
@endsection
