{{-- resources/views/crm/components/modals.blade.php --}}

<!-- Task Detail Modal -->
<div class="modal fade" id="taskDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tasks me-2"></i>Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="taskDetailBody">
                <div class="text-center py-4">
                    <div class="loader-spinner" style="width:30px;height:30px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="taskDetailLink" class="btn btn-primary">View Student Profile</a>
            </div>
        </div>
    </div>
</div>

<!-- Tag Modal -->
<div class="modal fade" id="tagModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tag me-2"></i>Add Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="tagInput" class="form-control" placeholder="Enter tag name..."
                    maxlength="50">
                <div class="mt-3">
                    <label class="small text-muted">Popular tags</label>
                    <div id="suggestedTagsList" class="d-flex flex-wrap gap-2 mt-2"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTagBtn">Add Tag</button>
            </div>
        </div>
    </div>
</div>
<!-- Add Student Modal - Source as free text input -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('crm.student.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
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
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Preferred Country</label>
                            <input type="text" name="preferred_country" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Source / Lead Channel</label>
                            <input type="text" name="source" class="form-control"
                                placeholder="e.g., Walk-in, Facebook, Google, Referral from John, etc.">
                            <small class="text-muted">Leave empty for default "manual"</small>
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
                            <label class="form-label">Assign to Agent</label>
                            <select name="agent_id" class="form-select">
                                <option value="">Select agent (optional)</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->business_name ?? $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Student to Column Modal - Source as free text -->
<div class="modal fade" id="addStudentToColModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Student to <span
                        id="colStageName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('crm.student.store') }}">
                @csrf
                <input type="hidden" name="current_stage_id" id="colStageId">
                <div class="modal-body">
                    <div class="row g-3">
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
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Preferred Country</label>
                            <input type="text" name="preferred_country" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Source / Lead Channel</label>
                            <input type="text" name="source" class="form-control"
                                placeholder="e.g., Walk-in, Facebook, Referral from Sarah, etc.">
                            <small class="text-muted">Leave empty for default "manual"</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Assign to Agent</label>
                            <select name="agent_id" class="form-select">
                                <option value="">Select agent (optional)</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->business_name ?? $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Student</button>
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
