{{-- MODAL 1: CREATE NEW TASK --}}
<div id="newTaskModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <span>➕ Create New Task</span>
            <button type="button" onclick="closeModal('newTaskModal')"
                style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form action="{{ route('crm.tasks.store') }}" method="POST">
            @csrf
            <input type="hidden" name="student_id" value="{{ $student->id }}">
            <div class="modal-body">
                <div class="form-group">
                    <label>Task Title *</label>
                    <input type="text" name="title" class="form-control" required
                        placeholder="e.g., Call student for follow-up">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="Task details..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Task Type *</label>
                            <select name="task_type" class="form-select" required>
                                <option value="call">📞 Call</option>
                                <option value="email">✉️ Email</option>
                                <option value="whatsapp">💬 WhatsApp</option>
                                <option value="meeting">👥 Meeting</option>
                                <option value="follow_up">⏰ Follow Up</option>
                                <option value="counseling">🎓 Counseling</option>
                                <option value="document_review">📄 Document Review</option>
                                <option value="todo">✅ To Do</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Due Date *</label>
                            <input type="date" name="due_date" id="create_task_due_date" class="form-control"
                                value="{{ date('Y-m-d', strtotime('+1 days')) }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Time Slot</label>
                            <select name="time_slot" class="form-select">
                                <option value="">Any time</option>
                                <option value="morning">🌅 Morning (9:00 AM - 12:00 PM)</option>
                                <option value="afternoon">☀️ Afternoon (12:00 PM - 3:00 PM)</option>
                                <option value="evening">🌙 Evening (3:00 PM - 6:00 PM)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Time (optional)</label>
                            <select name="time" class="form-select">
                                <option value="">Auto (5-min slots 9-6)</option>
                                @for($h = 9; $h <= 17; $h++)
                                    @for($m = 0; $m < 60; $m += 5)
                                        @php $val = sprintf('%02d:%02d', $h, $m); $display = date('g:i A', strtotime($val)); @endphp
                                        <option value="{{ $val }}">{{ $display }}</option>
                                    @endfor
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">🟢 Low</option>
                                <option value="medium" selected>🟡 Medium</option>
                                <option value="high">🔴 High</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Assign To</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Myself ({{ auth()->user()->name }})</option>
                        @foreach ($assignableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"
                    onclick="closeModal('newTaskModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Task</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: COMPLETE TASK --}}
<div id="completeModal" class="modal-overlay">
    <div class="modal-content large">
        <div class="modal-header">
            <span>✅ Mark as Done</span>
            <button type="button" onclick="closeModal('completeModal')"
                style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form id="completeTaskForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body">
                <div class="form-group">
                    <label>Task: <strong><span id="completeTaskTitle"></span></strong></label>
                </div>

                <div class="form-group">
                    <label>Write Feedback / Completion Notes</label>
                    <textarea name="completion_note" id="completion_note" rows="3" class="form-control"
                        placeholder="What was accomplished? Any important notes? (Optional)"></textarea>
                </div>

                <div class="form-group">
                    <label>After completion:</label>
                    <div class="d-flex gap-3 flex-wrap">
                        <label class="d-flex align-items-center gap-2">
                            <input type="radio" name="completion_action" value="just_complete" checked>
                            <span class="btn btn-sm btn-success">✓ Done</span>
                        </label>
                        <label class="d-flex align-items-center gap-2">
                            <input type="radio" name="completion_action" value="create_next">
                            <span class="btn btn-sm btn-primary">✓ Done & Schedule Next</span>
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="closeModal('completeModal')">Discard</button>
                    </div>
                </div>

                <div id="newTaskSection"
                    style="display:none; margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div class="form-group">
                        <label style="font-weight: 600;">📋 Schedule Next Activity</label>
                    </div>
                    <div class="form-group">
                        <label>Activity Type *</label>
                        <select name="next_task_type" id="next_task_type" class="form-select">
                            <option value="todo">✅ To-Do</option>
                            <option value="call">📞 Call</option>
                            <option value="email">✉️ Email</option>
                            <option value="whatsapp">💬 WhatsApp</option>
                            <option value="meeting">👥 Meeting</option>
                            <option value="follow_up" selected>⏰ Follow Up</option>
                            <option value="counseling">🎓 Counseling</option>
                            <option value="document_review">📄 Document Review</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Summary *</label>
                        <input type="text" name="next_task_title" id="next_task_title" class="form-control"
                            placeholder="e.g., Follow up on application">
                    </div>
                    <div class="form-group">
                        <label>Log a note...</label>
                        <textarea name="next_task_description" id="next_task_description" rows="2" class="form-control"
                            placeholder="Additional details..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Due Date *</label>
                                <input type="date" name="due_date" id="complete_task_due_date"
                                    class="form-control" value="{{ date('Y-m-d', strtotime('+1 days')) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Time Slot</label>
                                <select name="next_time_slot" id="next_time_slot" class="form-select">
                                    <option value="">Any time</option>
                                    @for($h = 9; $h <= 17; $h++)
                                        @for($m = 0; $m < 60; $m += 5)
                                            @php $val = sprintf('%02d:%02d', $h, $m); $display = date('g:i A', strtotime($val)); @endphp
                                            <option value="{{ $val }}" {{ $h === 9 && $m === 0 ? 'selected' : '' }}>{{ $display }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="next_priority" id="next_priority" class="form-select">
                                    <option value="low">🟢 Low</option>
                                    <option value="medium" selected>🟡 Medium</option>
                                    <option value="high">🔴 High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Assign To</label>
                                <select name="next_assigned_to" id="next_assigned_to" class="form-select">
                                    <option value="">Same assignee</option>
                                    @foreach ($assignableUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"
                    onclick="closeModal('completeModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="completeSubmitBtn">✓ Done</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: RESCHEDULE TASK --}}
<div id="rescheduleModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <span>📅 Reschedule Task</span>
            <button type="button" onclick="closeModal('rescheduleModal')"
                style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form id="rescheduleTaskForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body">
                <div class="form-group">
                    <label>Task: <strong><span id="rescheduleTaskTitle"></span></strong></label>
                </div>

                <div class="form-group">
                    <label>Quick Options</label>
                    <div class="reschedule-buttons">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            onclick="setRescheduleDate(1)">Tomorrow</button>
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            onclick="setRescheduleDate(3)">In 3 days</button>
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            onclick="setRescheduleDate(7)">Next week</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>New Due Date</label>
                    <input type="date" name="due_date" id="reschedule_due_date" class="form-control">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Time Slot</label>
                            <select name="time_slot" id="reschedule_time_slot" class="form-select">
                                <option value="">Any time</option>
                                <option value="morning">🌅 Morning (9:00 AM - 12:00 PM)</option>
                                <option value="afternoon">☀️ Afternoon (12:00 PM - 3:00 PM)</option>
                                <option value="evening">🌙 Evening (3:00 PM - 6:00 PM)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Time (optional)</label>
                            <select name="time" id="reschedule_time" class="form-select">
                                <option value="">Auto (5-min slots 9-6)</option>
                                @for($h = 9; $h <= 17; $h++)
                                    @for($m = 0; $m < 60; $m += 5)
                                        @php $val = sprintf('%02d:%02d', $h, $m); $display = date('g:i A', strtotime($val)); @endphp
                                        <option value="{{ $val }}">{{ $display }}</option>
                                    @endfor
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Reassign to (Optional)</label>
                    <select name="assigned_to" id="reschedule_assigned_to" class="form-select">
                        <option value="">Keep current assignee</option>
                        @foreach ($assignableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Reschedule Reason (Optional)</label>
                    <textarea name="reschedule_reason" rows="2" class="form-control"
                        placeholder="Why is this task being rescheduled?"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"
                    onclick="closeModal('rescheduleModal')">Cancel</button>
                <button type="submit" class="btn btn-warning">Reschedule Task</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: CANCEL TASK --}}
<div id="cancelModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <span>❌ Cancel Task</span>
            <button type="button" onclick="closeModal('cancelModal')"
                style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form id="cancelTaskForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body">
                <div class="form-group">
                    <label>Task: <strong><span id="cancelTaskTitle"></span></strong></label>
                </div>
                <div class="form-group">
                    <label>Cancellation Reason *</label>
                    <textarea name="cancellation_reason" id="cancellation_reason" rows="3" class="form-control" required
                        placeholder="Why is this task being cancelled?"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="closeModal('cancelModal')">Go
                    Back</button>
                <button type="submit" class="btn btn-danger">Cancel Task</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 5: EDIT TASK (Admin Only) --}}
<div id="editTaskModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <span>✏️ Edit Task (Admin Only)</span>
            <button type="button" onclick="closeModal('editTaskModal')"
                style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form id="editTaskForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <input type="hidden" name="task_id" id="edit_task_id">
                <div class="form-group">
                    <label>Task Title *</label>
                    <input type="text" name="title" id="edit_task_title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_task_description" rows="3" class="form-control"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Task Type</label>
                            <select name="task_type" id="edit_task_type" class="form-select">
                                <option value="call">📞 Call</option>
                                <option value="email">✉️ Email</option>
                                <option value="whatsapp">💬 WhatsApp</option>
                                <option value="meeting">👥 Meeting</option>
                                <option value="follow_up">⏰ Follow Up</option>
                                <option value="counseling">🎓 Counseling</option>
                                <option value="document_review">📄 Document Review</option>
                                <option value="todo">✅ To Do</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Due Date</label>
                            <input type="date" name="due_date" id="edit_due_date" class="form-control">
                        </div>
                    </div>
                </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Time Slot</label>
                                <select name="time_slot" id="edit_time_slot" class="form-select">
                                    <option value="">Any time</option>
                                    <option value="morning">🌅 Morning (9:00 AM - 12:00 PM)</option>
                                    <option value="afternoon">☀️ Afternoon (12:00 PM - 3:00 PM)</option>
                                    <option value="evening">🌙 Evening (3:00 PM - 6:00 PM)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Time (optional)</label>
                                <select name="time" id="edit_time" class="form-select">
                                    <option value="">Auto (5-min slots 9-6)</option>
                                    @for($h = 9; $h <= 17; $h++)
                                        @for($m = 0; $m < 60; $m += 5)
                                            @php $val = sprintf('%02d:%02d', $h, $m); $display = date('g:i A', strtotime($val)); @endphp
                                            <option value="{{ $val }}">{{ $display }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" id="edit_priority" class="form-select">
                                    <option value="low">🟢 Low</option>
                                    <option value="medium">🟡 Medium</option>
                                    <option value="high">🔴 High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Assign To</label>
                    <select name="assigned_to" id="edit_assigned_to" class="form-select">
                        <option value="">Unassigned</option>
                        @foreach ($assignableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"
                    onclick="closeModal('editTaskModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Task</button>
            </div>
        </form>
    </div>
</div>


{{-- MODAL 6: LOG NOTE --}}
<div id="logNoteModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <span>📋 Log Activity</span>
            <button onclick="closeModal('logNoteModal')"
                style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form action="{{ route('crm.notes.store') }}" method="POST">
            @csrf
            <input type="hidden" name="student_id" value="{{ $student->id }}">
            <input type="hidden" name="type" value="log">
            <div class="modal-body">
                <div class="form-group">
                    <label>Activity Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Title of the log note">
                </div>
                <div class="form-group">
                    <label>Activity Description *</label>
                    <textarea name="content" rows="4" class="form-control" required
                        placeholder="Describe the activity, call outcome, meeting notes, or important updates..."></textarea>
                </div>
                <div class="text-muted small">This will appear in the Activity Log section only.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"
                    onclick="closeModal('logNoteModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Activity Log</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 7: EDIT NOTE --}}
<div id="editNoteModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <span>✏️ Edit Note</span>
            <button onclick="closeModal('editNoteModal')"
                style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form id="editNoteForm" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="note_id" id="edit_note_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" id="edit_note_content" rows="5" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_pinned" value="1" id="edit_note_pinned">
                        Pin this note
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"
                    onclick="closeModal('editNoteModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Note</button>
            </div>
        </form>
    </div>
</div>

{{-- TAG MODAL --}}
<div id="tagModal" class="tag-modal">
    <div class="tag-modal-content">
        <div class="tag-modal-header">
            <span>Add Tag</span>
            <span class="tag-modal-close" onclick="closeTagModal()">&times;</span>
        </div>
        <div class="tag-input-group">
            <input type="text" id="tagInput" placeholder="Enter tag name..." maxlength="50">
        </div>
        <div class="suggested-tags-list" id="suggestedTagsList"></div>
        <div class="modal-buttons">
            <button class="btn btn-outline-secondary" onclick="closeTagModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveTag()">Add Tag</button>
        </div>
    </div>
</div>
{{-- REVENUE MODAL --}}
<div id="revenueModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <span id="revenueModalTitle">Add Revenue</span>
            <button onclick="closeRevenueModal()"
                style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form id="revenueForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label>Amount *</label>
                    <input type="number" name="amount" id="revenue_amount" class="form-control" step="0.01"
                        required>
                </div>
                <div class="form-group">
                    <label>Payment Method *</label>
                    <select name="method" id="revenue_method" class="form-select" required>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="cheque">Cheque</option>
                        <option value="online_payment">Online Payment</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Transaction Date *</label>
                    <input type="date" name="transaction_date" id="revenue_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Reference Number</label>
                    <input type="text" name="reference_number" id="revenue_reference" class="form-control"
                        placeholder="Transaction ID, Cheque No, etc.">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="revenue_description" rows="3" class="form-control"
                        placeholder="Additional details..."></textarea>
                </div>
                <div class="form-group">
                    <label>Receipt (Optional)</label>
                    <input type="file" name="receipt_file" class="form-control" accept="image/*,.pdf"
                        id="receipt_file_input">
                    <small class="text-muted">Max 5MB. JPG, PNG, PDF</small>

                    {{-- Current receipt display area --}}
                    <div id="current_receipt_container" style="margin-top: 10px; display: none;">
                        <div
                            style="background: #f8f9fa; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span>📎</span>
                                    <span id="current_receipt_filename"
                                        style="font-size: 13px; color: #4a5568;"></span>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="previewReceipt()">
                                        👁️ Preview
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="removeCurrentReceipt()">
                                        ✕ Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"
                    onclick="closeRevenueModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Revenue</button>
            </div>
        </form>
    </div>
</div>

{{-- RECEIPT PREVIEW POPUP MODAL --}}
<div id="receiptPreviewModal" class="modal-overlay" style="display: none; z-index: 10000;">
    <div class="modal-content" style="max-width: 90%; width: auto; max-height: 90%; overflow: hidden;">
        <div class="modal-header">
            <span>📄 Receipt Preview</span>
            <button onclick="closeReceiptPreviewModal()"
                style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 0; text-align: center; overflow: auto; max-height: 80vh;">
            <div id="receipt_preview_content"
                style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                <div style="text-align: center;">
                    <div class="spinner"
                        style="border: 3px solid #f3f3f3; border-top: 3px solid #667eea; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto;">
                    </div>
                    <p>Loading preview...</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary"
                onclick="closeReceiptPreviewModal()">Close</button>
            <button type="button" class="btn btn-primary" id="download_receipt_btn"
                onclick="downloadCurrentReceipt()">
                📥 Download
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .receipt-image {
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
    }

    .receipt-pdf {
        width: 100%;
        height: 70vh;
        border: none;
    }
</style>

{{-- MINI EDIT MODAL --}}
<div id="miniEditModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h5>✏️ Quick Edit Student</h5>
            <button type="button" onclick="closeMiniEditModal()"
                style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form method="POST" id="miniEditForm">
            @csrf
            @method('PUT')

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" id="qe_first_name" class="form-control"
                                required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name" id="qe_last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone_number" id="qe_phone" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="qe_email" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Preferred Country</label>
                            <input type="text" name="preferred_country" id="qe_country" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Applying For</label>
                            <select name="applying_for" class="form-select">
                                <option value="">Select Program Type</option>
                                <option value="Bachelor">Bachelor</option>
                                <option value="Master">Master</option>
                                <option value="Diploma">Diploma</option>
                                <option value="Language Course">Language Course</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Expected Revenue</label>
                            <input type="number" step="0.01" name="expected_revenue" id="qe_expected_revenue"
                                class="form-control">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Tags (comma separated)</label>
                            <input type="text" name="tags" id="qe_tags" class="form-control"
                                placeholder="tag1, tag2, tag3">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"
                    onclick="closeMiniEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
