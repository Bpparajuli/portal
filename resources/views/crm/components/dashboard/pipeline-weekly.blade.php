{{-- resources/views/crm/components/dashboard/pipeline-weekly.blade.php --}}
@push('styles')
    <style>
        .bottom-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .insight-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .insight-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .insight-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #eef2ff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stage-progress-item {
            margin-bottom: 1rem;
        }

        .stage-progress-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .progress-bar-custom {
            height: 6px;
            background: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .quick-action-btn:hover {
            background: white;
            transform: translateX(4px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .quick-action-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .weekly-table {
            width: 100%;
            font-size: 0.75rem;
            border-collapse: collapse;
        }

        .weekly-table th,
        .weekly-table td {
            padding: 8px 6px;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }

        .weekly-table th:first-child,
        .weekly-table td:first-child {
            text-align: left;
        }

        .staff-detail-table {
            width: 100%;
            font-size: 0.7rem;
            margin: 0;
            background: #fafbfc;
        }

        .staff-detail-table th,
        .staff-detail-table td {
            padding: 6px 4px;
            text-align: center;
        }

        .staff-row {
            cursor: pointer;
            transition: background 0.2s;
        }

        .staff-row:hover {
            background: #f5f3ff;
        }

        .weekly-summary {
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .task-day {
            font-weight: 600;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            transition: all 0.2s;
        }

        .task-day.today {
            background: linear-gradient(135deg, #fef3c7, #fffbeb);
            border-left: 3px solid #f59e0b;
        }

        .task-day .day-name {
            font-size: 0.85rem;
        }

        .task-day .task-stats {
            display: flex;
            gap: 12px;
        }

        .task-stat {
            text-align: center;
            min-width: 50px;
        }

        .task-stat .count {
            font-size: 1rem;
            font-weight: 700;
            display: block;
        }

        .task-stat .label {
            font-size: 0.6rem;
            color: #6b7280;
        }

        .task-stat.overdue .count {
            color: #dc2626;
        }

        .task-stat.completed .count {
            color: #10b981;
        }

        .task-stat.upcoming .count {
            color: #3b82f6;
        }

        @media (max-width: 992px) {
            .bottom-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
@endpush

<div class="bottom-row">
    <!-- Pipeline Strategy -->
    <div class="insight-card">
        <div class="insight-title"><i class="fas fa-chart-line" style="color:#4f46e5;"></i> Pipeline Strategy</div>
        @php
            $totalStudents = $stats['total_students'] ?? 1;
            $maxStageCount = 0;
            $maxStageName = '';
            foreach ($stages as $stage) {
                $count = isset($students[$stage->id]) ? $students[$stage->id]->count() : 0;
                if ($count > $maxStageCount) {
                    $maxStageCount = $count;
                    $maxStageName = $stage->name;
                }
            }
        @endphp
        @foreach ($stages->take(5) as $stage)
            @php
                $colCount = isset($students[$stage->id]) ? $students[$stage->id]->count() : 0;
                $percent = $totalStudents > 0 ? ($colCount / $totalStudents) * 100 : 0;
            @endphp
            <div class="stage-progress-item">
                <div class="stage-progress-header"><span>{{ $stage->name }}</span><span>{{ $colCount }} /
                        {{ $totalStudents }}</span></div>
                <div class="progress-bar-custom">
                    <div class="progress-fill" style="width: {{ $percent }}%; background: {{ $stage->color }};">
                    </div>
                </div>
            </div>
        @endforeach
        <div class="mt-3 pt-2 border-top">
            <div class="d-flex justify-content-between mb-2">
                <div><small>Total Pipeline</small><br><strong>{{ $totalStudents }}</strong></div>
                <div><small>Highest Stage</small><br><strong>{{ $maxStageName }} ({{ $maxStageCount }})</strong></div>
                <div><small>Conversion
                        Rate</small><br><strong>{{ $stages->count() > 0 ? round((($students[$stages->last()->id] ?? collect())->count() / max($totalStudents, 1)) * 100, 1) : 0 }}%</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="insight-card">
        <div class="insight-title"><i class="fas fa-bolt" style="color:#f59e0b;"></i> Quick Actions</div>
        <div class="quick-action-btn" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <div class="quick-action-icon" style="background:#e0e7ff;color:#4f46e5;"><i class="fas fa-user-plus"></i>
            </div>
            <div>
                <div style="font-weight:600;font-size:0.85rem;">Add New Student</div>
                <div style="font-size:0.7rem;color:#6b7280;">Create a new student profile</div>
            </div>
        </div>
        <div class="quick-action-btn" onclick="window.setView('calendar')">
            <div class="quick-action-icon" style="background:#fed7aa;color:#d97706;"><i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <div style="font-weight:600;font-size:0.85rem;">View Calendar</div>
                <div style="font-size:0.7rem;color:#6b7280;">See all upcoming tasks</div>
            </div>
        </div>
        <div class="quick-action-btn"
            onclick="window.location.href='{{ route('crm.dashboard', ['activity_filter' => 'overdue']) }}'">
            <div class="quick-action-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-clock"></i></div>
            <div>
                <div style="font-weight:600;font-size:0.85rem;">Review Overdue Tasks</div>
                <div style="font-size:0.7rem;color:#6b7280;">{{ $stats['overdue'] ?? 0 }} tasks need attention</div>
            </div>
        </div>
        <div class="quick-action-btn" onclick="window.location.href='{{ route('crm.export') }}'">
            <div class="quick-action-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-download"></i>
            </div>
            <div>
                <div style="font-weight:600;font-size:0.85rem;">Export Data</div>
                <div style="font-size:0.7rem;color:#6b7280;">Download students list</div>
            </div>
        </div>
    </div>

    <!-- Weekly Task Review -->
    <div class="insight-card">
        <div class="insight-title"><i class="fas fa-calendar-week" style="color:#8b5cf6;"></i> Weekly Task Review</div>
        <div id="weeklyTaskContainer" class="weekly-task-container" style="max-height: 380px; overflow-y: auto;">
            <div class="text-center py-4">
                <div class="crm-loader-spinner" style="width:30px;height:30px;"></div>
                <div class="mt-2 small text-muted">Loading tasks...</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.CrmWeeklyReview = (function() {
            'use strict';
            const CRM = window.CrmCore?.getInstance();

            function showLoader() {
                CRM?.showLoader();
            }

            function hideLoader() {
                CRM?.hideLoader();
            }

            function getCsrfToken() {
                return CRM ? CRM.getCsrfToken() : document.querySelector('meta[name="csrf-token"]')?.content;
            }

            function escapeHtml(str) {
                return CRM ? CRM.escapeHtml(str) : (str ? String(str) : '');
            }

            async function loadWeeklyTaskReview() {
                const container = document.getElementById('weeklyTaskContainer');
                if (!container) return;
                showLoader();
                try {
                    const response = await fetch('{{ route('crm.weekly.tasks') }}', {
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken()
                        }
                    });
                    const data = await response.json();
                    hideLoader();

                    if (data.is_admin) {
                        // Admin View - Simple table
                        const staffData = data.staff_data || [];
                        if (staffData.length === 0) {
                            container.innerHTML =
                                '<div class="text-center text-muted py-4"><i class="fas fa-users fa-2x mb-2 d-block"></i>No staff members found</div>';
                            return;
                        }
                        let html =
                            '<table class="weekly-table"><thead><tr><th>Staff Name</th><th>⚠️ Overdue</th><th>✅ Completed</th><th>🚀 Upcoming</th><th>📊 Total</th></tr></thead><tbody>';
                        staffData.forEach((staff, idx) => {
                            const detailId = `staff-detail-${idx}`;
                            html +=
                                `<tr class="staff-row" data-detail-id="${detailId}" style="${staff.total_all > 0 ? 'cursor:pointer' : ''}">
                        <td><strong>${escapeHtml(staff.staff_name)}</strong>${staff.total_all === 0 ? ' <span class="badge bg-secondary">No Tasks</span>' : ''}</td>
                        <td class="${staff.total_overdue > 0 ? 'text-danger fw-bold' : 'text-muted'}">${staff.total_overdue}</td>
                        <td class="${staff.total_completed > 0 ? 'text-success fw-bold' : 'text-muted'}">${staff.total_completed}</td>
                        <td class="${staff.total_upcoming > 0 ? 'text-primary fw-bold' : 'text-muted'}">${staff.total_upcoming}</td>
                        <td class="fw-bold">${staff.total_all}</td>
                    </tr>
                    <tr class="staff-detail-row" id="${detailId}" style="display:none;"><td colspan="5" style="padding:0;"><div class="p-2 bg-light small">Click on a staff member to see daily breakdown</div></td></tr>`;
                        });
                        html += '</tbody></table>';
                        container.innerHTML = html;
                        document.querySelectorAll('.staff-row').forEach(row => {
                            const detailId = row.getAttribute('data-detail-id');
                            const totalCell = row.querySelector('td:last-child');
                            if (totalCell && totalCell.innerText !== '0') {
                                row.addEventListener('click', () => {
                                    const detailRow = document.getElementById(detailId);
                                    if (detailRow.style.display === 'none') {
                                        detailRow.style.display = 'table-row';
                                        row.style.background = '#f5f3ff';
                                    } else {
                                        detailRow.style.display = 'none';
                                        row.style.background = '';
                                    }
                                });
                            }
                        });
                    } else {
                        // Staff View - Modern card layout
                        const weekData = data.week_data;
                        const days = weekData.days || [];
                        if (days.length === 0) {
                            container.innerHTML =
                                '<div class="text-center text-muted py-4"><i class="fas fa-calendar-check fa-2x mb-2 d-block"></i>No tasks assigned this week</div>';
                            return;
                        }
                        let html =
                            `<div class="weekly-summary mb-3"><div class="row text-center">
                    <div class="col-4"><div class="small text-muted">Total</div><div class="h5 mb-0 fw-bold">${weekData.total_all}</div></div>
                    <div class="col-4"><div class="small text-success">✅ Done</div><div class="h5 mb-0 text-success fw-bold">${weekData.total_completed}</div></div>
                    <div class="col-4"><div class="small text-danger">⚠️ Overdue</div><div class="h5 mb-0 text-danger fw-bold">${weekData.total_overdue}</div></div>
                </div><div class="progress mt-2" style="height:4px"><div class="progress-bar bg-success" style="width:${weekData.total_all > 0 ? (weekData.total_completed / weekData.total_all * 100) : 0}%"></div><div class="progress-bar bg-danger" style="width:${weekData.total_all > 0 ? (weekData.total_overdue / weekData.total_all * 100) : 0}%"></div></div></div>`;

                        days.forEach(day => {
                            html += `<div class="task-day ${day.is_today ? 'today' : ''}">
                        <div class="day-name"><strong>${escapeHtml(day.day)}</strong> ${day.is_today ? '<span class="badge bg-warning ms-1" style="font-size:0.6rem;">Today</span>' : ''}</div>
                        <div class="task-stats">
                            <div class="task-stat overdue"><span class="count">${day.overdue}</span><span class="label">Overdue</span></div>
                            <div class="task-stat completed"><span class="count">${day.completed}</span><span class="label">Done</span></div>
                            <div class="task-stat upcoming"><span class="count">${day.upcoming}</span><span class="label">Upcoming</span></div>
                        </div>
                    </div>`;
                        });
                        container.innerHTML = html;
                    }
                } catch (err) {
                    hideLoader();
                    container.innerHTML =
                        '<div class="text-center text-muted py-4"><i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>Failed to load tasks</div>';
                }
            }
            return {
                load: loadWeeklyTaskReview
            };
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const currentView = document.querySelector('[name="view"]')?.value || 'kanban';
            if (currentView === 'kanban') {
                setTimeout(() => {
                    if (window.CrmWeeklyReview?.load) window.CrmWeeklyReview.load();
                }, 200);
            }
        });
    </script>
@endpush
