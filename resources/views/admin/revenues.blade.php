@extends('layouts.admin')
@section('admin-content')
    <div class="container-fluid px-4 py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button"
                    class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <x-page-header title="Collected Revenue" subtitle="View, edit, and manage revenue transactions">
            </x-page-header>
            <div class="d-flex align-items-center gap-3">
                <div class="card border-0 shadow-sm rounded-4 bg-success text-white mb-0">
                    <div class="card-body p-3 px-4">
                        <div class="d-flex align-items-center gap-3">
                            <h6 class="mb-0 text-white-50 small fw-semibold">Grand Total</h6>
                            <h4 class="mb-0 fw-bold">{{ number_format($grandTotal, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addRevenueModal">
                    <i class="fas fa-plus me-1"></i> Add Revenue
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('admin.revenues.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3"><label class="form-label small fw-semibold mb-1">Search Student</label><input
                                type="text" name="search" class="form-control form-control-sm"
                                value="{{ request('search') }}" placeholder="Name..."></div>
                        <div class="col-md-2"><label class="form-label small fw-semibold mb-1">Date From</label><input
                                type="date" name="date_from" class="form-control form-control-sm"
                                value="{{ request('date_from') }}"></div>
                        <div class="col-md-2"><label class="form-label small fw-semibold mb-1">Date To</label><input
                                type="date" name="date_to" class="form-control form-control-sm"
                                value="{{ request('date_to') }}"></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold mb-1">Agent</label>
                            <select name="agent" class="form-select form-select-sm">
                                <option value="">All Agents</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}"
                                        {{ request('agent') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->business_name ?? $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm w-100"><i
                                    class="fas fa-filter me-1"></i> Filter</button></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small">
                            <tr>
                                <th class="px-3">Student</th>
                                <th>Agent</th>
                                <th>Method</th>
                                <th class="text-end">Amount</th>
                                <th>Description</th>
                                <th>Payment Date</th>
                                <th class="text-center">Receipt</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($revenues as $rev)
                                <tr>
                                    <td class="px-3"><a href="{{ route('admin.students.show', $rev->student_id) }}"
                                            class="text-decoration-none fw-semibold text-dark">{{ $rev->student?->first_name }}
                                            {{ $rev->student?->last_name }}</a></td>
                                    <td>
                                        @if ($rev->student?->agent)
                                            <a href="{{ route('admin.users.show', $rev->student->agent->slug ?? $rev->student->agent->id) }}"
                                                class="text-decoration-none text-secondary small">{{ $rev->student->agent->business_name ?? $rev->student->agent->name }}</a>
                                        @endif
                                    </td>
                                    <td class="small text-capitalize">{{ str_replace('_', ' ', $rev->method ?? '—') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($rev->amount, 2) }}</td>
                                    <td class="small text-muted"
                                        style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        {{ $rev->description }}</td>
                                    <td>{{ $rev->transaction_date?->format('d M Y') }}</td>
                                    <td class="text-center">
                                        @if ($rev->receipt_file)
                                            <a href="{{ route('receipt.view', $rev->receipt_file) }}" target="_blank"
                                                class="btn btn-sm btn-outline-secondary"><i class="fas fa-file"></i></a>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                            onclick="editRevenue({{ $rev->id }})"><i class="fas fa-edit"></i></button>
                                        <x-confirm-delete action="admin.revenues.destroy" :id="$rev->id"
                                            title="Delete Payment made of {{ number_format($rev->amount, 2) }}?"
                                            message="This action cannot be undone." class="btn btn-sm btn-outline-danger" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted"><i
                                            class="fas fa-inbox fa-3x mb-3"></i>
                                        <p class="mb-0">No revenues found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($revenues->count())
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="px-3 text-end text-uppercase small text-muted">Filtered Total
                                    </td>
                                    <td class="text-end">{{ number_format($filteredTotal, 2) }}</td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            @if ($revenues->hasPages())
                <div class="card-footer bg-white border-0 pt-3">
                    <div class="d-flex justify-content-center">{{ $revenues->withQueryString()->links() }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Add Revenue Modal --}}
    <div class="modal fade" id="addRevenueModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.revenues.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-success"></i>Add Revenue</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                                <select name="student_id" class="form-select" required>
                                    <option value="">Select Student</option>
                                    @foreach($students as $s)
                                        <option value="{{ $s->id }}">{{ $s->full_name }} @if($s->agent) ({{ $s->agent->business_name ?? $s->agent->name }}) @endif</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                <select name="method" class="form-select" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="online_payment">Online Payment</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Reference Number</label>
                                <input type="text" name="reference_number" class="form-control" placeholder="Transaction ID, Cheque No, etc.">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Receipt (Optional)</label>
                                <input type="file" name="receipt_file" class="form-control" accept="image/*,.pdf">
                                <small class="text-muted">Max 5MB. JPG, PNG, PDF</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Additional details..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save Revenue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Revenue Modal --}}
    <div class="modal fade" id="editRevenueModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="editRevenueForm">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Revenue</h5><button type="button"
                            class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Amount</label><input type="number" step="0.01"
                                name="amount" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3"><label class="form-label">Transaction Date</label><input type="date"
                                name="transaction_date" class="form-control" required></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editRevenue(id) {
                fetch(`/admin/revenues/${id}/edit`)
                    .then(r => r.json())
                    .then(data => {
                        const form = document.getElementById('editRevenueForm');
                        form.action = `/admin/revenues/${id}`;
                        form.querySelector('[name="amount"]').value = data.amount;
                        form.querySelector('[name="description"]').value = data.description || '';
                        form.querySelector('[name="transaction_date"]').value = data.transaction_date ? data
                            .transaction_date.substring(0, 10) : '';
                        new bootstrap.Modal(document.getElementById('editRevenueModal')).show();
                    });
            }
        </script>
    @endpush
@endsection