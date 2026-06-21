@push('styles')
    <style>
        .kanban-wrapper {
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .kanban-scroll-top {
            overflow-x: scroll;
            overflow-y: hidden;
            height: 8px;
            margin-bottom: 2px;
            flex-shrink: 0;
        }

        .kanban-scroll-top::-webkit-scrollbar {
            height: 5px;
        }

        .kanban-scroll-top::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 3px;
        }

        .kanban-scroll-top::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 3px;
        }

        #scrollTopInner {
            height: 1px;
            display: block;
        }

        .kanban-board {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            overflow-y: visible;
            padding-bottom: 4px;
            min-height: calc(100vh - 180px);
        }

        .kanban-board::-webkit-scrollbar {
            height: 5px;
        }

        .kanban-board::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 3px;
        }

        .kanban-col {
            min-width: 290px;
            width: 290px;
            background: var(--glass-bg);
            backdrop-filter: blur(8px);
            border-radius: var(--radius);
            border: 1px solid var(--card-border);
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 110px);
            flex-shrink: 0;
            transition: border .12s, box-shadow .12s;
            box-shadow: var(--card-shadow);
        }

        .kanban-col.drag-over {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 2px rgba(15, 118, 110, .15);
        }

        .kanban-col-header {
            padding: 8px 12px;
            font-weight: 600;
            font-size: var(--font-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0, 0, 0, .05);
            flex-shrink: 0;
            border-radius: var(--radius) var(--radius) 0 0;
        }

        .kanban-col-header-left {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .kanban-col-header-actions {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .stage-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .stage-count-badge {
            font-size: .6rem;
            font-weight: 700;
            background: rgba(0, 0, 0, .06);
            padding: 1px 7px;
            border-radius: 20px;
            color: #475569;
        }

        .add-student-to-col-btn {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-size: .85rem;
            padding: 2px 6px;
            border-radius: 6px;
            line-height: 1;
            transition: all .12s;
        }

        .add-student-to-col-btn:hover {
            background: var(--primary-light);
            transform: scale(1.1);
        }

        .col-progress-bar-wrap {
            padding: 4px 10px 2px;
            background: rgba(0, 0, 0, .02);
            border-bottom: 1px solid rgba(0, 0, 0, .04);
            flex-shrink: 0;
        }

        .col-progress-bar {
            display: flex;
            height: 5px;
            overflow: visible;
            cursor: default;
            border-radius: 3px;
        }

        .cpb-seg {
            height: 5px;
            min-width: 3px;
            flex-shrink: 0;
            position: relative;
            cursor: pointer;
            transition: filter .12s;
        }

        .cpb-seg:hover {
            filter: brightness(1.2);
        }

        .cpb-seg+.cpb-seg {
            border-left: 1px solid rgba(255, 255, 255, .3);
        }

        .cpb-seg::after {
            content: attr(data-tip);
            position: absolute;
            bottom: calc(100% + 5px);
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: #fff;
            font-size: .58rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 4px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity .12s;
            z-index: 100;
        }

        .cpb-seg:hover::after {
            opacity: 1;
        }

        .cpb-seg.cpb-today {
            background: var(--warning);
        }

        .cpb-seg.cpb-upcoming {
            background: #306945;
        }

        .cpb-seg.cpb-overdue {
            background: var(--danger);
        }

        .cpb-seg.cpb-none {
            background: #bcbcbc;
        }

        .cpb-seg.seg-active {
            box-shadow: 0 0 0 2px var(--primary);
        }

        .kanban-col-body {
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            overflow-y: auto;
            flex: 1;
            min-height: 60px;
        }

        .kanban-col-body.drag-over {
            background: rgba(15, 118, 110, .04);
            border-radius: var(--radius-sm);
        }

        .student-card {
            background: white;
            border: 1px solid #e8e8e8;
            border-left: 3px solid transparent;
            border-radius: 8px;
            padding: 0.65rem 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            cursor: grab;
            transition: all 0.15s;
            position: relative;
            user-select: none;
        }

        .student-card:active {
            cursor: grabbing;
        }

        .student-card.dragging {
            opacity: 0.5;
        }

        .student-card.filter-hidden {
            display: none !important;
        }

        .student-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
            border-color: #d1d5db;
            border-left-color: var(--sc, #d1d5db);
        }

        .student-card.pinned {
            border-color: #f59e0b;
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, #fff, #fffbeb);
            box-shadow: 0 1px 4px rgba(245, 158, 11, 0.15);
        }

        .student-card.pinned:hover {
            box-shadow: 0 3px 12px rgba(245, 158, 11, 0.2);
        }

        .student-name {
            font-weight: 700;
            font-size: 0.83rem;
            color: #1f2937;
        }

        .student-name a {
            text-decoration: none;
            color: inherit;
        }

        .student-name a:hover {
            color: #4f46e5;
        }

        .student-sub {
            font-size: 0.65rem;
            color: #6b7280;
            margin-top: 2px;
        }

        .student-sub i {
            width: 13px;
            text-align: center;
            margin-right: 3px;
            color: #9ca3af;
        }

        .card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 6px;
        }

        .card-row+.card-row {
            margin-top: 4px;
        }

        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 1px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 13px;
            color: #d1d5db;
            cursor: pointer;
            line-height: 1;
        }

        .star-rating input:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #fbbf24;
        }

        .followup-badge {
            font-size: 0.55rem;
            font-weight: 600;
            padding: 0.15rem 0.45rem;
            border-radius: 20px;
            display: inline-block;
            text-align: center;
            white-space: nowrap;
        }

        .followup-badge.overdue {
            background: #fef2f2;
            color: #dc2626;
        }

        .followup-badge.today {
            background: #fffbeb;
            color: #d97706;
        }

        .followup-badge.upcoming {
            background: #ede5f8;
            color: #1a0262;
        }

        .followup-badge.none {
            background: #f3f4f6;
            color: #6b7280;
        }

        .pin-btn {
            font-size: .52rem;
            background: #f3f4f6;
            color: #6b7280;
            border: none;
            border-radius: 20px;
            padding: 1px 7px;
            cursor: pointer;
            transition: all .12s;
            line-height: 1.5;
        }

        .pin-btn.pinned {
            background: #f59e0b;
            color: #fff;
        }

        .pin-btn:hover {
            filter: brightness(1.05);
        }

        .add-tag-btn {
            font-size: .52rem;
            background: transparent;
            border: 1px dashed #cbd5e1;
            border-radius: 20px;
            padding: 0.1rem 0.45rem;
            cursor: pointer;
            color: #6b7280;
            transition: all .12s;
            line-height: 1.5;
            flex-shrink: 0;
        }

        .add-tag-btn:hover {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        .assignee-chip {
            font-size: .5rem;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 20px;
            padding: 1px 7px;
            line-height: 1.5;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.2rem;
        }

        .tag {
            font-size: 0.55rem;
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 12px;
            padding: 0.1rem 0.4rem;
            display: inline-flex;
            align-items: center;
            gap: 0.15rem;
        }

        .tag-remove {
            cursor: pointer;
            font-weight: bold;
            margin-left: 0.1rem;
            opacity: 0.7;
        }

        .tag-remove:hover {
            opacity: 1;
        }

        .col-empty-msg {
            text-align: center;
            color: #94a3b8;
            padding: 1.5rem .5rem;
            font-size: .75rem;
        }

        @media (max-width: 768px) {
            .kanban-col {
                min-width: 250px;
                width: 250px;
            }
        }
    </style>
@endpush

<div class="kanban-wrapper" id="kanbanWrapper">
    <div class="kanban-scroll-top" id="kanbanScrollTop">
        <div id="scrollTopInner"></div>
    </div>

    <div class="kanban-board" id="kanbanBoard">
        @foreach ($stages as $stage)
            @php
                $colSt = isset($students[$stage->id]) ? $students[$stage->id] : collect();
            @endphp
            @continue($colSt->isEmpty())
            @php
                $sorted = $colSt->sortByDesc(function ($s) {
                    $p = ($s->pinned ?? 0) == 1;
                    $r = $s->rating ?? 0;
                    $score = 0;
                    if ($p && $r >= 3) {
                        $score = 1e9;
                    } elseif ($p && $r >= 2) {
                        $score = 9e8;
                    } elseif ($p && $r >= 1) {
                        $score = 8e8;
                    } elseif ($r >= 3) {
                        $score = 7e8;
                    } elseif ($r >= 2) {
                        $score = 6e8;
                    } elseif ($r >= 1) {
                        $score = 5e8;
                    } else {
                        $score = 4e8;
                    }
                    return $score + strtotime($s->created_at ?? 0);
                });

                $cT = $cU = $cO = $cN = 0;
                foreach ($sorted as $s) {
                    $od = $s->overdueActivities->count();
                    $up = $s->upcomingActivities->count();
                    $tk = $s->pendingActivities->first();
                    if ($od > 0) {
                        $cO++;
                    } elseif ($tk && $tk->scheduled_for?->isToday()) {
                        $cT++;
                    } elseif ($up > 0) {
                        $cU++;
                    } else {
                        $cN++;
                    }
                }
                $tot = max(1, $cT + $cU + $cO + $cN);
                $pT = round(($cT / $tot) * 100);
                $pU = round(($cU / $tot) * 100);
                $pO = round(($cO / $tot) * 100);
                $pN = max(0, 100 - $pT - $pU - $pO);
            @endphp

            <div class="kanban-col" data-stage-id="{{ $stage->id }}">
                <div class="kanban-col-header" style="background:{{ $stage->color }}44;">
                    <div class="kanban-col-header-left">
                        <span class="stage-dot" style="background:{{ $stage->color }}"></span>
                        <span>{{ $stage->name }}</span>
                        <span class="stage-count-badge">{{ $sorted->count() }}</span>
                    </div>
                    <div class="kanban-col-header-actions">
                        @if (isset($isAdmin) && $isAdmin)
                            <button type="button" class="add-student-to-col-btn"
                                onclick="CrmKanban.openAddStudentModal({{ $stage->id }},'{{ addslashes($stage->name) }}')"
                                title="Add student">
                                <i class="fas fa-plus-circle"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="col-progress-bar-wrap">
                    <div class="col-progress-bar" data-stage="{{ $stage->id }}" data-total="{{ $tot }}"
                        data-today="{{ $cT }}" data-upcoming="{{ $cU }}"
                        data-overdue="{{ $cO }}" data-none="{{ $cN }}">
                        @if ($pT > 0)
                            <div class="cpb-seg cpb-today" style="flex:{{ $pT }}" data-filter="today"
                                data-stage="{{ $stage->id }}" data-tip="Today: {{ $cT }}"></div>
                        @endif
                        @if ($pU > 0)
                            <div class="cpb-seg cpb-upcoming" style="flex:{{ $pU }}" data-filter="upcoming"
                                data-stage="{{ $stage->id }}" data-tip="Upcoming: {{ $cU }}"></div>
                        @endif
                        @if ($pO > 0)
                            <div class="cpb-seg cpb-overdue" style="flex:{{ $pO }}" data-filter="overdue"
                                data-stage="{{ $stage->id }}" data-tip="Overdue: {{ $cO }}"></div>
                        @endif
                        @if ($pN > 0)
                            <div class="cpb-seg cpb-none" style="flex:{{ $pN }}" data-filter="unscheduled"
                                data-stage="{{ $stage->id }}" data-tip="No Tasks: {{ $cN }}"></div>
                        @endif
                    </div>
                </div>

                <div class="kanban-col-body" data-stage-id="{{ $stage->id }}">
                    @forelse($sorted as $s)
                        @php
                            $od = $s->overdueActivities->count();
                            $up = $s->upcomingActivities->count();
                            $tk = $s->pendingActivities->first();
                            $isToday = $tk && $tk->scheduled_for?->isToday();
                            $nx = $tk?->scheduled_for;
                            $rt = $s->rating ?? 0;
                            $pn = ($s->pinned ?? 0) == 1;
                            if ($od > 0) {
                                $fCls = 'overdue';
                                $fLbl = $od . ' overdue';
                                $cf = 'overdue';
                            } elseif ($isToday) {
                                $fCls = 'today';
                                $fLbl = 'Today';
                                $cf = 'today';
                            } elseif ($up > 0) {
                                $fCls = 'upcoming';
                                $fLbl = $nx ? $nx->format('d M') : 'Upcoming';
                                $cf = 'upcoming';
                            } else {
                                $fCls = 'none';
                                $fLbl = 'No tasks';
                                $cf = 'unscheduled';
                            }
                            $assignees = $s->pendingActivities->pluck('assignee')->filter()->unique('id');
                        @endphp

                        <div class="student-card {{ $pn ? 'pinned' : '' }}" style="--sc:{{ $stage->color }}"
                            data-student-id="{{ $s->id }}" data-rating="{{ $rt }}"
                            data-pinned="{{ $pn ? 1 : 0 }}" data-task-filter="{{ $cf }}" draggable="true">

                            {{-- Row 1: name + pin --}}
                            <div class="card-row">
                                <div class="student-name"><a
                                        href="{{ route('crm.students.show', $s) }}">{{ $s->full_name }}</a></div>
                                @if ($pn)
                                    <button class="pin-btn pinned"
                                        onclick="event.stopPropagation();CrmKanban.togglePin({{ $s->id }},0)"
                                        title="Unpin"><i class="fas fa-thumbtack"></i></button>
                                @else
                                    <button class="pin-btn"
                                        onclick="event.stopPropagation();CrmKanban.togglePin({{ $s->id }},1)"
                                        title="Pin"><i class="fas fa-thumbtack"></i></button>
                                @endif
                            </div>

                            {{-- Row 2: country --}}
                            <div class="student-sub"><i class="fas fa-globe-americas"></i>
                                {{ $s->preferred_country ?? 'N/A' }}</div>

                            {{-- Row 3: phone + rating --}}
                            <div class="card-row" style="margin-top:3px;">
                                <span style="font-size:0.65rem;color:#6b7280;"><i class="fas fa-phone-alt"
                                        style="width:13px;text-align:center;margin-right:3px;color:#9ca3af;"></i>
                                    {{ $s->phone_number ?? '—' }}</span>
                                <div class="star-rating" onclick="event.stopPropagation();">
                                    <input type="radio" name="kr_{{ $s->id }}" value="3"
                                        id="k3_{{ $s->id }}" {{ $rt == 3 ? 'checked' : '' }}>
                                    <label for="k3_{{ $s->id }}"
                                        onclick="event.stopPropagation();CrmKanban.updateRating({{ $s->id }},3)">★</label>
                                    <input type="radio" name="kr_{{ $s->id }}" value="2"
                                        id="k2_{{ $s->id }}" {{ $rt == 2 ? 'checked' : '' }}>
                                    <label for="k2_{{ $s->id }}"
                                        onclick="event.stopPropagation();CrmKanban.updateRating({{ $s->id }},2)">★</label>
                                    <input type="radio" name="kr_{{ $s->id }}" value="1"
                                        id="k1_{{ $s->id }}" {{ $rt == 1 ? 'checked' : '' }}>
                                    <label for="k1_{{ $s->id }}"
                                        onclick="event.stopPropagation();CrmKanban.updateRating({{ $s->id }},1)">★</label>
                                </div>
                            </div>

                            {{-- Row 4: tags list + add tag --}}
                            <div class="card-row">
                                <div class="tags-list">
                                    @if ($s->tags && is_array($s->tags))
                                        @foreach ($s->tags as $t)
                                            <span class="tag"><i class="fas fa-tag" style="font-size:.45rem;"></i>
                                                {{ $t }}<span class="tag-remove"
                                                    onclick="event.stopPropagation();CrmKanban.removeTag({{ $s->id }},'{{ addslashes($t) }}')">×</span></span>
                                        @endforeach
                                    @endif
                                </div>
                                <button type="button" class="add-tag-btn"
                                    onclick="event.stopPropagation();CrmKanban.openTagModal({{ $s->id }})"><i
                                        class="fas fa-plus"></i></button>
                            </div>

                            {{-- Row 5: assignee + task date --}}
                            <div class="card-row">
                                <div>
                                    @foreach ($assignees as $a)
                                        <span class="assignee-chip"><i class="fas fa-user-circle"
                                                style="font-size:.45rem;"></i>
                                            {{ \Illuminate\Support\Str::words($a->name, 2, '') }}</span>
                                    @endforeach
                                </div>
                                @if ($od > 0)
                                    <span class="followup-badge overdue"><i class="fas fa-exclamation-circle"
                                            style="font-size:.45rem;margin-right:2px;"></i> Overdue</span>
                                @elseif ($isToday)
                                    <span class="followup-badge today"><i class="fas fa-calendar-day"
                                            style="font-size:.45rem;margin-right:2px;"></i> Today</span>
                                @elseif ($nx)
                                    <span class="followup-badge upcoming"><i class="fas fa-calendar-alt"
                                            style="font-size:.45rem;margin-right:2px;"></i>
                                        {{ $nx->format('d M') }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-empty-msg">No students in this stage</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
    <script>
        window.CrmKanban = (function() {
            'use strict';
            var initd = false,
                curId = null,
                dragging = false,
                raf = null,
                sDir = null,
                cX = 0;
            var board, topScroll, topInner;
            var CRM = window.CrmCore?.getInstance();
            var sl = function() {
                CRM?.showLoader();
            };
            var hl = function() {
                CRM?.hideLoader();
            };
            var st = function(m, t) {
                CRM?.showToast(m, t);
            };
            var csrf = function() {
                return CRM ? CRM.getCsrfToken() : document.querySelector('meta[name="csrf-token"]')?.content;
            };
            var esc = function(s) {
                return CRM ? CRM.escapeHtml(s) : String(s ?? '').replace(/[&<>"']/g, function(c) {
                    return ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    })[c];
                });
            };

            function syncScroll() {
                board = document.getElementById('kanbanBoard');
                topScroll = document.getElementById('kanbanScrollTop');
                topInner = document.getElementById('scrollTopInner');
                if (!board) return;

                function mw() {
                    if (!topInner || !board) return;
                    topInner.style.width = board.scrollWidth + 'px';
                    if (topScroll) topScroll.style.display = board.scrollWidth > board.clientWidth + 2 ? 'block' :
                        'none';
                }
                var ft = false,
                    fb = false;
                if (topScroll) {
                    topScroll.addEventListener('scroll', function() {
                        if (fb) return;
                        ft = true;
                        board.scrollLeft = topScroll.scrollLeft;
                        ft = false;
                    }, {
                        passive: true
                    });
                }
                board.addEventListener('scroll', function() {
                    if (ft) return;
                    fb = true;
                    if (topScroll) topScroll.scrollLeft = board.scrollLeft;
                    fb = false;
                }, {
                    passive: true
                });
                board.addEventListener('wheel', function(e) {
                    if (e.shiftKey) {
                        e.preventDefault();
                        board.scrollLeft += e.deltaY;
                        if (topScroll) topScroll.scrollLeft = board.scrollLeft;
                    }
                }, {
                    passive: false
                });
                var ro = new ResizeObserver(mw);
                ro.observe(board);
                window.addEventListener('resize', mw);
                setTimeout(mw, 150);
                setTimeout(mw, 600);
            }

            function autoScroll(d) {
                if (raf) cancelAnimationFrame(raf);
                var SPEED = 16;
                sDir = d;

                function tick() {
                    if (!dragging || !board) {
                        if (raf) {
                            cancelAnimationFrame(raf);
                            raf = null;
                        }
                        return;
                    }
                    board.scrollLeft += d === 'left' ? -SPEED : SPEED;
                    if (topScroll) topScroll.scrollLeft = board.scrollLeft;
                    raf = requestAnimationFrame(tick);
                }
                raf = requestAnimationFrame(tick);
            }

            function stopScroll() {
                if (raf) {
                    cancelAnimationFrame(raf);
                    raf = null;
                }
                sDir = null;
            }

            function checkEdge(x) {
                if (!board || !dragging) return;
                var r = board.getBoundingClientRect(),
                    Z = 100,
                    vw = window.innerWidth;
                var ex = Math.max(0, Math.min(vw, x));
                var fl = ex - r.left,
                    fr = r.right - ex;
                if (fl < Z && board.scrollLeft > 0) {
                    if (sDir !== 'left') autoScroll('left');
                } else if (fr < Z && board.scrollLeft < board.scrollWidth - board.clientWidth - 1) {
                    if (sDir !== 'right') autoScroll('right');
                } else stopScroll();
            }

            function onDragOver(e) {
                cX = e.clientX;
                checkEdge(e.clientX);
            }

            function dragStart(e) {
                dragging = true;
                this.classList.add('dragging');
                var col = this.closest('.kanban-col');
                if (col) {
                    e.dataTransfer.setData('text/plain', JSON.stringify({
                        studentId: this.dataset.studentId,
                        sourceStageId: col.dataset.stageId
                    }));
                    e.dataTransfer.effectAllowed = 'move';
                }
                var ghost = document.createElement('div');
                ghost.style.cssText = 'position:fixed;top:-999px;left:-999px;width:1px;height:1px;opacity:0;';
                document.body.appendChild(ghost);
                try {
                    e.dataTransfer.setDragImage(ghost, 0, 0);
                } catch (_) {}
                setTimeout(function() {
                    ghost.remove();
                }, 0);
                document.addEventListener('dragover', onDragOver);
            }

            function dragEnd() {
                dragging = false;
                this.classList.remove('dragging');
                stopScroll();
                document.removeEventListener('dragover', onDragOver);
                clearOver();
            }

            function dragOver(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                clearOver();
                var c = e.currentTarget.closest('.kanban-col');
                if (c) c.classList.add('drag-over');
            }

            function dragLeave(e) {
                var c = e.currentTarget.closest('.kanban-col');
                if (c && !c.contains(e.relatedTarget)) c.classList.remove('drag-over');
            }

            function clearOver() {
                document.querySelectorAll('.drag-over').forEach(function(el) {
                    el.classList.remove('drag-over');
                });
            }

            async function drop(e) {
                e.preventDefault();
                e.stopPropagation();
                stopScroll();
                dragging = false;
                clearOver();
                document.removeEventListener('dragover', onDragOver);
                var col = e.currentTarget.closest('.kanban-col');
                if (!col) return;
                var tId = col.dataset.stageId;
                if (!tId) return;
                var d;
                try {
                    d = JSON.parse(e.dataTransfer.getData('text/plain'));
                } catch (_) {
                    return;
                }
                var sid = d.studentId,
                    ssid = d.sourceStageId;
                if (!sid || ssid === tId) return;
                sl();
                try {
                    var r = await fetch('/crm/students/' + sid + '/stage', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf()
                        },
                        body: JSON.stringify({
                            stage_id: parseInt(tId)
                        })
                    });
                    var j = await r.json();
                    if (j.success) {
                        st('Moved', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 250);
                    } else throw new Error(j.error || 'Failed');
                } catch (err) {
                    st(err.message, 'error');
                } finally {
                    hl();
                }
            }

            function initDnd() {
                document.querySelectorAll('.student-card[draggable="true"]').forEach(function(c) {
                    c.ondragstart = dragStart;
                    c.ondragend = dragEnd;
                });
                document.querySelectorAll('.kanban-col-body, .kanban-col').forEach(function(el) {
                    el.ondragover = dragOver;
                    el.ondragleave = dragLeave;
                    el.ondrop = drop;
                });
            }

            var filters = {};

            function applyFilter(sid, f) {
                var body = document.querySelector('.kanban-col-body[data-stage-id="' + sid + '"]');
                var segs = document.querySelectorAll('.col-progress-bar[data-stage="' + sid + '"] .cpb-seg');
                if (!body) return;
                if (filters[sid] === f) {
                    filters[sid] = null;
                    body.querySelectorAll('.student-card').forEach(function(c) {
                        c.classList.remove('filter-hidden');
                    });
                    segs.forEach(function(s) {
                        s.classList.remove('seg-active');
                    });
                    return;
                }
                filters[sid] = f;
                body.querySelectorAll('.student-card').forEach(function(c) {
                    c.classList.toggle('filter-hidden', c.dataset.taskFilter !== f);
                });
                segs.forEach(function(s) {
                    s.classList.toggle('seg-active', s.dataset.filter === f);
                });
            }

            function initProgress() {
                document.querySelectorAll('.cpb-seg').forEach(function(seg) {
                    seg.addEventListener('click', function(e) {
                        e.stopPropagation();
                        applyFilter(this.dataset.stage, this.dataset.filter);
                    });
                });
            }

            async function updateRating(id, r) {
                sl();
                try {
                    var res = await fetch('/crm/student/' + id + '/update-rating', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            rating: r
                        })
                    });
                    var d = await res.json();
                    if (d.success) {
                        st(r > 0 ? 'Rated ' + r + '★' : 'Rating removed', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 400);
                    } else throw new Error(d.error || 'Failed');
                } catch (err) {
                    st('Error updating rating', 'error');
                } finally {
                    hl();
                }
            }

            async function togglePin(id, s) {
                sl();
                try {
                    var r = await fetch('/crm/students/' + id + '/toggle-pin', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            pinned: s
                        })
                    });
                    var d = await r.json();
                    if (d.success) {
                        st(s == 1 ? 'Pinned' : 'Unpinned', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 400);
                    } else throw new Error(d.error || 'Failed');
                } catch (err) {
                    st('Error', 'error');
                } finally {
                    hl();
                }
            }

            function openTagModal(id) {
                curId = id;
                var m = document.getElementById('tagModal');
                if (m) {
                    new bootstrap.Modal(m).show();
                    var i = document.getElementById('tagInput');
                    if (i) i.value = '';
                    loadPopularTags();
                }
            }
            async function loadPopularTags() {
                try {
                    var r = await fetch('/crm/popular-tags', {
                        headers: {
                            'X-CSRF-TOKEN': csrf()
                        }
                    });
                    var d = await r.json();
                    var el = document.getElementById('suggestedTagsList');
                    if (el && d.tags) el.innerHTML = d.tags.map(function(t) {
                        return '<span class="badge bg-light text-dark p-1 me-1 mb-1" style="cursor:pointer;font-size:.55rem;border-radius:4px;" onclick="document.getElementById(\'tagInput\').value=\'' +
                            esc(t) + '\'">' + esc(t) + '</span>';
                    }).join('');
                } catch (e) {
                    console.error(e);
                }
            }
            async function saveTag() {
                var inp = document.getElementById('tagInput');
                var tag = inp?.value.trim();
                if (!tag) {
                    st('Enter a tag', 'error');
                    return;
                }
                sl();
                try {
                    var r = await fetch('/crm/students/' + curId + '/add-tag', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf()
                        },
                        body: JSON.stringify({
                            tag: tag
                        })
                    });
                    var d = await r.json();
                    if (d.success) {
                        updateTags(curId, d.tags);
                        bootstrap.Modal.getInstance(document.getElementById('tagModal'))?.hide();
                        st('Tag added', 'success');
                    } else throw new Error(d.error);
                } catch (err) {
                    st(err.message, 'error');
                } finally {
                    hl();
                }
            }
            async function removeTag(id, tag) {
                if (!confirm('Remove tag "' + tag + '"?')) return;
                sl();
                try {
                    var r = await fetch('/crm/students/' + id + '/remove-tag', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf()
                        },
                        body: JSON.stringify({
                            tag: tag
                        })
                    });
                    var d = await r.json();
                    if (d.success) {
                        updateTags(id, d.tags);
                        st('Tag removed', 'success');
                    } else throw new Error(d.error);
                } catch (err) {
                    st(err.message, 'error');
                } finally {
                    hl();
                }
            }

            function updateTags(id, tags) {
                var card = document.querySelector('.student-card[data-student-id="' + id + '"]');
                var list = card?.querySelector('.tags-list');
                var btn = card?.querySelector('.add-tag-btn');
                if (!list) return;
                if (tags?.length) {
                    list.innerHTML = tags.map(function(t) {
                        return '<span class="tag">' + esc(t) +
                            '<span class="tag-remove" onclick="event.stopPropagation();CrmKanban.removeTag(' +
                            id + ',\'' + esc(t) + '\')">×</span></span>';
                    }).join('');
                } else {
                    list.innerHTML = '';
                }
            }

            function openAddStudentModal(sid, sn) {
                var i = document.getElementById('colStageId');
                if (i) i.value = sid;
                var s = document.getElementById('colStageName');
                if (s) s.innerText = sn;
                var m = document.getElementById('addStudentToColModal');
                if (m) new bootstrap.Modal(m).show();
            }

            function init() {
                if (initd) return;
                syncScroll();
                initDnd();
                initProgress();
                var sb = document.getElementById('saveTagBtn');
                if (sb) sb.addEventListener('click', saveTag);
                initd = true;
            }

            function destroy() {
                stopScroll();
                document.removeEventListener('dragover', onDragOver);
                initd = false;
                dragging = false;
            }
            return {
                init: init,
                destroy: destroy,
                openAddStudentModal: openAddStudentModal,
                openTagModal: openTagModal,
                saveTag: saveTag,
                removeTag: removeTag,
                updateRating: updateRating,
                togglePin: togglePin
            };
        })();

        document.addEventListener('DOMContentLoaded', function() {
            var v = document.querySelector('[name="view"]')?.value || 'kanban';
            if (v === 'kanban') setTimeout(function() {
                window.CrmKanban?.init();
            }, 100);
        });
    </script>
@endpush
