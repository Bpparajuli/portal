{{-- resources/views/crm/components/calendar-view.blade.php --}}

@push('styles')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        .calendar-container {
            background: linear-gradient(135deg, #1a0262 0%, #820b5c 100%);
            border-radius: 24px;
            padding: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .calendar-wrapper {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .fc {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .fc .fc-toolbar-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
        }

        .fc .fc-button-primary {
            background: white;
            border: 1px solid #e2e8f0;
            color: #820b5c;
            padding: 8px 14px;
            font-weight: 500;
            font-size: 0.85rem;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .fc .fc-button-primary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
            transform: translateY(-1px);
        }


        .fc-daygrid-day-number:hover {
            color: #820b5c !important;
            transform: scale(1.02);
        }

        /* Make the day-top container use space-between layout */
        .fc-daygrid-day-top {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-direction: row !important;
            width: 100% !important;
            padding: 6px 10px !important;
        }

        /* Date number stays on the left */
        .fc-daygrid-day-number {
            text-decoration: none !important;
            font-size: 0.95rem !important;
            font-weight: 600 !important;
            color: #475569 !important;
            padding: 0 !important;
            margin: 0 !important;
            float: none !important;
        }

        /* Task badge goes to the right */
        .task-count-badge {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            background: #f1f5f9;
            color: #475569;
            font-size: 10px;
            font-weight: 600;
            border-radius: 20px;
            padding: 3px 10px;
            min-width: 30px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-left: auto !important;
            /* Pushes it to the right */
        }

        /* Remove any default fullcalendar positioning */
        .fc-daygrid-day-frame {
            display: flex !important;
            flex-direction: column !important;
        }

        .fc-daygrid-day-top {
            justify-content: space-between !important;
        }

        .task-count-badge:hover {
            background: #4f46e5;
            color: white;
            transform: scale(1.05);
        }

        .fc-day-sat {
            background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 100%);

        }

        .fc-day-sat .fc-daygrid-day-number {
            color: #dc2626 !important;
        }

        .fc-day-sat .fc-daygrid-day-top::after {
            content: "Holiday";
            position: absolute;
            right: 10px;
            top: 8px;
            font-size: 7px;
            background: #fee2e2;
            color: #dc2626;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
        }

        .fc .fc-daygrid-day {
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            margin: 2px;
        }

        .fc .fc-daygrid-day:hover {
            background: #fafbff;
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: #e2e8f0;
        }

        .fc .fc-day-today {
            background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%) !important;
            border-color: #fde047 !important;
        }

        .fc .fc-day-today .fc-daygrid-day-number {
            color: #854d0e !important;
            font-weight: 700 !important;
        }

        .fc-daygrid-day[data-day="0"],
        .fc-daygrid-day[data-day="1"],
        .fc-daygrid-day[data-day="2"],
        .fc-daygrid-day[data-day="3"],
        .fc-daygrid-day[data-day="4"],
        .fc-daygrid-day[data-day="5"] {
            width: 15.3% !important;
            min-width: 100px !important;
        }

        .fc-daygrid-day[data-day="6"] {
            width: 8% !important;
            min-width: 70px !important;
        }

        .fc-col-header-cell[data-day="6"] {
            width: 8% !important;
        }

        .fc-col-header-cell:not([data-day="6"]) {
            width: 15.3% !important;
        }

        .fc-event-time {
            display: none !important;
        }

        .fc-event-title {
            font-size: 0.7rem !important;
            font-weight: 600 !important;
            white-space: normal !important;
            word-wrap: break-word !important;
            padding: 6px 8px !important;
            line-height: 1.3 !important;
            color: white !important;
        }

        .fc-daygrid-event {
            white-space: normal !important;
            min-height: 36px !important;
            margin: 2px 3px !important;
            border-radius: 10px !important;
            transition: all 0.2s ease;
        }

        .fc-daygrid-event:hover {
            transform: translateX(2px);
            filter: brightness(1.05);
        }

        .fc-event-morning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            border: none !important;
            border-radius: 10px !important;
        }

        .fc-event-afternoon {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            border: none !important;
            border-radius: 10px !important;
        }

        .fc-event-evening {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important;
            border: none !important;
            border-radius: 10px !important;
        }

        .fc-event-overdue {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            border: none !important;
            border-radius: 10px !important;
            animation: subtlePulse 2s infinite;
        }

        @keyframes subtlePulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.85;
            }
        }

        .fc-event-completed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            border: none !important;
            border-radius: 10px !important;
            opacity: 0.75;
        }

        .fc-event-staff {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%) !important;
            border: none !important;
            border-radius: 10px !important;
            cursor: pointer;
        }

        .fc-h-event {
            background: transparent !important;
            border: none !important;
        }

        .staff-tasks-popup {
            position: fixed;
            background: white;
            border-radius: 24px;
            padding: 0;
            box-shadow: 0 30px 60px -20px rgba(0, 0, 0, 0.3);
            z-index: 10001;
            min-width: 450px;
            max-width: 550px;
            display: none;
            max-height: 80vh;
            overflow: hidden;
        }

        .popup-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            border-radius: 24px 24px 0 0;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .popup-header h6 {
            margin: 0;
            font-weight: 700;
            font-size: 1rem;
        }

        .popup-body {
            padding: 1.25rem;
            max-height: calc(80vh - 80px);
            overflow-y: auto;
        }

        .staff-group {
            margin-bottom: 1.25rem;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #eef2ff;
        }

        .staff-group-header {
            background: #fafbff;
            color: #1e293b;
            padding: 0.9rem 1.2rem;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            border-bottom: 1px solid #eef2ff;
        }

        .staff-group-header:hover {
            background: #f5f3ff;
            color: #4f46e5;
        }

        .staff-group-header .badge {
            background: #e0e7ff !important;
            color: #4f46e5 !important;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .staff-task-list {
            padding: 0.75rem;
        }

        .staff-task-item {
            background: #fafbff;
            padding: 0.9rem;
            margin-bottom: 0.6rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid #f0f2f5;
        }

        .staff-task-item:hover {
            background: white;
            transform: translateX(6px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: #c7d2fe;
        }

        .task-student-name {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .task-title {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 6px;
            font-weight: 500;
        }

        .task-meta {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        .task-meta .badge {
            font-size: 0.65rem;
            padding: 4px 10px;
            font-weight: 600;
            border-radius: 20px;
        }

        .task-detail-popup {
            position: fixed;
            background: white;
            border-radius: 24px;
            padding: 0;
            box-shadow: 0 30px 60px -20px rgba(0, 0, 0, 0.3);
            z-index: 10002;
            min-width: 380px;
            max-width: 450px;
            display: none;
        }

        .task-detail-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            border-radius: 24px 24px 0 0;
        }

        .task-detail-body {
            padding: 1.5rem;
        }

        .task-detail-item {
            margin-bottom: 1rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .task-detail-label {
            font-size: 0.65rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .task-detail-value {
            font-size: 0.9rem;
            color: #1e293b;
            font-weight: 500;
        }

        .calendar-legend {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
            padding: 0.5rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 12px;
            background: #f8fafc;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #475569;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .popup-close {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.1rem;
        }

        .popup-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        .back-button {
            margin-bottom: 1rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .back-button button {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 30px;
            cursor: pointer;
        }

        .back-button button:hover {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        .view-student-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: white;
            padding: 8px 18px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .view-student-btn:hover {
            background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
            transform: translateY(-2px);
            color: white;
        }

        @media (max-width: 768px) {
            .fc-daygrid-day[data-day="6"] .fc-daygrid-day-top::after {
                font-size: 6px !important;
                padding: 1px 4px !important;
                left: 4px !important;
                top: 4px !important;
                content: "H" !important;
            }

            .fc-event-title {
                font-size: 0.65rem !important;
                padding: 4px 6px !important;
            }
        }
    </style>
@endpush

<div class="calendar-container">
    <div class="calendar-wrapper">
        <div class="calendar-legend">
            <div class="legend-item"><span class="legend-dot"
                    style="background: linear-gradient(135deg, #f59e0b, #d97706);"></span><span>Morning</span></div>
            <div class="legend-item"><span class="legend-dot"
                    style="background: linear-gradient(135deg, #3b82f6, #2563eb);"></span><span>Afternoon</span></div>
            <div class="legend-item"><span class="legend-dot"
                    style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"></span><span>Evening</span></div>
            <div class="legend-item"><span class="legend-dot"
                    style="background: linear-gradient(135deg, #ef4444, #dc2626);"></span><span>Overdue</span></div>
            <div class="legend-item"><span class="legend-dot"
                    style="background: linear-gradient(135deg, #10b981, #059669);"></span><span>Completed</span></div>
            <div class="legend-item"><span class="legend-dot" style="background: #fee2e2;"></span><span>Saturday</span>
            </div>
            @if ($isAdmin)
                <div class="legend-item"><span class="legend-dot"
                        style="background: linear-gradient(135deg, #4f46e5, #4338ca);"></span><span>Staff Group</span>
                </div>
            @endif
        </div>
        <div id="workCalendar"></div>
    </div>
</div>

<div class="staff-tasks-popup" id="staffTasksPopup">
    <div class="popup-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 id="staffPopupTitle"><i class="fas fa-tasks me-2"></i>Tasks</h6>
            <button class="popup-close" onclick="closeStaffPopup()">×</button>
        </div>
    </div>
    <div class="popup-body" id="staffPopupBody"></div>
</div>

<div class="task-detail-popup" id="taskDetailPopup">
    <div class="task-detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6><i class="fas fa-info-circle me-2"></i>Task Details</h6>
            <button class="popup-close" onclick="closeTaskDetailPopup()">×</button>
        </div>
    </div>
    <div class="task-detail-body" id="taskDetailBody"></div>
</div>

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script>
        const isAdmin = {{ isset($isAdmin) && $isAdmin ? 'true' : 'false' }};
        let calendar = null;
        let currentSelectedDate = null;

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function showLoader() {
            const loader = document.getElementById('globalLoader');
            if (loader) loader.classList.add('show');
        }

        function hideLoader() {
            const loader = document.getElementById('globalLoader');
            if (loader) loader.classList.remove('show');
        }

        function closeStaffPopup() {
            document.getElementById('staffTasksPopup').style.display = 'none';
        }

        function closeTaskDetailPopup() {
            document.getElementById('taskDetailPopup').style.display = 'none';
        }

        function getTimeSlotLabel(timeSlot) {
            if (timeSlot === 'morning') return '🌅 Morning (9AM-12PM)';
            if (timeSlot === 'afternoon') return '☀️ Afternoon (12PM-3PM)';
            if (timeSlot === 'evening') return '🌙 Evening (3PM-6PM)';
            return '📅 No time slot';
        }

        function getPriorityBadge(priority) {
            if (priority === 'high') return '<span class="badge bg-danger">🔴 High Priority</span>';
            if (priority === 'medium') return '<span class="badge bg-warning">🟡 Medium Priority</span>';
            return '<span class="badge bg-success">🟢 Low Priority</span>';
        }

        function getStatusBadge(status, isOverdue) {
            if (status === 'completed') return '<span class="badge bg-success">✓ Completed</span>';
            if (isOverdue) return '<span class="badge bg-danger">⚠️ Overdue</span>';
            return '<span class="badge bg-secondary">⏳ Pending</span>';
        }

        function openStaffTasksPopup(date, staffId = null, staffName = null) {
            currentSelectedDate = date;
            showLoader();

            let apiUrl = `/crm/calendar/staff-tasks?date=${date}`;
            if (staffId && staffId !== 'null' && staffId !== 'undefined' && staffId !== 'unassigned') {
                apiUrl += `&assignee_id=${staffId}`;
            }

            fetch(apiUrl, {
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    hideLoader();
                    const formattedDate = new Date(date).toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    const popupTitle = document.getElementById('staffPopupTitle');
                    const popupBody = document.getElementById('staffPopupBody');

                    if (staffId && staffId !== 'null' && staffId !== 'undefined' && staffId !== 'unassigned' &&
                        staffName) {
                        popupTitle.innerHTML =
                            `<i class="fas fa-user-tie me-2"></i>${escapeHtml(staffName)}'s Tasks for ${formattedDate}`;
                    } else {
                        popupTitle.innerHTML = `<i class="fas fa-users me-2"></i>Staff Tasks for ${formattedDate}`;
                    }

                    if (data.tasks && data.tasks.length > 0) {
                        let html = '';

                        if (staffId && staffId !== 'null' && staffId !== 'undefined' && staffId !== 'unassigned') {
                            html +=
                                `<div class="back-button"><button onclick="openStaffTasksPopup('${date}')"><i class="fas fa-arrow-left me-1"></i> Back to All Staff</button></div>`;
                            html += `<div class="staff-task-list">`;
                            data.tasks.forEach(task => {
                                html += `<div class="staff-task-item" onclick="openTaskDetailPopup('${task.id}', '${escapeHtml(task.title)}', '${task.student_id}', '${escapeHtml(task.student_name)}', '${escapeHtml(task.student_country || 'N/A')}', '${task.priority_time_slot || 'evening'}', '${task.priority || 'medium'}', '${task.status}', ${task.is_overdue}, '${escapeHtml(task.description || 'No description')}')">
                                <div class="task-student-name"><i class="fas fa-user-graduate me-2"></i>${escapeHtml(task.student_name)}${task.student_country ? `<span class="badge bg-info ms-2">${escapeHtml(task.student_country)}</span>` : ''}</div>
                                <div class="task-title mt-1">📌 <strong>${escapeHtml(task.title)}</strong></div>
                                <div class="task-meta mt-2">${getPriorityBadge(task.priority)} ${getStatusBadge(task.status, task.is_overdue)} <span class="badge bg-secondary">${getTimeSlotLabel(task.priority_time_slot)}</span></div>
                            </div>`;
                            });
                            html += `</div>`;
                        } else {
                            const tasksByStaff = {};
                            data.tasks.forEach(task => {
                                const sName = task.assigned_to_name || 'Unassigned';
                                const sId = task.assigned_to || 'unassigned';
                                if (!tasksByStaff[sName]) tasksByStaff[sName] = {
                                    staffId: sId,
                                    tasks: []
                                };
                                tasksByStaff[sName].tasks.push(task);
                            });

                            for (const [sName, sData] of Object.entries(tasksByStaff)) {
                                html += `<div class="staff-group">
                                <div class="staff-group-header" data-staff-id="${sData.staffId}" data-staff-name="${escapeHtml(sName)}" data-date="${date}">
                                    <span><i class="fas fa-user-tie me-2"></i>${escapeHtml(sName)}</span>
                                    <span class="badge bg-white text-dark">${sData.tasks.length} tasks</span>
                                </div>
                                <div class="staff-task-list" style="display: block;">
                                    ${sData.tasks.map(task => `<div class="staff-task-item" onclick="openTaskDetailPopup('${task.id}', '${escapeHtml(task.title)}', '${task.student_id}', '${escapeHtml(task.student_name)}', '${escapeHtml(task.student_country || 'N/A')}', '${task.priority_time_slot || 'evening'}', '${task.priority || 'medium'}', '${task.status}', ${task.is_overdue}, '${escapeHtml(task.description || 'No description')}')">
                                                                                                                                        <div class="task-student-name"><i class="fas fa-user-graduate me-2"></i>${escapeHtml(task.student_name)}${task.student_country ? `<span class="badge bg-info ms-2">${escapeHtml(task.student_country)}</span>` : ''}</div>
                                                                                                                                        <div class="task-title mt-1">📌 <strong>${escapeHtml(task.title)}</strong></div>
                                                                                                                                        <div class="task-meta mt-2">${getPriorityBadge(task.priority)} ${getStatusBadge(task.status, task.is_overdue)} <span class="badge bg-secondary">${getTimeSlotLabel(task.priority_time_slot)}</span></div>
                                                                                                                                    </div>`).join('')}
                                </div>
                            </div>`;
                            }
                        }
                        popupBody.innerHTML = html;

                        if (!staffId || staffId === 'null' || staffId === 'undefined' || staffId === 'unassigned') {
                            setTimeout(() => {
                                document.querySelectorAll('.staff-group-header').forEach(header => {
                                    const newHeader = header.cloneNode(true);
                                    header.parentNode.replaceChild(newHeader, header);
                                    newHeader.onclick = function(e) {
                                        e.stopPropagation();
                                        const sId = this.dataset.staffId;
                                        const sName = this.dataset.staffName;
                                        const popupDate = this.dataset.date;
                                        if (sId && sId !== 'unassigned' && sId !== 'null') {
                                            closeStaffPopup();
                                            openStaffTasksPopup(popupDate, sId, sName);
                                        }
                                    };
                                });
                            }, 100);
                        }
                    } else {
                        popupBody.innerHTML =
                            '<div class="text-center py-5 text-muted"><i class="fas fa-calendar-check fa-3x mb-3 d-block"></i>No tasks scheduled for this day</div>';
                    }

                    const popup = document.getElementById('staffTasksPopup');
                    popup.style.display = 'block';
                    popup.style.top = '50%';
                    popup.style.left = '50%';
                    popup.style.transform = 'translate(-50%, -50%)';
                })
                .catch(err => {
                    hideLoader();
                    document.getElementById('staffPopupBody').innerHTML =
                        '<div class="text-center py-5 text-danger">Error loading tasks</div>';
                });
        }

        function openTaskDetailPopup(taskId, task_title, studentId, studentName, studentCountry, timeSlot, priority, status,
            isOverdue, description) {
            const popupBody = document.getElementById('taskDetailBody');
            popupBody.innerHTML = `
                <div class="task-detail-item">
                    <div class="task-detail-label"><i class="fas fa-user-graduate me-1"></i> Student</div>
                    <div class="task-detail-value"><strong>${escapeHtml(studentName)}</strong><br><small class="text-muted">📍 ${escapeHtml(studentCountry)}</small></div>
                </div>
                <div class="task-detail-item">
                    <div class="task-detail-label"><i class="fas fa-tasks me-1"></i> Task Title</div>
                    <div class="task-detail-value">📌 ${escapeHtml(task_title)}</div>
                </div>
                <div class="task-detail-item">
                    <div class="task-detail-label"><i class="fas fa-align-left me-1"></i> Description</div>
                    <div class="task-detail-value">📝 ${escapeHtml(description)}</div>
                </div>
                <div class="task-detail-item">
                    <div class="task-detail-label"><i class="fas fa-clock me-1"></i> Time Slot</div>
                    <div class="task-detail-value">${getTimeSlotLabel(timeSlot)}</div>
                </div>
                <div class="task-detail-item">
                    <div class="task-detail-label"><i class="fas fa-flag me-1"></i> Priority & Status</div>
                    <div class="task-detail-value">${getPriorityBadge(priority)} ${getStatusBadge(status, isOverdue)}</div>
                </div>
                <div class="text-center mt-3">
                    <a href="/crm/student/${studentId}" class="view-student-btn"><i class="fas fa-external-link-alt me-1"></i> View Full Student Profile</a>
                </div>
            `;
            const popup = document.getElementById('taskDetailPopup');
            popup.style.display = 'block';
            popup.style.top = '50%';
            popup.style.left = '50%';
            popup.style.transform = 'translate(-50%, -50%)';
        }

        function updateDayNumbers(events) {
            const counts = {};
            events.forEach(e => {
                const d = new Date(e.start).toDateString();
                counts[d] = (counts[d] || 0) + 1;
            });
            document.querySelectorAll('.fc-daygrid-day').forEach(day => {
                const date = day.getAttribute('data-date');
                if (date) {
                    const count = counts[new Date(date).toDateString()] || 0;
                    const oldBadge = day.querySelector('.task-count-badge');
                    if (oldBadge) oldBadge.remove();
                    if (count > 0) {
                        const topContainer = day.querySelector('.fc-daygrid-day-top');
                        if (topContainer) {
                            const badge = document.createElement('span');
                            badge.className = 'task-count-badge';
                            badge.innerHTML = `<i class="fas fa-tasks me-1"></i>${count}`;
                            badge.onclick = (e) => {
                                e.stopPropagation();
                                openStaffTasksPopup(date);
                            };
                            topContainer.appendChild(badge);
                        }
                    }
                }
            });
        }

        function applyColumnWidths() {
            document.querySelectorAll('.fc-col-header-cell, .fc-daygrid-day').forEach(cell => {
                let dayIndex = cell.hasAttribute('data-day') ? parseInt(cell.getAttribute('data-day')) : (cell
                    .getAttribute('data-date') ? new Date(cell.getAttribute('data-date')).getDay() : null);
                if (dayIndex === 6) {
                    cell.style.width = '8%';
                    cell.style.minWidth = '70px';
                } else if (dayIndex !== null) {
                    cell.style.width = '15.3%';
                    cell.style.minWidth = '100px';
                }
            });
        }

        function initCalendar() {
            const el = document.getElementById('workCalendar');
            if (!el) return;

            calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                displayEventTime: false,
                displayEventEnd: false,
                eventContent: function(arg) {
                    let title = arg.event.title;
                    let eventClass = '';
                    if (arg.event.extendedProps?.staff_name) eventClass = 'fc-event-staff';
                    else if (arg.event.extendedProps?.is_overdue) eventClass = 'fc-event-overdue';
                    else if (arg.event.extendedProps?.status === 'completed') eventClass = 'fc-event-completed';
                    else if (arg.event.extendedProps?.time_slot === 'morning') eventClass = 'fc-event-morning';
                    else if (arg.event.extendedProps?.time_slot === 'afternoon') eventClass =
                        'fc-event-afternoon';
                    else if (arg.event.extendedProps?.time_slot === 'evening') eventClass = 'fc-event-evening';
                    return {
                        html: `<div class="fc-event-main-frame ${eventClass}" style="border-radius: 6px; padding: 2px;"><div class="fc-event-title">${escapeHtml(title)}</div></div>`
                    };
                },
                dayCellDidMount: function(info) {
                    info.el.setAttribute('data-day', info.date.getDay());
                    if (info.date.getDay() === 6) info.el.classList.add('fc-day-sat');
                },
                dayHeaderDidMount: function(info) {
                    info.el.setAttribute('data-day', info.date.getDay());
                },
                events: function(info, success, failure) {
                    showLoader();
                    const urlParams = new URLSearchParams(window.location.search);
                    fetch('{{ route('crm.calendar.events') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken()
                            },
                            body: JSON.stringify({
                                start: info.startStr,
                                end: info.endStr,
                                assignee_id: urlParams.get('assignee_id'),
                                activity_filter: urlParams.get('activity_filter'),
                                stage_id: urlParams.get('stage_id'),
                                search: urlParams.get('search'),
                                search_type: urlParams.get('search_type')
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            hideLoader();
                            success(data.events || []);
                            setTimeout(() => {
                                updateDayNumbers(data.events || []);
                                applyColumnWidths();
                            }, 100);
                        })
                        .catch(err => {
                            hideLoader();
                            failure(err);
                        });
                },
                datesSet: function() {
                    setTimeout(() => {
                        applyColumnWidths();
                        document.querySelectorAll('.fc-daygrid-day').forEach(day => {
                            const dateAttr = day.getAttribute('data-date');
                            if (dateAttr && new Date(dateAttr).getDay() === 6) day.classList
                                .add('fc-day-sat');
                        });
                    }, 100);
                },
                dateClick: function(info) {
                    openStaffTasksPopup(info.dateStr);
                },
                eventClick: function(info) {
                    const props = info.event.extendedProps;
                    if (props?.staff_name && props?.tasks) {
                        openStaffTasksPopup(info.event.startStr.split('T')[0], props.staff_id, props
                            .staff_name);
                    } else if (props?.student_id) {
                        const realTaskTitle = props.task_title || info.event.title;
                        openTaskDetailPopup(
                            props.task_id || info.event.id, realTaskTitle,
                            props.student_id, props.student_name || 'Unknown',
                            props.student_country || 'N/A', props.time_slot || 'evening',
                            props.priority || 'medium', props.status || 'pending',
                            props.is_overdue || false, props.description || 'No description'
                        );
                    } else {
                        openStaffTasksPopup(info.event.startStr.split('T')[0]);
                    }
                },
                height: 650,
                weekends: true,
                nowIndicator: true
            });
            calendar.render();
            setTimeout(() => {
                applyColumnWidths();
                const events = calendar.getEvents();
                if (events.length) updateDayNumbers(events.map(e => ({
                    start: e.start
                })));
            }, 200);
        }

        @if (($view ?? 'kanban') === 'calendar')
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initCalendar, 100);
            });
        @endif
    </script>
@endpush
