{{-- resources/views/crm/components/dashboard/list-view.blade.php --}}

@push('styles')
    <style>
        /* Rating Stars */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 2px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 18px;
            color: #d1d5db;
            cursor: pointer;
        }

        .star-rating input:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #fbbf24;
        }

        /* Tags Styles */
        .tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
            margin: 0.5rem 0 0.3rem;
        }

        .tag {
            font-size: 0.65rem;
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 12px;
            padding: 0.15rem 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .tag-remove {
            cursor: pointer;
            font-weight: bold;
            margin-left: 0.2rem;
            opacity: 0.7;
        }

        .tag-remove:hover {
            opacity: 1;
        }

        .add-tag-btn {
            font-size: 0.65rem;
            background: transparent;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 0.15rem 0.5rem;
            cursor: pointer;
            color: #6b7280;
            width: 100%;
            text-align: center;
            margin-top: 5px;
        }

        .add-tag-btn:hover {
            background: #4f46e5;
            color: white;
        }
    </style>
@endpush

<div class="list-view-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student Name</th>
                    <th>Contact</th>
                    <th>Country</th>
                    <th>Stage</th>
                    @if ($isAdmin)
                        <th>Assigned To</th>
                    @endif
                    <th>Tasks</th>
                    <th>Rating</th>
                    <th>Tags</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students ?? [] as $student)
                    <tr>
                        <td class="fw-semibold">
                            <a href="{{ route('crm.student.show', $student) }}" class="text-decoration-none text-dark">
                                {{ $student->full_name }}
                            </a>
                        </td>
                        <td>
                            <div class="small">{{ $student->phone_number ?? '—' }}</div>
                            <div class="small text-muted">{{ $student->email ?? '—' }}</div>
                        </td>
                        <td>{{ $student->preferred_country ?? '—' }}</td>
                        <td>
                            <span class="badge" style="background: {{ $student->currentStage?->color ?? '#6b7280' }}">
                                {{ $student->currentStage?->name ?? 'Unknown' }}
                            </span>
                        </td>
                        @if ($isAdmin)
                            <td>
                                @php
                                    // Get unique assignees from all pending tasks
                                    $assignees = $student->pendingActivities->pluck('assignee')->filter()->unique('id');
                                @endphp
                                @if ($assignees->count() > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($assignees as $assignee)
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-user-tie me-1"></i> {{ $assignee->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted">Unassigned</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            <div class="d-flex flex-column gap-1">
                                @php
                                    $overdueCount = $student->overdueActivities->count();
                                    $upcomingCount = $student->upcomingActivities->count();
                                    $completedTodayCount = $student
                                        ->activities()
                                        ->where('status', 'completed')
                                        ->whereDate('completed_at', today())
                                        ->count();
                                @endphp
                                @if ($overdueCount > 0)
                                    <span class="badge bg-danger">{{ $overdueCount }} Overdue</span>
                                @endif
                                @if ($upcomingCount > 0)
                                    <span class="badge bg-success">{{ $upcomingCount }} Upcoming</span>
                                @endif
                                @if ($completedTodayCount > 0)
                                    <span class="badge bg-info">✓ {{ $completedTodayCount }} Completed Today</span>
                                @endif
                                @if ($overdueCount == 0 && $upcomingCount == 0 && $completedTodayCount == 0)
                                    <span class="badge bg-light text-dark">No tasks</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <form class="rating-form" action="{{ route('crm.dashboard.updateRating', $student->id) }}"
                                method="POST">
                                @csrf @method('PUT')
                                <div class="star-rating">
                                    <input type="radio" name="rating" id="list_star3_{{ $student->id }}"
                                        value="3" {{ ($student->rating ?? '') == 3 ? 'checked' : '' }}>
                                    <label for="list_star3_{{ $student->id }}">★</label>
                                    <input type="radio" name="rating" id="list_star2_{{ $student->id }}"
                                        value="2" {{ ($student->rating ?? '') == 2 ? 'checked' : '' }}>
                                    <label for="list_star2_{{ $student->id }}">★</label>
                                    <input type="radio" name="rating" id="list_star1_{{ $student->id }}"
                                        value="1" {{ ($student->rating ?? '') == 1 ? 'checked' : '' }}>
                                    <label for="list_star1_{{ $student->id }}">★</label>
                                </div>
                            </form>
                        </td>
                        <td>
                            <div class="d-flex flex-column flex-wrap gap-2 tags-list">
                                @if ($student->tags && is_array($student->tags))
                                    @foreach ($student->tags as $tag)
                                        <span class="tag">
                                            <i class="fas fa-tag"></i> {{ $tag }}
                                            <span class="tag-remove" data-tag="{{ $tag }}"
                                                onclick="event.stopPropagation(); window.CrmListHelper.removeTag({{ $student->id }}, '{{ addslashes($tag) }}')">×</span>
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="add-tag-btn" data-student-id="{{ $student->id }}">
                                <i class="fas fa-plus"></i> Add tag
                            </button>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <a href="{{ route('crm.student.show', $student) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('crm.student.edit', $student) }}"
                                    class="btn btn-sm btn-outline-dark">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 9 : 8 }}" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No students found with the current filters
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if (isset($students) && method_exists($students, 'links'))
        <div class="p-3">{{ $students->withQueryString()->links() }}</div>
    @endif
</div>

@push('scripts')
    <script>
        // ============================================
        // LIST VIEW HELPER - No conflicts with other modules
        // ============================================
        window.CrmListHelper = (function() {
            'use strict';

            const CRM = window.CrmCore?.getInstance();
            let currentStudentId = null;

            function showLoader() {
                CRM?.showLoader();
            }

            function hideLoader() {
                CRM?.hideLoader();
            }

            function showToast(msg, type) {
                CRM?.showToast(msg, type);
            }

            function getCsrfToken() {
                return CRM ? CRM.getCsrfToken() : document.querySelector('meta[name="csrf-token"]')?.content;
            }

            function escapeHtml(str) {
                return CRM ? CRM.escapeHtml(str) : (str ? String(str) : '');
            }

            function openTagModal(id) {
                currentStudentId = id;
                const modal = document.getElementById('tagModal');
                if (modal) {
                    new bootstrap.Modal(modal).show();
                    const tagInput = document.getElementById('tagInput');
                    if (tagInput) tagInput.value = '';
                    loadPopularTags();
                }
            }

            async function loadPopularTags() {
                try {
                    const response = await fetch('{{ route('crm.student.popularTags') }}', {
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken()
                        }
                    });
                    const data = await response.json();
                    const container = document.getElementById('suggestedTagsList');
                    if (container && data.tags) {
                        container.innerHTML = data.tags.map(t =>
                            `<span class="badge bg-light text-dark p-2 me-1 mb-1" style="cursor:pointer" 
                        onclick="document.getElementById('tagInput').value='${escapeHtml(t)}'">
                        🏷️ ${escapeHtml(t)}
                    </span>`
                        ).join('');
                    }
                } catch (e) {
                    console.error('Error loading popular tags:', e);
                }
            }

            async function saveTag() {
                const tagInput = document.getElementById('tagInput');
                const tag = tagInput?.value.trim();

                if (!tag) {
                    showToast('Please enter a tag', 'error');
                    return;
                }

                showLoader();
                try {
                    const response = await fetch(`/crm/students/${currentStudentId}/add-tag`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            tag
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        updateTagsInRow(currentStudentId, data.tags);
                        const modal = document.getElementById('tagModal');
                        if (modal) bootstrap.Modal.getInstance(modal)?.hide();
                        showToast('Tag added successfully', 'success');
                    } else {
                        throw new Error(data.error || 'Failed to add tag');
                    }
                } catch (err) {
                    showToast(err.message, 'error');
                } finally {
                    hideLoader();
                }
            }

            async function removeTag(id, tag) {
                if (!confirm(`Remove tag "${tag}"?`)) return;

                showLoader();
                try {
                    const response = await fetch(`/crm/students/${id}/remove-tag`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            tag
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        updateTagsInRow(id, data.tags);
                        showToast('Tag removed', 'success');
                    } else {
                        throw new Error(data.error || 'Failed to remove tag');
                    }
                } catch (err) {
                    showToast(err.message, 'error');
                } finally {
                    hideLoader();
                }
            }

            function updateTagsInRow(id, tags) {
                const row = document.querySelector(`tr:has(.add-tag-btn[data-student-id="${id}"])`);
                if (!row) return;

                const container = row.querySelector('.tags-list');
                if (!container) return;

                if (tags && tags.length > 0) {
                    container.innerHTML = tags.map(t =>
                        `<span class="tag">
                    <i class="fas fa-tag"></i> ${escapeHtml(t)}
                    <span class="tag-remove" onclick="event.stopPropagation(); window.CrmListHelper.removeTag(${id}, '${escapeHtml(t)}')">×</span>
                </span>`
                    ).join('');
                } else {
                    container.innerHTML = '';
                }
            }

            async function handleRatingChange(radio) {
                const form = radio.closest('.rating-form');
                if (!form) return;

                showLoader();
                try {
                    const response = await fetch(form.action, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            rating: radio.value
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast('Rating updated', 'success');
                    } else {
                        throw new Error(data.error || 'Failed to update rating');
                    }
                } catch (err) {
                    showToast(err.message, 'error');
                } finally {
                    hideLoader();
                }
            }

            function init() {
                // Set up rating change listeners
                document.querySelectorAll('.star-rating input').forEach(radio => {
                    radio.removeEventListener('change', window._listRatingHandler);
                    window._listRatingHandler = () => handleRatingChange(radio);
                    radio.addEventListener('change', window._listRatingHandler);
                });

                // Set up add tag buttons
                document.body.addEventListener('click', function(e) {
                    const addTagBtn = e.target.closest('.add-tag-btn');
                    if (addTagBtn && addTagBtn.closest('.list-view-table')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const studentId = addTagBtn.dataset.studentId;
                        if (studentId) openTagModal(studentId);
                    }
                });

                // Set up save tag button
                const saveTagBtn = document.getElementById('saveTagBtn');
                if (saveTagBtn) {
                    saveTagBtn.removeEventListener('click', window._listSaveHandler);
                    window._listSaveHandler = saveTag;
                    saveTagBtn.addEventListener('click', window._listSaveHandler);
                }
            }

            return {
                init: init,
                openTagModal: openTagModal,
                saveTag: saveTag,
                removeTag: removeTag
            };
        })();

        // Auto-initialize when on list view
        document.addEventListener('DOMContentLoaded', function() {
            const currentView = document.querySelector('[name="view"]')?.value || 'kanban';
            if (currentView === 'list') {
                setTimeout(function() {
                    if (window.CrmListHelper && typeof window.CrmListHelper.init === 'function') {
                        window.CrmListHelper.init();
                    }
                }, 100);
            }
        });
    </script>
@endpush
