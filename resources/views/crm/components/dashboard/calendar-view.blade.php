@push('styles')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        .calendar-container { background: linear-gradient(135deg, #1a0262, #820b5c); border-radius: var(--hd-radius); padding: var(--hd-md); }
        .calendar-wrapper { background: #fff; border-radius: var(--hd-radius); padding: var(--hd-md); }

        .fc { background: #fff; border-radius: var(--hd-radius); overflow: hidden; font-family: inherit; }
        .fc .fc-toolbar-title { font-size: 1rem; font-weight: 700; color: #1e293b; }
        .fc .fc-button-primary {
            background: #fff; border: 1px solid #e2e8f0; color: #820b5c;
            padding: 4px 10px; font-weight: 500; font-size: var(--hd-font-sm);
            border-radius: var(--hd-radius-sm); transition: all .12s;
        }
        .fc .fc-button-primary:hover { background: #f8fafc; border-color: #cbd5e1; }
        .fc .fc-button-primary:disabled { opacity: .5; }
        .fc .fc-toolbar { margin-bottom: var(--hd-md) !important; }

        .fc-daygrid-day-top { display: flex !important; justify-content: space-between !important; align-items: center !important; flex-direction: row !important; width: 100% !important; padding: 3px 6px !important; }
        .fc-daygrid-day-number { text-decoration: none !important; font-size: var(--hd-font-sm) !important; font-weight: 600 !important; color: #475569 !important; padding: 0 !important; float: none !important; }

        .task-count-badge {
            display: inline-flex !important; align-items: center !important; gap: 3px !important;
            background: #f1f5f9; color: #475569; font-size: .55rem; font-weight: 600;
            border-radius: 4px; padding: 1px 5px; cursor: pointer; transition: all .12s;
            margin-left: auto !important;
        }
        .task-count-badge:hover { background: var(--crm-primary); color: #fff; }

        .fc-day-sat { background: linear-gradient(135deg, #fef2f2, #fff5f5); }
        .fc-day-sat .fc-daygrid-day-number { color: #dc2626 !important; }
        .fc-day-sat .fc-daygrid-day-top::after { content: "H"; position: absolute; right: 4px; top: 2px; font-size: .5rem; background: #fee2e2; color: #dc2626; padding: 1px 5px; border-radius: 4px; font-weight: 600; }

        .fc .fc-daygrid-day { cursor: pointer; border: 1px solid #f1f5f9; margin: 1px; transition: all .12s; }
        .fc .fc-daygrid-day:hover { background: #fafbff; box-shadow: var(--hd-shadow); border-color: #e2e8f0; }
        .fc .fc-day-today { background: linear-gradient(135deg, #fefce8, #fef9c3) !important; border-color: #fde047 !important; }
        .fc .fc-day-today .fc-daygrid-day-number { color: #854d0e !important; font-weight: 700 !important; }

        .fc-daygrid-day[data-day]:not([data-day="6"]) { width: 15.3% !important; min-width: 80px !important; }
        .fc-daygrid-day[data-day="6"] { width: 8% !important; min-width: 50px !important; }
        .fc-col-header-cell[data-day="6"] { width: 8% !important; }
        .fc-col-header-cell:not([data-day="6"]) { width: 15.3% !important; }

        .fc-event-time { display: none !important; }
        .fc-event-title { font-size: var(--hd-font-xs) !important; font-weight: 600 !important; padding: 2px 5px !important; line-height: 1.2 !important; color: #fff !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; }
        .fc-daygrid-event { min-height: 20px !important; margin: 1px 2px !important; border-radius: 3px !important; cursor: pointer; }
        .fc-daygrid-event:hover { filter: brightness(1.08); }

        .fc-event-morning { background: linear-gradient(135deg, #f59e0b, #d97706) !important; border: none !important; }
        .fc-event-afternoon { background: linear-gradient(135deg, #3b82f6, #2563eb) !important; border: none !important; }
        .fc-event-evening { background: linear-gradient(135deg, #8b5cf6, #7c3aed) !important; border: none !important; }
        .fc-event-overdue { background: linear-gradient(135deg, #ef4444, #dc2626) !important; border: none !important; animation: pulse 2s infinite; }
        .fc-event-completed { background: linear-gradient(135deg, #1a0262, #820b5c) !important; border: none !important; opacity: .7; }
        .fc-event-staff { background: linear-gradient(135deg, var(--crm-primary), #4338ca) !important; border: none !important; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .8; } }

        .fc-h-event { background: transparent !important; border: none !important; }

        /* Legend compact */
        .calendar-legend { display: flex; gap: var(--hd-sm); margin-bottom: var(--hd-md); flex-wrap: wrap; padding: var(--hd-xs) 0; }
        .legend-item { display: flex; align-items: center; gap: 4px; padding: 2px 8px; background: #f8fafc; border-radius: 4px; font-size: var(--hd-font-xs); font-weight: 600; color: #475569; }
        .legend-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }

        /* Popups compact */
        .staff-tasks-popup, .task-detail-popup {
            position: fixed; background: #fff; border-radius: var(--hd-radius); padding: 0;
            box-shadow: 0 20px 40px -12px rgba(0,0,0,.25); z-index: 10001;
            min-width: 360px; max-width: 450px; display: none; max-height: 70vh; overflow: hidden;
        }
        .popup-header { background: linear-gradient(135deg, #1a1a2e, #16213e); color: #fff; padding: var(--hd-md) var(--hd-lg); font-size: var(--hd-font-md); font-weight: 700; }
        .popup-body { padding: var(--hd-md) var(--hd-lg); max-height: calc(70vh - 50px); overflow-y: auto; }
        .popup-close { background: rgba(255,255,255,.1); border: none; color: #fff; width: 24px; height: 24px; border-radius: 50%; cursor: pointer; font-size: .85rem; display: flex; align-items: center; justify-content: center; }
        .popup-close:hover { background: rgba(255,255,255,.2); }

        .staff-group { margin-bottom: var(--hd-md); border: 1px solid #eef2ff; border-radius: var(--hd-radius-sm); overflow: hidden; }
        .staff-group-header {
            background: #fafbff; padding: 6px 10px; font-weight: 700; font-size: var(--hd-font-sm);
            display: flex; justify-content: space-between; align-items: center; cursor: pointer;
            border-bottom: 1px solid #eef2ff;
        }
        .staff-group-header:hover { background: #f5f3ff; }
        .staff-task-list { padding: var(--hd-sm); }
        .staff-task-item { background: #fafbff; padding: 6px 8px; margin-bottom: 4px; border-radius: var(--hd-radius-sm); cursor: pointer; transition: all .12s; border: 1px solid #f0f2f5; font-size: var(--hd-font-xs); }
        .staff-task-item:hover { background: #fff; transform: translateX(3px); box-shadow: var(--hd-shadow); border-color: #c7d2fe; }
        .task-student-name { font-weight: 700; color: #1e293b; font-size: var(--hd-font-sm); display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }

        .task-detail-body { padding: var(--hd-lg); font-size: var(--hd-font-sm); }
        .task-detail-item { margin-bottom: var(--hd-md); padding-bottom: var(--hd-sm); border-bottom: 1px solid #f1f5f9; }
        .task-detail-label { font-size: var(--hd-font-xs); font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 2px; }
        .task-detail-value { color: #1e293b; font-weight: 500; }

        .view-student-btn { display: inline-flex; align-items: center; gap: 4px; background: var(--crm-primary); color: #fff; padding: 4px 12px; border-radius: var(--hd-radius-sm); text-decoration: none; font-size: var(--hd-font-xs); font-weight: 600; }
        .view-student-btn:hover { background: var(--crm-primary-dark); color: #fff; }
    </style>
@endpush

<div class="calendar-container">
    <div class="calendar-wrapper">
        <div class="calendar-legend">
            <span class="legend-item"><span class="legend-dot" style="background:linear-gradient(135deg,#f59e0b,#d97706);"></span>AM</span>
            <span class="legend-item"><span class="legend-dot" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"></span>PM</span>
            <span class="legend-item"><span class="legend-dot" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"></span>Eve</span>
            <span class="legend-item"><span class="legend-dot" style="background:linear-gradient(135deg,#ef4444,#dc2626);"></span>Overdue</span>
            <span class="legend-item"><span class="legend-dot" style="background:linear-gradient(135deg,#1a0262,#820b5c);"></span>Done</span>
            <span class="legend-item"><span class="legend-dot" style="background:#fee2e2;"></span>Sat</span>
            @if ($isAdmin)<span class="legend-item"><span class="legend-dot" style="background:linear-gradient(135deg,var(--crm-primary),#4338ca);"></span>Staff</span>@endif
        </div>
        <div id="workCalendar"></div>
    </div>
</div>

<div class="staff-tasks-popup" id="staffTasksPopup">
    <div class="popup-header">
        <div class="d-flex justify-content-between align-items-center">
            <span id="staffPopupTitle"><i class="fas fa-tasks me-1"></i>Tasks</span>
            <button class="popup-close" onclick="closeStaffPopup()">×</button>
        </div>
    </div>
    <div class="popup-body" id="staffPopupBody"></div>
</div>

<div class="task-detail-popup" id="taskDetailPopup">
    <div class="popup-header">
        <div class="d-flex justify-content-between align-items-center">
            <span><i class="fas fa-info-circle me-1"></i>Task Detail</span>
            <button class="popup-close" onclick="closeTaskDetailPopup()">×</button>
        </div>
    </div>
    <div class="task-detail-body" id="taskDetailBody"></div>
</div>

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script>
        var isAdmin = {{ isset($isAdmin) && $isAdmin ? 'true' : 'false' }};
        var calendar = null, curDate = null;

        function csrf() { return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'; }
        function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
        function sl() { var l = document.getElementById('globalLoader'); if (l) l.classList.add('show'); }
        function hl() { var l = document.getElementById('globalLoader'); if (l) l.classList.remove('show'); }
        function closeStaffPopup() { document.getElementById('staffTasksPopup').style.display = 'none'; }
        function closeTaskDetailPopup() { document.getElementById('taskDetailPopup').style.display = 'none'; }
        function slotLabel(s) { return s === 'morning' ? 'AM' : s === 'afternoon' ? 'PM' : s === 'evening' ? 'Eve' : '—'; }

        function openStaffTasksPopup(date, staffId, staffName) {
            curDate = date; sl();
            var url = '/crm/calendar/staff-tasks?date=' + date;
            if (staffId && staffId !== 'null' && staffId !== 'undefined' && staffId !== 'unassigned') url += '&assignee_id=' + staffId;
            fetch(url, { headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    hl();
                    var fd = new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                    var title = document.getElementById('staffPopupTitle');
                    var body = document.getElementById('staffPopupBody');
                    if (staffId && staffName && staffId !== 'null' && staffId !== 'unassigned') title.innerHTML = '<i class="fas fa-user-tie me-1"></i>' + esc(staffName) + ' — ' + fd;
                    else title.innerHTML = '<i class="fas fa-users me-1"></i>Staff Tasks — ' + fd;

                    if (d.tasks && d.tasks.length) {
                        var html = '';
                        if (staffId && staffId !== 'null' && staffId !== 'unassigned') {
                            html += '<div style="margin-bottom:var(--hd-sm);"><button onclick="openStaffTasksPopup(\'' + date + '\')" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:2px 8px;font-size:var(--hd-font-xs);cursor:pointer;">← All Staff</button></div>';
                            html += '<div class="staff-task-list">';
                            d.tasks.forEach(function(t) {
                                html += '<div class="staff-task-item" onclick="openTaskDetailPopup(\'' + t.id + '\',\'' + esc(t.title) + '\',\'' + t.student_id + '\',\'' + esc(t.student_name) + '\',\'' + esc(t.student_country || 'N/A') + '\',\'' + (t.priority_time_slot || 'evening') + '\',\'' + (t.priority || 'medium') + '\',\'' + (t.status) + '\',' + (t.is_overdue ? 'true' : 'false') + ',\'' + esc(t.description || '') + '\')"><div class="task-student-name"><i class="fas fa-user-graduate"></i>' + esc(t.student_name) + '</div><div style="margin-top:2px;"><strong>' + esc(t.title) + '</strong></div><div style="margin-top:2px;display:flex;gap:4px;flex-wrap:wrap;">' + window.CrmCore.getInstance().getPriorityBadge(t.priority) + ' ' + window.CrmCore.getInstance().getStatusBadge(t.status, t.is_overdue) + ' <span class="badge bg-secondary" style="font-size:.55rem;">' + slotLabel(t.priority_time_slot) + '</span></div></div>';
                            });
                            html += '</div>';
                        } else {
                            var byStaff = {};
                            d.tasks.forEach(function(t) { var sn = t.assigned_to_name || 'Unassigned', si = t.assigned_to || 'unassigned'; if (!byStaff[sn]) byStaff[sn] = { staffId: si, tasks: [] }; byStaff[sn].tasks.push(t); });
                            Object.keys(byStaff).forEach(function(sn) {
                                var sd = byStaff[sn];
                                html += '<div class="staff-group"><div class="staff-group-header" data-sid="' + sd.staffId + '" data-sname="' + esc(sn) + '" data-date="' + date + '"><span><i class="fas fa-user-tie me-1"></i>' + esc(sn) + '</span><span class="badge" style="background:#e0e7ff;color:var(--crm-primary);">' + sd.tasks.length + '</span></div>';
                                html += '<div class="staff-task-list">';
                                sd.tasks.forEach(function(t) {
                                    html += '<div class="staff-task-item" onclick="openTaskDetailPopup(\'' + t.id + '\',\'' + esc(t.title) + '\',\'' + t.student_id + '\',\'' + esc(t.student_name) + '\',\'' + esc(t.student_country || 'N/A') + '\',\'' + (t.priority_time_slot || 'evening') + '\',\'' + (t.priority || 'medium') + '\',\'' + (t.status) + '\',' + (t.is_overdue ? 'true' : 'false') + ',\'' + esc(t.description || '') + '\')"><div class="task-student-name"><i class="fas fa-user-graduate"></i>' + esc(t.student_name) + '</div><div><strong>' + esc(t.title) + '</strong></div><div style="display:flex;gap:4px;margin-top:2px;">' + window.CrmCore.getInstance().getPriorityBadge(t.priority) + ' ' + window.CrmCore.getInstance().getStatusBadge(t.status, t.is_overdue) + ' <span class="badge bg-secondary" style="font-size:.55rem;">' + slotLabel(t.priority_time_slot) + '</span></div></div>';
                                });
                                html += '</div></div>';
                            });
                        }
                        body.innerHTML = html;
                        document.querySelectorAll('.staff-group-header').forEach(function(h) {
                            h.addEventListener('click', function() { var sid = this.dataset.sid, sn = this.dataset.sname, d = this.dataset.date; if (sid && sid !== 'unassigned' && sid !== 'null') { closeStaffPopup(); openStaffTasksPopup(d, sid, sn); } });
                        });
                    } else {
                        body.innerHTML = '<div class="text-center py-4 text-muted" style="font-size:var(--hd-font-sm);"><i class="fas fa-calendar-check fa-2x mb-1 d-block"></i>No tasks</div>';
                    }
                    var pop = document.getElementById('staffTasksPopup');
                    pop.style.display = 'block'; pop.style.top = '50%'; pop.style.left = '50%'; pop.style.transform = 'translate(-50%,-50%)';
                })
                .catch(function() { hl(); document.getElementById('staffPopupBody').innerHTML = '<div class="text-center py-4 text-danger" style="font-size:var(--hd-font-sm);">Error</div>'; });
        }

        function openTaskDetailPopup(id, title, sid, sname, scountry, slot, priority, status, overdue, desc) {
            var body = document.getElementById('taskDetailBody');
            var CRM = window.CrmCore?.getInstance();
            body.innerHTML = '<div class="task-detail-item"><div class="task-detail-label">Student</div><div class="task-detail-value"><strong>' + esc(sname) + '</strong> <span class="text-muted">· ' + esc(scountry) + '</span></div></div>' +
                '<div class="task-detail-item"><div class="task-detail-label">Task</div><div class="task-detail-value"><strong>' + esc(title) + '</strong></div></div>' +
                (desc ? '<div class="task-detail-item"><div class="task-detail-label">Description</div><div class="task-detail-value">' + esc(desc) + '</div></div>' : '') +
                '<div class="task-detail-item"><div class="task-detail-label">Time Slot</div><div class="task-detail-value">' + slotLabel(slot) + '</div></div>' +
                '<div class="task-detail-item"><div class="task-detail-label">Priority & Status</div><div class="task-detail-value">' + (CRM ? CRM.getPriorityBadge(priority) : '') + ' ' + (CRM ? CRM.getStatusBadge(status, overdue) : '') + '</div></div>' +
                '<div class="text-center mt-2"><a href="/crm/student/' + sid + '" class="view-student-btn"><i class="fas fa-external-link-alt"></i> View Profile</a></div>';
            var pop = document.getElementById('taskDetailPopup');
            pop.style.display = 'block'; pop.style.top = '50%'; pop.style.left = '50%'; pop.style.transform = 'translate(-50%,-50%)';
        }

        function updateDayNumbers(events) {
            var counts = {};
            events.forEach(function(e) { var d = new Date(e.start).toDateString(); counts[d] = (counts[d] || 0) + 1; });
            document.querySelectorAll('.fc-daygrid-day').forEach(function(day) {
                var date = day.getAttribute('data-date');
                if (date) {
                    var cnt = counts[new Date(date).toDateString()] || 0;
                    var old = day.querySelector('.task-count-badge'); if (old) old.remove();
                    if (cnt > 0) {
                        var top = day.querySelector('.fc-daygrid-day-top');
                        if (top) { var b = document.createElement('span'); b.className = 'task-count-badge'; b.innerHTML = cnt; b.onclick = function(e) { e.stopPropagation(); openStaffTasksPopup(date); }; top.appendChild(b); }
                    }
                }
            });
        }

        function applyColWidths() {
            document.querySelectorAll('.fc-col-header-cell, .fc-daygrid-day').forEach(function(cell) {
                var di = cell.hasAttribute('data-day') ? parseInt(cell.getAttribute('data-day')) : (cell.getAttribute('data-date') ? new Date(cell.getAttribute('data-date')).getDay() : null);
                if (di === 6) { cell.style.width = '8%'; cell.style.minWidth = '50px'; }
                else if (di !== null) { cell.style.width = '15.3%'; cell.style.minWidth = '80px'; }
            });
        }

        function initCalendar() {
            var el = document.getElementById('workCalendar');
            if (!el) return;
            calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
                displayEventTime: false, displayEventEnd: false,
                eventContent: function(arg) {
                    var cls = '';
                    if (arg.event.extendedProps?.staff_name) cls = 'fc-event-staff';
                    else if (arg.event.extendedProps?.is_overdue) cls = 'fc-event-overdue';
                    else if (arg.event.extendedProps?.status === 'completed') cls = 'fc-event-completed';
                    else if (arg.event.extendedProps?.time_slot === 'morning') cls = 'fc-event-morning';
                    else if (arg.event.extendedProps?.time_slot === 'afternoon') cls = 'fc-event-afternoon';
                    else if (arg.event.extendedProps?.time_slot === 'evening') cls = 'fc-event-evening';
                    return { html: '<div class="fc-event-main-frame ' + cls + '"><div class="fc-event-title">' + esc(arg.event.title) + '</div></div>' };
                },
                dayCellDidMount: function(info) { info.el.setAttribute('data-day', info.date.getDay()); if (info.date.getDay() === 6) info.el.classList.add('fc-day-sat'); },
                dayHeaderDidMount: function(info) { info.el.setAttribute('data-day', info.date.getDay()); },
                events: function(info, success, failure) {
                    sl();
                    var params = new URLSearchParams(window.location.search);
                    fetch('{{ route('crm.calendar.events') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() }, body: JSON.stringify({ start: info.startStr, end: info.endStr, assignee_id: params.get('assignee_id'), activity_filter: params.get('activity_filter'), stage_id: params.get('stage_id'), search: params.get('search'), search_type: params.get('search_type') }) })
                        .then(function(r) { return r.json(); })
                        .then(function(d) { hl(); success(d.events || []); setTimeout(function() { updateDayNumbers(d.events || []); applyColWidths(); }, 80); })
                        .catch(function(err) { hl(); failure(err); });
                },
                datesSet: function() { setTimeout(function() { applyColWidths(); document.querySelectorAll('.fc-daygrid-day').forEach(function(day) { var da = day.getAttribute('data-date'); if (da && new Date(da).getDay() === 6) day.classList.add('fc-day-sat'); }); }, 80); },
                dateClick: function(info) { openStaffTasksPopup(info.dateStr); },
                eventClick: function(info) {
                    var p = info.event.extendedProps;
                    if (p?.staff_name && p?.tasks) { openStaffTasksPopup(info.event.startStr.split('T')[0], p.staff_id, p.staff_name); }
                    else if (p?.student_id) { openTaskDetailPopup(p.task_id || info.event.id, p.task_title || info.event.title, p.student_id, p.student_name || 'Unknown', p.student_country || 'N/A', p.time_slot || 'evening', p.priority || 'medium', p.status || 'pending', p.is_overdue || false, p.description || ''); }
                    else { openStaffTasksPopup(info.event.startStr.split('T')[0]); }
                },
                height: 520, weekends: true, nowIndicator: true
            });
            calendar.render();
            setTimeout(function() { applyColWidths(); var ev = calendar.getEvents(); if (ev.length) updateDayNumbers(ev.map(function(e) { return { start: e.start }; })); }, 150);
        }

        @if (($view ?? 'kanban') === 'calendar')
            document.addEventListener('DOMContentLoaded', function() { setTimeout(initCalendar, 80); });
        @endif
    </script>
@endpush
