<script>
    // ========== GLOBAL VARIABLES ==========
    let currentTaskId = null;
    let currentStudentId = {{ $student->id }};
    let historyLoaded = false;
    let currentRevenueId = null;

    // ========== CSRF TOKEN HELPER ==========
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            document.querySelector('input[name="_token"]')?.value;
    }

    // ========== TAB SWITCHING ==========
    function switchTab(name, btn) {
        document.querySelectorAll('[id^="tab-"]').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.crm-tab').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + name).style.display = 'block';
        btn.classList.add('active');
        if (name === 'history') loadHistory();
    }

    function loadHistory() {
        if (historyLoaded) return;
        historyLoaded = true;

        fetch('{{ route('crm.student.history', $student) }}')
            .then(response => response.json())
            .then(data => {
                const el = document.getElementById('stageHistoryContent');
                if (!data.length) {
                    el.innerHTML =
                        '<div class="text-muted text-center py-4 small">No stage changes recorded.</div>';
                    return;
                }
                el.innerHTML = data.map(h => `
                    <div style="display:flex;gap:.75rem;padding:.75rem 0;border-bottom:1px solid #e5e9f2">
                        <span style="font-size:1.1rem">🔄</span>
                        <div>
                            <div style="font-size:.85rem;font-weight:500">${escapeHtml(h.from)} → ${escapeHtml(h.to)}</div>
                            <div style="font-size:.72rem;color:#6b7280">By ${escapeHtml(h.changed_by)} &bull; ${escapeHtml(h.date)}</div>
                        </div>
                    </div>
                `).join('');
            })
            .catch(error => {
                console.error('Error loading history:', error);
                document.getElementById('stageHistoryContent').innerHTML =
                    '<div class="text-danger text-center py-3">Failed to load history.</div>';
            });
    }

    // ========== NOTE FUNCTIONS ==========
    function togglePin(noteId) {
        fetch(`/crm/notes/${noteId}/pin`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    showToast(data.message || 'Failed to pin note', 'error');
                }
            })
            .catch(error => {
                console.error('Error toggling pin:', error);
                showToast('An error occurred', 'error');
            });
    }

    function openEditNoteModal(noteId, content, isPinned) {
        document.getElementById('edit_note_id').value = noteId;
        document.getElementById('edit_note_content').value = content;
        document.getElementById('edit_note_pinned').checked = isPinned;
        document.getElementById('editNoteForm').action = `/crm/notes/${noteId}`;
        document.getElementById('editNoteModal').style.display = 'flex';
    }

    function openLogNoteModal() {
        document.getElementById('logNoteModal').style.display = 'flex';
    }

    // ========== TASK MODAL FUNCTIONS ==========
    function openNewTaskModal() {
        console.log('Opening new task modal');
        const modal = document.getElementById('newTaskModal');
        if (modal) {
            // Set default due date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const dateInput = document.getElementById('create_task_due_date');
            if (dateInput) {
                dateInput.value = tomorrow.toISOString().split('T')[0];
            }
            modal.style.display = 'flex';
        } else {
            console.error('newTaskModal not found');
        }
    }

    function openCompleteModal(taskId, taskTitle) {
        console.log('Opening complete modal for task:', taskId);
        currentTaskId = taskId;
        document.getElementById('completeTaskTitle').innerHTML = escapeHtml(taskTitle);
        document.getElementById('completeTaskForm').action = `/crm/tasks/${taskId}/complete`;

        // Reset form
        document.getElementById('completion_note').value = '';
        document.querySelector('input[name="completion_action"][value="just_complete"]').checked = true;
        document.getElementById('newTaskSection').style.display = 'none';
        document.getElementById('completeSubmitBtn').innerHTML = '✓ Done';
        document.getElementById('completeSubmitBtn').disabled = false;

        // Reset next task form with better defaults
        document.getElementById('next_task_title').value = '';
        document.getElementById('next_task_description').value = '';
        document.getElementById('next_task_type').value = 'follow_up';

        // Set default due date to TOMORROW
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const dueDateInput = document.getElementById('complete_task_due_date');
        if (dueDateInput) {
            dueDateInput.value = tomorrow.toISOString().split('T')[0];
        }

        // Set default time slot to MORNING
        document.getElementById('next_time_slot').value = 'morning';
        document.getElementById('next_priority').value = 'medium';
        document.getElementById('next_assigned_to').value = '';

        const modal = document.getElementById('completeModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function openRescheduleModal(taskId, taskTitle) {
        console.log('Opening reschedule modal for task:', taskId);
        currentTaskId = taskId;
        document.getElementById('rescheduleTaskTitle').innerHTML = escapeHtml(taskTitle);
        document.getElementById('rescheduleTaskForm').action = `/crm/tasks/${taskId}/reschedule`;

        // Set default to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('reschedule_due_date').value = tomorrow.toISOString().split('T')[0];
        document.getElementById('reschedule_time_slot').value = 'morning';
        document.getElementById('reschedule_assigned_to').value = '';

        const modal = document.getElementById('rescheduleModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function openCancelModal(taskId, taskTitle) {
        console.log('Opening cancel modal for task:', taskId);
        currentTaskId = taskId;
        document.getElementById('cancelTaskTitle').innerHTML = escapeHtml(taskTitle);
        document.getElementById('cancelTaskForm').action = `/crm/tasks/${taskId}/cancel`;
        document.getElementById('cancellation_reason').value = '';

        const modal = document.getElementById('cancelModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function openEditModal(taskId) {
        @if (!auth()->user()->is_admin)
            alert('Only administrators can edit tasks.');
            return;
        @endif

        fetch(`/crm/tasks/${taskId}/data`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch task data');
                return response.json();
            })
            .then(task => {
                document.getElementById('edit_task_id').value = task.id;
                document.getElementById('edit_task_title').value = task.subject || '';
                document.getElementById('edit_task_description').value = task.description || '';
                document.getElementById('edit_task_type').value = task.activity_type || 'todo';

                // Handle date properly
                let dueDate = task.scheduled_for;
                if (dueDate && dueDate.includes(' ')) {
                    dueDate = dueDate.split(' ')[0];
                }
                document.getElementById('edit_due_date').value = dueDate || '';
                document.getElementById('edit_time_slot').value = task.priority_time_slot || '';
                // Extract time from scheduled_for if available
                let editTime = '';
                if (dueDate && task.scheduled_for && task.scheduled_for.includes(' ')) {
                    let parts = task.scheduled_for.split(' ');
                    if (parts[1] && parts[1] !== '00:00:00') {
                        editTime = parts[1].substring(0, 5);
                    }
                }
                document.getElementById('edit_time').value = editTime || '';
                document.getElementById('edit_priority').value = task.priority || 'medium';
                document.getElementById('edit_assigned_to').value = task.assigned_to || '';
                document.getElementById('editTaskForm').action = `/crm/tasks/${taskId}`;
                document.getElementById('editTaskModal').style.display = 'flex';
            })
            .catch(error => {
                console.error('Error loading task:', error);
                alert('Failed to load task details. Please try again.');
            });
    }

    function deleteTask(taskId, taskType = null) {
        let title = 'Delete this task?';
        let text = 'This action cannot be undone.';
        if (taskType === 'stage_change') {
            text = 'This is a stage change record. Deleting it will only remove the task, NOT the student.';
        }

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch(`/crm/tasks/${taskId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Task deleted successfully', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast(data.message || 'Failed to delete task', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error deleting task:', error);
                    showToast('Error: ' + error.message, 'error');
                });
        });
    }

    function setRescheduleDate(days) {
        const date = new Date();
        date.setDate(date.getDate() + days);
        const formattedDate = date.toISOString().split('T')[0];
        document.getElementById('reschedule_due_date').value = formattedDate;
    }

    function closeModal(modalId) {
        console.log('Closing modal:', modalId);
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Make functions globally available
    window.openEditNoteModal = openEditNoteModal;
    window.openLogNoteModal = openLogNoteModal;
    window.openNewTaskModal = openNewTaskModal;
    window.openCompleteModal = openCompleteModal;
    window.openRescheduleModal = openRescheduleModal;
    window.openCancelModal = openCancelModal;
    window.openEditModal = openEditModal;
    window.togglePin = togglePin;
    window.setRescheduleDate = setRescheduleDate;
    window.deleteTask = deleteTask;
    window.undoComplete = undoComplete;
    window.undoCancel = undoCancel;
    window.closeModal = closeModal;
    window.closeTagModal = closeTagModal;
    window.openTagModal = openTagModal;
    window.saveTag = saveTag;
    window.removeTagFromCard = removeTagFromCard;
    window.deleteTask = deleteTask;
    window.openEditModal = openEditModal;
    window.undoComplete = undoComplete;
    window.undoCancel = undoCancel;

    // ========== TASK COMPLETION RADIO BUTTON HANDLER ==========
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('input[name="completion_action"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                const newTaskSection = document.getElementById('newTaskSection');
                const submitBtn = document.getElementById('completeSubmitBtn');
                if (this.value === 'create_next') {
                    newTaskSection.style.display = 'block';
                    submitBtn.innerHTML = '✓ Done & Schedule Next';
                    document.getElementById('next_task_title').setAttribute('required',
                        'required');
                    document.getElementById('complete_task_due_date').setAttribute('required',
                        'required');
                } else {
                    newTaskSection.style.display = 'none';
                    submitBtn.innerHTML = '✓ Done';
                    document.getElementById('next_task_title').removeAttribute('required');
                    document.getElementById('complete_task_due_date').removeAttribute(
                        'required');
                }
            });
        });
    });

    // ========== UNDO FUNCTIONS ==========
    function undoComplete(taskId) {
        if (!confirm('Undo mark as completed? This will reopen the task.')) return;

        fetch(`/crm/tasks/${taskId}/undo`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Task reopened successfully', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message || 'Failed to undo', 'error');
                }
            })
            .catch(error => {
                console.error('Error undoing task:', error);
                showToast('An error occurred', 'error');
            });
    }

    function undoCancel(taskId) {
        if (!confirm('Restore this cancelled task?')) return;

        fetch(`/crm/tasks/${taskId}/undo-cancel`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Task restored successfully', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message || 'Failed to restore', 'error');
                }
            })
            .catch(error => {
                console.error('Error restoring task:', error);
                showToast('An error occurred', 'error');
            });
    }

    // ========== TAG MANAGEMENT ==========
    function openTagModal(studentId) {
        currentStudentId = studentId;
        const modal = document.getElementById('tagModal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('active');
            document.getElementById('tagInput').value = '';
            document.getElementById('tagInput').focus();
            loadPopularTags();
        }
    }

    function closeTagModal() {
        const modal = document.getElementById('tagModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('active');
        }
    }

    async function loadPopularTags() {
        try {
            const response = await fetch('{{ route('crm.student.popularTags') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!response.ok) throw new Error('Failed to load tags');
            const data = await response.json();
            const container = document.getElementById('suggestedTagsList');
            if (container && data.tags?.length) {
                container.innerHTML = data.tags.map(tag =>
                    `<span class="suggested-tag" data-tag="${escapeHtml(tag)}">🏷️ ${escapeHtml(tag)}</span>`
                ).join('');
                document.querySelectorAll('.suggested-tag').forEach(tag => {
                    tag.onclick = () => document.getElementById('tagInput').value = tag.dataset.tag;
                });
            } else if (container) {
                container.innerHTML = '<div class="text-muted small">No popular tags found</div>';
            }
        } catch (error) {
            console.error('Error loading popular tags:', error);
            const container = document.getElementById('suggestedTagsList');
            if (container) {
                container.innerHTML = '<div class="text-muted small">Failed to load suggestions</div>';
            }
        }
    }

    async function saveTag() {
        const tag = document.getElementById('tagInput').value.trim();
        if (!tag) {
            showToast('Please enter a tag', 'error');
            return;
        }

        try {
            const response = await fetch(`/crm/students/${currentStudentId}/add-tag`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    tag: tag
                })
            });

            const data = await response.json();
            if (data.success) {
                updateTagsInCard(currentStudentId, data.tags);
                showToast(`Tag "${tag}" added successfully`);
                closeTagModal();
            } else {
                throw new Error(data.error || 'Failed to add tag');
            }
        } catch (error) {
            console.error('Error saving tag:', error);
            showToast(error.message, 'error');
        }
    }

    async function removeTagFromCard(studentId, tag) {
        if (!confirm(`Remove tag "${tag}"?`)) return;

        try {
            const response = await fetch(`/crm/students/${studentId}/remove-tag`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    tag: tag
                })
            });

            const data = await response.json();
            if (data.success) {
                updateTagsInCard(studentId, data.tags);
                showToast(`Tag "${tag}" removed`);
            } else {
                throw new Error(data.error || 'Failed to remove tag');
            }
        } catch (error) {
            console.error('Error removing tag:', error);
            showToast(error.message, 'error');
        }
    }

    function updateTagsInCard(studentId, tags) {
        const container = document.getElementById('studentTagsList');
        if (!container) return;

        if (tags?.length) {
            container.innerHTML = tags.map(tag =>
                `<span class="sc-tag">🏷️ ${escapeHtml(tag)}<button type="button" class="remove-tag-btn" data-tag="${escapeHtml(tag)}">×</button></span>`
            ).join('');

            container.querySelectorAll('.remove-tag-btn').forEach(btn => {
                btn.onclick = (e) => {
                    e.stopPropagation();
                    removeTagFromCard(studentId, btn.dataset.tag);
                };
            });
        } else {
            container.innerHTML = '';
        }
    }

    // ========== REVENUE FUNCTIONS ==========
    // ========== REVENUE FUNCTIONS WITH POPUP PREVIEW ==========

    let currentReceiptUrl = null;
    let currentRevenueIdForReceipt = null;

    function openRevenueModal(studentId, revenueId = null) {
        currentRevenueId = revenueId;
        currentRevenueIdForReceipt = revenueId;
        const modal = document.getElementById('revenueModal');
        const title = document.getElementById('revenueModalTitle');
        const form = document.getElementById('revenueForm');
        const methodInput = document.querySelector('#revenueForm input[name="_method"]');
        const currentReceiptContainer = document.getElementById('current_receipt_container');
        const receiptFileInput = document.getElementById('receipt_file_input');

        // Reset current receipt display
        if (currentReceiptContainer) {
            currentReceiptContainer.style.display = 'none';
        }
        if (receiptFileInput) {
            receiptFileInput.value = '';
        }
        currentReceiptUrl = null;

        if (revenueId) {
            // EDIT MODE
            title.textContent = 'Edit Revenue';
            form.action = `/crm/students/${studentId}/revenues/${revenueId}`;
            if (methodInput) methodInput.value = 'PUT';

            // Show loading state
            showToast('Loading revenue data...', 'info');

            fetch(`/crm/students/${studentId}/revenues/${revenueId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const revenue = data.data;
                        document.getElementById('revenue_amount').value = revenue.amount;
                        document.getElementById('revenue_method').value = revenue.method;
                        let transactionDate = revenue.transaction_date;
                        if (transactionDate && transactionDate.includes(' ')) {
                            transactionDate = transactionDate.split(' ')[0];
                        }
                        document.getElementById('revenue_date').value = transactionDate;
                        document.getElementById('revenue_description').value = revenue.description || '';
                        document.getElementById('revenue_reference').value = revenue.reference_number || '';

                        // Show current receipt if exists
                        if (revenue.receipt_file && revenue.receipt_url) {
                            currentReceiptUrl = revenue.receipt_url;
                            const filename = revenue.receipt_file.split('/').pop();
                            document.getElementById('current_receipt_filename').textContent = filename;
                            if (currentReceiptContainer) {
                                currentReceiptContainer.style.display = 'block';
                            }
                        }
                    } else {
                        showToast(data.message || 'Failed to load revenue data', 'error');
                        closeRevenueModal();
                    }
                })
                .catch(error => {
                    console.error('Error fetching revenue:', error);
                    showToast('Failed to load revenue data', 'error');
                    closeRevenueModal();
                });
        } else {
            // ADD MODE
            title.textContent = 'Add Revenue';
            form.reset();
            form.action = `/crm/students/${studentId}/revenues`;
            if (methodInput) methodInput.value = 'POST';
            document.getElementById('revenue_date').value = new Date().toISOString().split('T')[0];
        }

        modal.style.display = 'flex';
    }

    function closeRevenueModal() {
        const modal = document.getElementById('revenueModal');
        if (modal) modal.style.display = 'none';
        currentReceiptUrl = null;
    }

    function previewReceipt() {
        if (!currentReceiptUrl) {
            showToast('No receipt available to preview', 'warning');
            return;
        }

        const previewModal = document.getElementById('receiptPreviewModal');
        const previewContent = document.getElementById('receipt_preview_content');

        if (!previewModal || !previewContent) return;

        // Show loading
        previewContent.innerHTML = `
        <div style="text-align: center;">
            <div class="spinner" style="border: 3px solid #f3f3f3; border-top: 3px solid #667eea; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto;"></div>
            <p>Loading preview...</p>
        </div>
    `;

        previewModal.style.display = 'flex';

        // Determine file type and display accordingly
        const fileExtension = currentReceiptUrl.split('.').pop().toLowerCase();

        if (fileExtension === 'pdf') {
            previewContent.innerHTML = `
            <iframe src="${currentReceiptUrl}" class="receipt-pdf" style="width: 100%; height: 70vh; border: none;"></iframe>
        `;
        } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension)) {
            const img = new Image();
            img.onload = function() {
                previewContent.innerHTML = `
                <img src="${currentReceiptUrl}" class="receipt-image" alt="Receipt Preview" style="max-width: 100%; max-height: 70vh; object-fit: contain;">
            `;
            };
            img.onerror = function() {
                previewContent.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <span style="font-size: 48px;">🖼️</span>
                    <p>Failed to load image. <a href="${currentReceiptUrl}" target="_blank">Open directly</a></p>
                </div>
            `;
            };
            img.src = currentReceiptUrl;
        } else {
            previewContent.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <span style="font-size: 48px;">📄</span>
                <p>Preview not available for this file type.</p>
                <a href="${currentReceiptUrl}" target="_blank" class="btn btn-primary mt-2">Download File</a>
            </div>
        `;
        }
    }

    function closeReceiptPreviewModal() {
        const modal = document.getElementById('receiptPreviewModal');
        if (modal) modal.style.display = 'none';
    }

    function downloadCurrentReceipt() {
        if (!currentReceiptUrl) {
            showToast('No receipt available to download', 'warning');
            return;
        }

        // Create temporary link to download
        const link = document.createElement('a');
        link.href = currentReceiptUrl;
        link.download = currentReceiptUrl.split('/').pop();
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showToast('Download started...', 'success');
    }

    function removeCurrentReceipt() {
        if (!confirm('Remove the current receipt? You can upload a new one.')) {
            return;
        }

        // Clear the receipt display
        const container = document.getElementById('current_receipt_container');
        if (container) {
            container.style.display = 'none';
        }
        currentReceiptUrl = null;

        // Add a hidden input to mark receipt for deletion
        const form = document.getElementById('revenueForm');
        let removeReceiptInput = form.querySelector('input[name="remove_receipt"]');
        if (!removeReceiptInput) {
            removeReceiptInput = document.createElement('input');
            removeReceiptInput.type = 'hidden';
            removeReceiptInput.name = 'remove_receipt';
            form.appendChild(removeReceiptInput);
        }
        removeReceiptInput.value = '1';

        showToast('Receipt marked for removal', 'info');
    }

    function deleteRevenue(studentId, revenueId, amount) {
        if (!confirm(`Delete revenue of ${amount}? This action cannot be undone.`)) {
            return;
        }

        fetch(`/crm/students/${studentId}/revenues/${revenueId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Revenue deleted successfully');
                    // Remove the revenue item from DOM
                    const revenueItem = document.querySelector(`.revenue-item[data-revenue-id="${revenueId}"]`);
                    if (revenueItem) {
                        revenueItem.remove();
                        if (data.student) {
                            updateRevenueDisplay(data.student);
                        }
                        const revenueList = document.querySelector('.revenue-list');
                        if (revenueList && revenueList.querySelectorAll('.revenue-item').length === 0) {
                            const emptyMessage = document.querySelector('.revenue-list .text-muted');
                            if (!emptyMessage) {
                                revenueList.innerHTML +=
                                    '<div class="text-muted text-center py-3">No revenue records yet.</div>';
                            }
                        }
                    } else {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else {
                    showToast(data.message || 'Failed to delete revenue', 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting revenue:', error);
                showToast('An error occurred: ' + error.message, 'error');
            });
    }

    // Revenue form submission handler with receipt removal support
    document.addEventListener('DOMContentLoaded', function() {
        const revenueForm = document.getElementById('revenueForm');
        if (revenueForm) {
            revenueForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Show loading state on submit button
                const submitBtn = revenueForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Saving...';

                const formData = new FormData(this);
                const url = this.action;

                fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;

                        if (data.success) {
                            showToast(data.message);
                            closeRevenueModal();
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showToast(data.message || 'Error saving revenue', 'error');
                        }
                    })
                    .catch(error => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        console.error('Error saving revenue:', error);
                        showToast('An error occurred: ' + error.message, 'error');
                    });
            });
        }
    });

    function updateRevenueDisplay(studentData) {
        const statsValues = document.querySelectorAll('.revenue-stats-value');
        if (statsValues.length >= 3) {
            statsValues[0].textContent = `$${parseFloat(studentData.expected_revenue || 0).toFixed(2)}`;
            statsValues[1].textContent = `$${parseFloat(studentData.received_revenue || 0).toFixed(2)}`;
            statsValues[2].textContent = `$${parseFloat(studentData.remaining_due || 0).toFixed(2)}`;
        }
    }
    // ========== RECEIPT PREVIEW FROM LIST ==========

    // ========== RECEIPT PREVIEW FROM LIST ==========
    function viewReceiptFromList(receiptUrl, filename) {
        if (!receiptUrl) {
            showToast('No receipt available to preview', 'warning');
            return;
        }

        currentReceiptUrl = receiptUrl;
        const previewModal = document.getElementById('receiptPreviewModal');
        const previewContent = document.getElementById('receipt_preview_content');

        if (!previewModal || !previewContent) return;

        // Show loading
        previewContent.innerHTML = `
        <div style="text-align: center;">
            <div class="spinner" style="border: 3px solid #f3f3f3; border-top: 3px solid #667eea; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto;"></div>
            <p>Loading preview...</p>
        </div>
    `;

        previewModal.style.display = 'flex';

        // Determine file type from URL or filename
        const fileExtension = filename.split('.').pop().toLowerCase();

        if (fileExtension === 'pdf') {
            // For PDF, embed using iframe
            previewContent.innerHTML = `
            <iframe src="${receiptUrl}" class="receipt-pdf" style="width: 100%; height: 70vh; border: none;"></iframe>
        `;
        } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(fileExtension)) {
            // For images, create img tag
            const img = new Image();
            img.onload = function() {
                previewContent.innerHTML = `
                <img src="${receiptUrl}" class="receipt-image" alt="Receipt Preview" style="max-width: 100%; max-height: 70vh; object-fit: contain;">
            `;
            };
            img.onerror = function() {
                previewContent.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <span style="font-size: 48px;">🖼️</span>
                    <p>Failed to load image. <a href="${receiptUrl}" target="_blank">Open directly</a></p>
                </div>
            `;
            };
            img.src = receiptUrl;
        } else {
            previewContent.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <span style="font-size: 48px;">📄</span>
                <p>Preview not available for this file type.</p>
                <p style="font-size: 12px; color: #666;">File: ${filename}</p>
                <a href="${receiptUrl}" target="_blank" class="btn btn-primary mt-2">Download File</a>
            </div>
        `;
        }
    }

    // ========== MINI EDIT MODAL ==========
    function openMiniEditModal() {
        const student = @json($student);
        const modal = document.getElementById('miniEditModal');
        modal.classList.remove('d-none');
        modal.style.display = 'flex';

        document.getElementById('miniEditForm').action = `/crm/students/${student.id}/mini-update`;
        document.getElementById('qe_first_name').value = student.first_name ?? '';
        document.getElementById('qe_last_name').value = student.last_name ?? '';
        document.getElementById('qe_phone').value = student.phone_number ?? '';
        document.getElementById('qe_email').value = student.email ?? '';
        document.getElementById('qe_country').value = student.preferred_country ?? '';
        document.getElementById('qe_expected_revenue').value = student.expected_revenue ?? '';
        document.getElementById('qe_tags').value = Array.isArray(student.tags) ? student.tags.join(', ') : (student
            .tags ?? '');
    }

    function closeMiniEditModal() {
        const modal = document.getElementById('miniEditModal');
        modal.classList.add('d-none');
        modal.style.display = 'none';
    }

    // ========== HELPER FUNCTIONS ==========
    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function showToast(message, type = 'success') {
        let toast = document.getElementById('crmToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'crmToast';
            toast.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 8px;
                color: white;
                font-size: 14px;
                z-index: 10000;
                opacity: 0;
                transition: opacity 0.3s ease;
                pointer-events: none;
            `;
            document.body.appendChild(toast);
        }

        toast.style.backgroundColor = type === 'error' ? '#ef4444' : '#10b981';
        toast.textContent = message;
        toast.style.opacity = '1';

        setTimeout(() => {
            toast.style.opacity = '0';
        }, 3000);
    }

    // ========== CLOSE MODALS ON OUTSIDE CLICK ==========
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
        if (event.target.classList.contains('tag-modal')) {
            closeTagModal();
        }
    }

    // Prevent modal content clicks from bubbling to overlay
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.modal-content').forEach(modalContent => {
            modalContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    });

    // ========== Duplication check==========
    let lastSubmissionTime = 0;
    let isSubmitting = false;

    function createTaskWithDuplicationCheck(formData) {
        // Prevent rapid successive submissions
        const now = Date.now();
        if (now - lastSubmissionTime < 3000) {
            showToast('Please wait before creating another task', 'warning');
            return false;
        }

        // Prevent double submission
        if (isSubmitting) {
            showToast('Task creation in progress...', 'warning');
            return false;
        }

        isSubmitting = true;
        lastSubmissionTime = now;

        // Check for recent similar tasks via AJAX
        const studentId = formData.get('student_id');
        const title = formData.get('title');

        fetch(`/crm/tasks/check-duplicate/${studentId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    title: title,
                    minutes: 5
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.has_duplicate) {
                    if (confirm(
                            `A similar task "${data.existing_task.subject}" was created recently. Do you want to create another one?`
                        )) {
                        submitTaskForm(formData);
                    } else {
                        isSubmitting = false;
                    }
                } else {
                    submitTaskForm(formData);
                }
            })
            .catch(error => {
                console.error('Duplicate check failed:', error);
                submitTaskForm(formData); // Proceed if check fails
            });

        return false;
    }

    function submitTaskForm(formData) {
        fetch('/crm/tasks', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                isSubmitting = false;
                if (data.success) {
                    showToast('Task created successfully');
                    location.reload();
                } else {
                    showToast(data.message || 'Failed to create task', 'error');
                }
            })
            .catch(error => {
                isSubmitting = false;
                showToast('Error creating task', 'error');
            });
    }

    // ========== INITIALIZE REMOVE TAG BUTTONS ==========
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.remove-tag-btn').forEach(btn => {
            btn.onclick = (e) => {
                e.stopPropagation();
                const tag = btn.dataset.tag;
                if (tag) {
                    removeTagFromCard(currentStudentId, tag);
                }
            };
        });
    });

    // ========== STUDENT RATING ==========
    function updateStudentRating(r) {
        fetch('/crm/student/' + currentStudentId + '/update-rating', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
            body: JSON.stringify({ rating: r })
        })
        .then(res => res.json())
        .then(d => {
            if (d.success) {
                showToast(d.message || 'Rating updated');
                document.querySelectorAll('#studentStarRating input').forEach(el => {
                    el.checked = parseInt(el.value) === r;
                });
            } else showToast(d.error || 'Failed', 'error');
        })
        .catch(() => showToast('Error updating rating', 'error'));
    }
</script>
