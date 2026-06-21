@push('styles')
    <style>
        .list-view-table .table { margin-bottom: 0; }
        .list-view-table .table td, .list-view-table .table th {
            padding: 5px 7px !important; font-size: var(--hd-font-sm); vertical-align: middle;
            white-space: nowrap; border-color: #f3f4f6;
        }
        .list-view-table .table th {
            font-weight: 600; font-size: var(--hd-font-xs); text-transform: uppercase;
            letter-spacing: .3px; color: #6b7280; background: #f9fafb;
        }
        .list-view-table .table tbody tr { transition: background var(--hd-transition); cursor: pointer; }
        .list-view-table .table tbody tr:hover { background: #f5f3ff; }
        .list-view-table .table td a { text-decoration: none; }
    </style>
@endpush

<div class="list-view-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Country</th>
                    <th>Stage</th>
                    @if ($isAdmin)<th>Staff</th>@endif
                    <th>Tasks</th>
                    <th>Rating</th>
                    <th>Tags</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($students ?? [] as $s)
                    <tr>
                        <td class="fw-semibold">
                            <a href="{{ route('crm.students.show', $s) }}" class="text-dark">{{ $s->full_name }}</a>
                        </td>
                        <td>
                            <div style="font-size:var(--hd-font-xs)">{{ $s->phone_number ?? '—' }}</div>
                            <div style="font-size:var(--hd-font-xs);color:#6b7280">{{ $s->email ?? '' }}</div>
                        </td>
                        <td style="font-size:var(--hd-font-xs)">{{ $s->preferred_country ?? '—' }}</td>
                        <td>
                            <select class="form-select stage-select" data-student-id="{{ $s->id }}"
                                style="font-size:.58rem;padding:1px 4px;min-width:80px;border-radius:4px;border-color:#e2e8f0;background:#f8fafc;cursor:pointer;">
                                @foreach ($stages as $stg)
                                    <option value="{{ $stg->id }}" @selected(($s->currentStage?->id ?? '') == $stg->id)>{{ $stg->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        @if ($isAdmin)
                            <td>
                                @php $as = $s->pendingActivities->pluck('assignee')->filter()->unique('id'); @endphp
                                @if ($as->count())
                                    <div style="display:flex;flex-wrap:wrap;gap:2px;">
                                        @foreach ($as as $a)
                                             <span style="font-size:.55rem;background:#f3f4f6;color:#374151;border-radius:3px;padding:1px 4px;">{{ \Illuminate\Support\Str::words($a->name, 2, '') }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="font-size:.55rem;color:#9ca3af">—</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            @php
                                $od = $s->overdueActivities->count();
                                $up = $s->upcomingActivities->count();
                                $ct = $s->activities()->where('status','completed')->whereDate('completed_at', today())->count();
                            @endphp
                            <div style="display:flex;gap:2px;flex-wrap:wrap;">
                                @if ($od > 0)<span class="badge bg-danger" style="font-size:.55rem;">{{ $od }} overdue</span>@endif
                                @if ($up > 0)<span class="badge bg-success" style="font-size:.55rem;">{{ $up }} upcoming</span>@endif
                                @if ($ct > 0)<span class="badge bg-info" style="font-size:.55rem;">{{ $ct }} done</span>@endif
                                @if ($od == 0 && $up == 0 && $ct == 0)<span style="font-size:.55rem;color:#9ca3af">—</span>@endif
                            </div>
                        </td>
                        <td>
                            <div class="hd-star" data-sid="{{ $s->id }}">
                                @php $r = $s->rating ?? 0; @endphp
                                <input type="radio" name="lr_{{ $s->id }}" value="3" id="l3_{{ $s->id }}" {{ $r == 3 ? 'checked' : '' }}>
                                <label for="l3_{{ $s->id }}">★</label>
                                <input type="radio" name="lr_{{ $s->id }}" value="2" id="l2_{{ $s->id }}" {{ $r == 2 ? 'checked' : '' }}>
                                <label for="l2_{{ $s->id }}">★</label>
                                <input type="radio" name="lr_{{ $s->id }}" value="1" id="l1_{{ $s->id }}" {{ $r == 1 ? 'checked' : '' }}>
                                <label for="l1_{{ $s->id }}">★</label>
                            </div>
                        </td>
                        <td>
                            <div class="hd-tags">
                                @if ($s->tags && is_array($s->tags))
                                    @foreach ($s->tags as $t)
                                        <span class="hd-tag">{{ $t }}<span class="hd-tag-remove" onclick="event.stopPropagation();CrmListHelper.removeTag({{ $s->id }},'{{ addslashes($t) }}')">×</span></span>
                                    @endforeach
                                @endif
                                <button class="hd-add-tag" data-sid="{{ $s->id }}">+</button>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('crm.students.show', $s) }}" class="btn btn-sm btn-outline-primary" style="padding:1px 6px;font-size:.6rem;"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 9 : 8 }}" class="text-center py-4 text-muted" style="font-size:var(--hd-font-sm);">
                            <i class="fas fa-inbox fa-2x mb-1 d-block"></i>No students found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if (isset($students) && method_exists($students, 'links'))
        <div style="padding:var(--hd-md)">{{ $students->withQueryString()->links() }}</div>
    @endif
</div>

@push('scripts')
    <script>
        window.CrmListHelper = (function() {
            'use strict';
            var CRM = window.CrmCore?.getInstance(), curId = null;
            var sl = function() { CRM?.showLoader(); };
            var hl = function() { CRM?.hideLoader(); };
            var st = function(m, t) { CRM?.showToast(m, t); };
            var csrf = function() { return CRM ? CRM.getCsrfToken() : document.querySelector('meta[name="csrf-token"]')?.content; };
            var esc = function(s) { return CRM ? CRM.escapeHtml(s) : String(s ?? ''); };

            function openTagModal(id) { curId = id; var m = document.getElementById('tagModal'); if (m) { new bootstrap.Modal(m).show(); var i = document.getElementById('tagInput'); if (i) i.value = ''; loadPopularTags(); } }
            async function loadPopularTags() {
                try { var r = await fetch('{{ route('crm.student.popularTags') }}', { headers: { 'X-CSRF-TOKEN': csrf() } }); var d = await r.json(); var el = document.getElementById('suggestedTagsList'); if (el && d.tags) el.innerHTML = d.tags.map(function(t) { return '<span class=\"badge bg-light text-dark p-1 me-1 mb-1\" style=\"cursor:pointer;font-size:.6rem;\" onclick=\"document.getElementById(\'tagInput\').value=\'' + esc(t) + '\'">' + esc(t) + '</span>'; }).join(''); } catch(e) {}
            }
            async function saveTag() {
                var inp = document.getElementById('tagInput'), tag = inp?.value.trim();
                if (!tag) { st('Enter a tag', 'error'); return; }
                sl();
                try { var r = await fetch('/crm/students/' + curId + '/add-tag', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() }, body: JSON.stringify({ tag: tag }) }); var d = await r.json(); if (d.success) { updateTags(curId, d.tags); bootstrap.Modal.getInstance(document.getElementById('tagModal'))?.hide(); st('Tag added', 'success'); } else throw new Error(d.error); } catch(err) { st(err.message, 'error'); } finally { hl(); }
            }
            async function removeTag(id, tag) { if (!confirm('Remove tag "' + tag + '"?')) return; sl(); try { var r = await fetch('/crm/students/' + id + '/remove-tag', { method: 'DELETE', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() }, body: JSON.stringify({ tag: tag }) }); var d = await r.json(); if (d.success) { updateTags(id, d.tags); st('Tag removed', 'success'); } else throw new Error(d.error); } catch(err) { st(err.message, 'error'); } finally { hl(); } }
            function updateTags(id, tags) {
                var row = document.querySelector('button.hd-add-tag[data-sid="' + id + '"]')?.closest('td');
                if (!row) return;
                var list = row.querySelector('.hd-tags');
                if (!list) return;
                if (tags?.length) {
                    list.innerHTML = tags.map(function(t) { return '<span class="hd-tag">' + esc(t) + '<span class="hd-tag-remove" onclick="event.stopPropagation();CrmListHelper.removeTag(' + id + ',\'' + esc(t) + '\')">×</span></span>'; }).join('');
                    list.innerHTML += '<button class="hd-add-tag" data-sid="' + id + '">+</button>';
                } else { list.innerHTML = '<button class="hd-add-tag" data-sid="' + id + '">+</button>'; }
                list.querySelector('.hd-add-tag')?.addEventListener('click', function(e) { e.stopPropagation(); openTagModal(id); });
            }
            async function handleRating(radio) {
                sl();
                try { var r = await fetch('/crm/student/' + radio.closest('.hd-star').dataset.sid + '/update-rating', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }, body: JSON.stringify({ rating: parseInt(radio.value) }) }); var d = await r.json(); if (d.success) st('Rated', 'success'); else throw new Error(d.error); } catch(err) { st('Error', 'error'); } finally { hl(); }
            }

            async function handleStageChange(sel) {
                var id = sel.dataset.studentId, stageId = sel.value;
                sl();
                try {
                    var r = await fetch('/crm/students/' + id + '/stage', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() }, body: JSON.stringify({ stage_id: parseInt(stageId) }) });
                    var d = await r.json();
                    if (d.success) { st('Stage updated', 'success'); } else throw new Error(d.error || 'Failed');
                } catch(err) { st(err.message, 'error'); sel.value = sel.dataset.origVal || sel.value; } finally { hl(); }
            }

            function init() {
                document.querySelectorAll('.list-view-table .hd-star label').forEach(function(lbl) {
                    lbl.addEventListener('click', function(e) { e.stopPropagation(); e.preventDefault(); var radio = document.getElementById(this.getAttribute('for')); if (radio && !radio.checked) { radio.checked = true; handleRating(radio); } });
                });
                document.querySelectorAll('.list-view-table .stage-select').forEach(function(sel) {
                    sel.dataset.origVal = sel.value;
                    sel.addEventListener('change', function() { handleStageChange(this); });
                });
                document.body.addEventListener('click', function(e) {
                    var btn = e.target.closest('.hd-add-tag');
                    if (btn && btn.closest('.list-view-table')) { e.preventDefault(); e.stopPropagation(); var id = btn.dataset.sid; if (id) openTagModal(id); }
                });
                document.getElementById('saveTagBtn')?.addEventListener('click', saveTag);
            }
            return { init: init, openTagModal: openTagModal, saveTag: saveTag, removeTag: removeTag };
        })();

        document.addEventListener('DOMContentLoaded', function() {
            if ((document.querySelector('[name="view"]')?.value || 'kanban') === 'list') {
                setTimeout(function() { window.CrmListHelper?.init(); }, 100);
            }
        });
    </script>
@endpush
