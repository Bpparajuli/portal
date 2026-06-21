@push('styles')
    <style>
        .crm-modal .modal-content { border-radius: var(--hd-radius); border: none; }
        .crm-modal .modal-header { padding: var(--hd-md) var(--hd-lg); border-bottom: 1px solid #f3f4f6; }
        .crm-modal .modal-header .modal-title { font-size: var(--hd-font-md); font-weight: 600; }
        .crm-modal .modal-body { padding: var(--hd-lg); }
        .crm-modal .modal-footer { padding: var(--hd-sm) var(--hd-lg); border-top: 1px solid #f3f4f6; }
        .crm-modal .form-label { font-size: var(--hd-font-sm); margin-bottom: 2px; }
    </style>
@endpush

<div class="modal fade crm-modal" id="tagModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tag me-1"></i>Add Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size:.7rem;"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="tagInput" class="form-control" placeholder="Enter tag..." maxlength="50">
                <div class="mt-2">
                    <div style="font-size:var(--hd-font-xs);color:#6b7280;margin-bottom:var(--hd-sm);">Popular</div>
                    <div id="suggestedTagsList" class="d-flex flex-wrap gap-1"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" id="saveTagBtn">Add</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade crm-modal" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-1"></i>Add Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size:.7rem;"></button>
            </div>
            <form action="{{ route('crm.student.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="preferred_country" class="form-control" placeholder="e.g., Canada">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Source</label>
                            <input type="text" name="source" class="form-control" placeholder="Walk-in, Facebook, etc.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stage *</label>
                            <select name="current_stage_id" class="form-select" required>
                                @foreach ($stages as $stage)
                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agent</label>
                            <select name="agent_id" class="form-select">
                                <option value="">Optional</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->business_name ?? $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade crm-modal" id="addStudentToColModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-1"></i>Add to <span id="colStageName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size:.7rem;"></button>
            </div>
            <form method="POST" action="{{ route('crm.student.store') }}">
                @csrf
                <input type="hidden" name="current_stage_id" id="colStageId">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="preferred_country" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Source</label>
                            <input type="text" name="source" class="form-control" placeholder="Walk-in, Facebook, etc.">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Agent</label>
                            <select name="agent_id" class="form-select">
                                <option value="">Optional</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->business_name ?? $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddStudentModal(stageId, stageName) {
    document.getElementById('colStageId').value = stageId;
    document.getElementById('colStageName').innerText = stageName;
    new bootstrap.Modal(document.getElementById('addStudentToColModal')).show();
}
</script>
