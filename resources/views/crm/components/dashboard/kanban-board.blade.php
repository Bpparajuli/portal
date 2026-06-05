{{-- resources/views/crm/components/dashboard/kanban-board.blade.php --}}
@push('styles')
    <style>
        /* ============================================
                            KANBAN LAYOUT
                            ============================================ */
        .kanban-wrapper {
            position: relative;
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
        }

        /* ---- TOP SCROLL BAR ---- */
        .kanban-scroll-top {
            overflow-x: scroll;
            overflow-y: hidden;
            height: 14px;
            margin-bottom: 4px;
            flex-shrink: 0;
        }

        .kanban-scroll-top::-webkit-scrollbar {
            height: 8px;
        }

        .kanban-scroll-top::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 4px;
        }

        .kanban-scroll-top::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 4px;
        }

        #scrollTopInner {
            height: 1px;
            display: block;
        }

        /* ---- BOARD ---- */
        .kanban-board {
            display: flex;
            gap: 1rem;
            overflow-x: scroll;
            overflow-y: visible;
            padding-bottom: 0.5rem;
            min-height: calc(100vh - 180px);
        }

        .kanban-board::-webkit-scrollbar {
            height: 8px;
        }

        .kanban-board::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 4px;
        }

        .kanban-board::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 4px;
        }

        /* ============================================
                            COLUMN
                            ============================================ */
        .kanban-col {
            min-width: 300px;
            width: 300px;
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 140px);
            flex-shrink: 0;
            transition: border 0.15s, background 0.15s;
        }

        .kanban-col.drag-over {
            background: #f0f9ff;
            border: 2px dashed #4f46e5 !important;
        }

        /* ============================================
                            COLUMN HEADER
                            ============================================ */
        .kanban-col-header {
            padding: 0.6rem 0.85rem;
            font-weight: 600;
            font-size: 0.88rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            background: #fafbfc;
            border-radius: 12px 12px 0 0;
            flex-shrink: 0;
        }

        .kanban-col-header-actions {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .add-student-to-col-btn {
            background: none;
            border: none;
            color: #820b4c;
            cursor: pointer;
            font-size: 1rem;
            padding: 3px 5px;
            border-radius: 20px;
            transition: all 0.2s;
            line-height: 1;
        }

        .add-student-to-col-btn:hover {
            background: #fce7f3;
            transform: scale(1.2);
        }

        /* ============================================
                            PROGRESS BAR TASK SUMMARY
                            ============================================ */
        .col-progress-bar-wrap {
            padding: 6px 8px 5px;
            background: #f9fafb;
            border-bottom: 1px solid #f0f0f0;
            flex-shrink: 0;
        }

        .col-progress-bar {
            display: flex;
            height: 10px;
            overflow: visible;
            cursor: default;
        }

        .cpb-seg {
            height: 10px;
            min-width: 4px;
            flex-shrink: 0;
            position: relative;
            cursor: pointer;
            transition: filter 0.15s, transform 0.15s;
        }

        .cpb-seg:hover {
            filter: brightness(1.12);
            transform: scaleY(1.3);
            z-index: 5;
        }

        .cpb-seg::after {
            content: attr(data-tip);
            position: absolute;
            bottom: calc(100% + 6px);
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 3px 7px;
            border-radius: 5px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s;
            z-index: 100;
        }

        .cpb-seg:hover::after {
            opacity: 1;
        }

        .cpb-seg.cpb-today {
            background: #f59e0b;
        }

        .cpb-seg.cpb-upcoming {
            background: #10b981;
        }

        .cpb-seg.cpb-overdue {
            background: #ef4444;
        }

        .cpb-seg.cpb-none {
            background: #e2e8f0;
        }

        .cpb-seg.seg-active {
            outline: 2px solid #4f46e5;
            outline-offset: 1px;
        }

        /* ============================================
                            COLUMN BODY
                            ============================================ */
        .kanban-col-body {
            padding: 0.6rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            overflow-y: auto;
            flex: 1;
            min-height: 80px;
        }

        .kanban-col-body.drag-over {
            background: #f0f9ff;
            outline: 2px dashed #4f46e5;
            border-radius: 8px;
        }

        /* ============================================
                            STUDENT CARD
                            ============================================ */
        .student-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.7rem;
            cursor: grab;
            transition: box-shadow 0.15s, transform 0.15s, opacity 0.15s;
            position: relative;
            user-select: none;
        }

        .student-card.pinned {
            border-left: 4px solid #f59e0b;
            background: linear-gradient(135deg, #fffdf0 0%, white 100%);
        }

        .student-card:hover {
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .student-card:active {
            cursor: grabbing;
        }

        .student-card.dragging {
            opacity: 0.3 !important;
            cursor: grabbing !important;
        }

        .student-card.filter-hidden {
            display: none !important;
        }

        .student-card>a {
            display: block;
            text-decoration: none;
            color: inherit;
        }

        .student-name {
            font-weight: 600;
            font-size: 0.88rem;
            color: #1f2937;
            padding-right: 68px;
        }

        .student-card .student-name {
            padding-top: 16px;
        }

        /* ---- PINNED BADGE WITH UNPIN BUTTON ---- */
        .pinned-badge {
            position: absolute;
            top: 7px;
            left: 7px;
            background: #f59e0b;
            color: white;
            font-size: 0.58rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            z-index: 2;
        }

        .pinned-badge:hover {
            border: 1px solid black;
        }

        .unpin-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 0.7rem;
            padding: 0;
            margin-left: 4px;
            opacity: 0.8;
            transition: opacity 0.2s;
            pointer-events: auto;
        }

        .unpin-btn:hover {
            opacity: 1;
            transform: scale(1.1);
            background-color: #10b981;

        }

        /* ---- FOLLOW-UP BADGE ---- */
        .followup-badge {
            position: absolute;
            top: 9px;
            right: 9px;
            font-size: 0.6rem;
            font-weight: 600;
            padding: 0.18rem 0.42rem;
            border-radius: 20px;
            pointer-events: none;
            z-index: 2;
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
            background: #f0fdf4;
            color: #10b981;
        }

        .followup-badge.none {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* ============================================
                            STAR RATING - 3 STARS ONLY
                            ============================================ */
        .rating-area {
            position: relative;
            z-index: 10;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 0.5rem;
            font-size: 0.62rem;
            background: transparent;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 0.12rem 0.42rem;
            cursor: pointer;
            color: #6b7280;
            width: auto;
            text-align: center;
            margin-top: 3px;
        }

        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 2px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 14px;
            color: #d1d5db;
            cursor: pointer;
            transition: color 0.1s;
        }

        .star-rating input:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #fbbf24;
        }

        .remove-rating-btn {
            font-size: 0.65rem;
            color: #9ca3af;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.15s, color 0.15s;
            background: none;
            border: none;
            padding: 0;
        }

        .rating-area:hover .remove-rating-btn {
            opacity: 1;

        }

        .remove-rating-btn:hover {
            color: #ef4444;
        }

        /* ============================================
                            TAGS
                        ============================================ */
        .tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            margin: 0.4rem 0 0.2rem;
        }

        .tag {
            font-size: 0.62rem;
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 10px;
            padding: 0.12rem 0.42rem;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
        }

        .tag-remove {
            cursor: pointer;
            font-weight: bold;
            opacity: 0.6;
        }

        .tag-remove:hover {
            opacity: 1;
        }

        .add-tag-btn {
            font-size: 0.62rem;
            background: transparent;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 0.12rem 0.42rem;
            cursor: pointer;
            color: #6b7280;
            width: 100%;
            text-align: center;
            margin-top: 3px;
        }

        .add-tag-btn:hover {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        .col-empty-msg {
            text-align: center;
            color: #9ca3af;
            padding: 1.5rem 0.5rem;
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .kanban-col {
                min-width: 260px;
                width: 260px;
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
                $colStudents = isset($students[$stage->id]) ? $students[$stage->id] : collect();

                $sortedStudents = $colStudents->sortByDesc(function ($s) {
                    $pinned = $s->pinned ?? 0;
                    $rating = $s->rating ?? 0;

                    // Calculate priority score (higher = higher in list)
                    // Priority order:
                    // 1. Pinned + high rating (3 star)
                    // 2. Pinned + medium rating (2 star)
                    // 3. Non-pinned + high rating (3 star)
                    // 4. Everything else sorted by created_at

                    if ($pinned == 1 && $rating >= 3) {
                        // Priority 1: Pinned 3-star students
                        $priority = 1000000;
                    } elseif ($pinned == 1 && $rating >= 2) {
                        // Priority 2: Pinned 2-star students
                        $priority = 900000;
                    } elseif ($pinned == 1 && $rating >= 1) {
                        // Priority 3: Pinned 1-star students
                        $priority = 800000;
                    } elseif ($pinned == 0 && $rating >= 3) {
                        // Priority 4: Non-pinned 3-star students
                        $priority = 700000;
                    } elseif ($pinned == 0 && $rating >= 2) {
                        // Priority 5: Non-pinned 2-star students
                        $priority = 600000;
                    } elseif ($pinned == 0 && $rating >= 1) {
                        // Priority 6: Non-pinned 1-star students
                        $priority = 500000;
                    } else {
                        // Priority 7: No rating
                        $priority = 400000;
                    }

                    // Add timestamp as secondary sort (newer = higher)
                    $timestamp = strtotime($s->created_at ?? 0);

                    // Return combined score (priority * large number + timestamp)
                    return $priority * 100000 + $timestamp;
                });

                $cToday = $cUpcoming = $cOverdue = $cNone = 0;
                foreach ($sortedStudents as $s) {
                    $od = $s->overdueActivities->count();
                    $up = $s->upcomingActivities->count();
                    $t = $s->pendingActivities->first();
                    if ($od > 0) {
                        $cOverdue++;
                    } elseif ($t && $t->scheduled_for?->isToday()) {
                        $cToday++;
                    } elseif ($up > 0) {
                        $cUpcoming++;
                    } else {
                        $cNone++;
                    }
                }
                $total = max(1, $cToday + $cUpcoming + $cOverdue + $cNone);
                $pToday = round(($cToday / $total) * 100);
                $pUpcoming = round(($cUpcoming / $total) * 100);
                $pOverdue = round(($cOverdue / $total) * 100);
                $pNone = max(0, 100 - $pToday - $pUpcoming - $pOverdue);
            @endphp

            <div class="kanban-col" data-stage-id="{{ $stage->id }}">
                <div class="kanban-col-header" style="background:{{ $stage->color }}12;">
                    <span>
                        <span
                            style="background:{{ $stage->color }};width:9px;height:9px;border-radius:50%;display:inline-block;margin-right:6px;"></span>
                        {{ $stage->name }}
                    </span>
                    <div class="kanban-col-header-actions">
                        <span class="badge bg-secondary rounded-pill">{{ $sortedStudents->count() }}</span>
                        @if (isset($isAdmin) && $isAdmin)
                            <button type="button" class="add-student-to-col-btn"
                                onclick="window.CrmKanban.openAddStudentModal({{ $stage->id }},'{{ addslashes($stage->name) }}')"
                                title="Add student">
                                <i class="fas fa-plus-circle"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="col-progress-bar-wrap">
                    <div class="col-progress-bar" data-stage="{{ $stage->id }}" data-total="{{ $total }}"
                        data-today="{{ $cToday }}" data-upcoming="{{ $cUpcoming }}"
                        data-overdue="{{ $cOverdue }}" data-none="{{ $cNone }}">
                        @if ($pToday > 0)
                            <div class="cpb-seg cpb-today" style="flex:{{ $pToday }}" data-filter="today"
                                data-stage="{{ $stage->id }}" data-tip="📅 Today: {{ $cToday }}"></div>
                        @endif
                        @if ($pUpcoming > 0)
                            <div class="cpb-seg cpb-upcoming" style="flex:{{ $pUpcoming }}" data-filter="upcoming"
                                data-stage="{{ $stage->id }}" data-tip="🚀 Upcoming: {{ $cUpcoming }}"></div>
                        @endif
                        @if ($pOverdue > 0)
                            <div class="cpb-seg cpb-overdue" style="flex:{{ $pOverdue }}" data-filter="overdue"
                                data-stage="{{ $stage->id }}" data-tip="⚠️ Overdue: {{ $cOverdue }}"></div>
                        @endif
                        @if ($pNone > 0)
                            <div class="cpb-seg cpb-none" style="flex:{{ $pNone }}" data-filter="unscheduled"
                                data-stage="{{ $stage->id }}" data-tip="📋 No Tasks: {{ $cNone }}"></div>
                        @endif
                    </div>
                </div>

                <div class="kanban-col-body" data-stage-id="{{ $stage->id }}">
                    @forelse($sortedStudents as $student)
                        @php
                            $overdue = $student->overdueActivities->count();
                            $upcoming = $student->upcomingActivities->count();
                            $task = $student->pendingActivities->first();
                            $isToday = $task && $task->scheduled_for?->isToday();
                            $nextDate = $task?->scheduled_for;
                            $rating = $student->rating ?? 0;
                            $pinned = $student->pinned ?? 0;
                            $isPinned = $pinned == 1;

                            if ($overdue > 0) {
                                $fClass = 'overdue';
                                $fLabel = "⚠️ {$overdue} overdue";
                                $cf = 'overdue';
                            } elseif ($isToday) {
                                $fClass = 'today';
                                $fLabel = '📅 Today';
                                $cf = 'today';
                            } elseif ($upcoming > 0) {
                                $fClass = 'upcoming';
                                $fLabel = '📅 ' . ($nextDate ? $nextDate->format('d M') : 'Upcoming');
                                $cf = 'upcoming';
                            } else {
                                $fClass = 'none';
                                $fLabel = 'No tasks';
                                $cf = 'unscheduled';
                            }
                        @endphp

                        <div class="student-card {{ $isPinned ? 'pinned' : '' }}"
                            data-student-id="{{ $student->id }}" data-rating="{{ $rating }}"
                            data-pinned="{{ $pinned }}" data-task-filter="{{ $cf }}" draggable="true">

                            @php
                                $isPinned = $student->pinned == 1; // Use pinned column, not rating
                            @endphp


                            <div class="followup-badge {{ $fClass }}">{{ $fLabel }}</div>

                            <a href="{{ route('crm.student.show', $student) }}">
                                <div class="student-name">{{ $student->full_name }}</div>
                                <div class="small text-muted mt-1">
                                    <i class="fas fa-globe-americas"></i>
                                    {{ $student->preferred_country ?? 'N/A' }}
                                </div>
                                <div class="small mt-1">
                                    <i class="fas fa-phone-alt"></i>
                                    {{ $student->phone_number ?? '—' }}
                                </div>
                                @if ($isAdmin)
                                    @php
                                        // Get unique assignees from all pending tasks
                                        $assignees = $student->pendingActivities
                                            ->pluck('assignee')
                                            ->filter()
                                            ->unique('id');
                                    @endphp
                                    @if ($assignees->count() > 0)
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-user-tie"></i>
                                            @foreach ($assignees as $assignee)
                                                <span class="badge bg-secondary me-1">{{ $assignee->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-user-tie"></i> Unassigned
                                        </div>
                                    @endif
                                @endif
                            </a>

                            <div class="tags-list">
                                @if ($student->tags && is_array($student->tags))
                                    @foreach ($student->tags as $tag)
                                        <span class="tag">
                                            <i class="fas fa-tag"></i> {{ $tag }}
                                            <span class="tag-remove"
                                                onclick="event.stopPropagation(); window.CrmKanban.removeTag({{ $student->id }},'{{ addslashes($tag) }}')">×</span>
                                        </span>
                                    @endforeach
                                @endif
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="add-tag-btn" data-student-id="{{ $student->id }}"
                                    onclick="event.stopPropagation(); window.CrmKanban.openTagModal({{ $student->id }})">
                                    <i class="fas fa-plus"></i> Add tag
                                </button>
                                {{-- Rating Area - 3 Stars Only --}}
                                <div class="rating-area" onclick="event.stopPropagation();">
                                    <div class="star-rating" data-student-id="{{ $student->id }}">
                                        <input type="radio" name="rating_{{ $student->id }}" value="3"
                                            id="star3_{{ $student->id }}" {{ $rating == 3 ? 'checked' : '' }}>
                                        <label for="star3_{{ $student->id }}" title="3 stars">★</label>

                                        <input type="radio" name="rating_{{ $student->id }}" value="2"
                                            id="star2_{{ $student->id }}" {{ $rating == 2 ? 'checked' : '' }}>
                                        <label for="star2_{{ $student->id }}" title="2 stars">★</label>

                                        <input type="radio" name="rating_{{ $student->id }}" value="1"
                                            id="star1_{{ $student->id }}" {{ $rating == 1 ? 'checked' : '' }}>
                                        <label for="star1_{{ $student->id }}" title="1 star">★</label>
                                    </div>
                                    @if ($rating > 0)
                                        <button type="button" class="remove-rating-btn" title="Remove rating"
                                            onclick="event.stopPropagation(); window.CrmKanban.updateRating({{ $student->id }}, 0)">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    @endif
                                </div>

                            </div>
                            <div class="pin">
                                @if ($isPinned)
                                    <div class="pinned-badge">
                                        <i class="fas fa-thumbtack"></i> Pinned
                                        <button type="button" class="unpin-btn"
                                            onclick="event.stopPropagation(); window.CrmKanban.togglePin({{ $student->id }}, 0)"
                                            title="Unpin student">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @else
                                    <div class="pinned-badge"
                                        style="background: #d1d5db ; color:#000000; cursor: pointer;"
                                        onclick="event.stopPropagation(); window.CrmKanban.togglePin({{ $student->id }}, 1)">
                                        📍 Pin
                                    </div>
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

            let isInitialized = false;
            let currentStudentId = null;
            let isDragging = false;
            let rafId = null;
            let scrollDir = null;
            let dragClientX = 0;

            let board = null;
            let topScroll = null;
            let topInner = null;
            let wrapper = null;

            const CRM = window.CrmCore?.getInstance();
            const showLoader = () => CRM?.showLoader();
            const hideLoader = () => CRM?.hideLoader();
            const showToast = (m, t) => CRM?.showToast(m, t);
            const getCsrf = () => CRM ? CRM.getCsrfToken() : document.querySelector('meta[name="csrf-token"]')
                ?.content;
            const escHtml = (s) => CRM ? CRM.escapeHtml(s) : String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));

            function initScrollSync() {
                board = document.getElementById('kanbanBoard');
                topScroll = document.getElementById('kanbanScrollTop');
                topInner = document.getElementById('scrollTopInner');
                wrapper = document.getElementById('kanbanWrapper');

                if (!board) return;

                function matchWidth() {
                    if (!topInner || !board) return;
                    topInner.style.width = board.scrollWidth + 'px';
                    if (topScroll) {
                        topScroll.style.display = board.scrollWidth > board.clientWidth + 2 ? 'block' : 'none';
                    }
                }

                let fromTop = false,
                    fromBoard = false;

                if (topScroll) {
                    topScroll.addEventListener('scroll', () => {
                        if (fromBoard) return;
                        fromTop = true;
                        board.scrollLeft = topScroll.scrollLeft;
                        fromTop = false;
                    }, {
                        passive: true
                    });
                }

                board.addEventListener('scroll', () => {
                    if (fromTop) return;
                    fromBoard = true;
                    if (topScroll) topScroll.scrollLeft = board.scrollLeft;
                    fromBoard = false;
                }, {
                    passive: true
                });

                board.addEventListener('wheel', (e) => {
                    if (e.shiftKey) {
                        e.preventDefault();
                        board.scrollLeft += e.deltaY;
                        if (topScroll) topScroll.scrollLeft = board.scrollLeft;
                    }
                }, {
                    passive: false
                });

                const ro = new ResizeObserver(matchWidth);
                ro.observe(board);
                window.addEventListener('resize', matchWidth);
                setTimeout(matchWidth, 200);
                setTimeout(matchWidth, 800);
            }

            function startAutoScroll(dir) {
                stopAutoScroll();
                const SPEED = 18;
                scrollDir = dir;

                function tick() {
                    if (!isDragging || !board) {
                        stopAutoScroll();
                        return;
                    }
                    board.scrollLeft += dir === 'left' ? -SPEED : SPEED;
                    if (topScroll) topScroll.scrollLeft = board.scrollLeft;
                    rafId = requestAnimationFrame(tick);
                }
                rafId = requestAnimationFrame(tick);
            }

            function stopAutoScroll() {
                if (rafId) {
                    cancelAnimationFrame(rafId);
                    rafId = null;
                }
                scrollDir = null;
            }

            function checkEdge(clientX) {
                if (!board || !isDragging) return;
                const rect = board.getBoundingClientRect();
                const ZONE = 120;
                const viewW = window.innerWidth;
                const effectiveX = Math.max(0, Math.min(viewW, clientX));
                const fromLeft = effectiveX - rect.left;
                const fromRight = rect.right - effectiveX;

                if (fromLeft < ZONE && board.scrollLeft > 0) {
                    if (scrollDir !== 'left') startAutoScroll('left');
                } else if (fromRight < ZONE && board.scrollLeft < board.scrollWidth - board.clientWidth - 1) {
                    if (scrollDir !== 'right') startAutoScroll('right');
                } else {
                    stopAutoScroll();
                }
            }

            function onDocDragOver(e) {
                dragClientX = e.clientX;
                checkEdge(e.clientX);
            }

            function handleDragStart(e) {
                isDragging = true;
                this.classList.add('dragging');

                const col = this.closest('.kanban-col');
                if (col) {
                    e.dataTransfer.setData('text/plain', JSON.stringify({
                        studentId: this.dataset.studentId,
                        sourceStageId: col.dataset.stageId
                    }));
                    e.dataTransfer.effectAllowed = 'move';
                }

                const ghost = document.createElement('div');
                ghost.style.cssText = 'position:fixed;top:-999px;left:-999px;width:1px;height:1px;opacity:0;';
                document.body.appendChild(ghost);
                try {
                    e.dataTransfer.setDragImage(ghost, 0, 0);
                } catch (_) {}
                setTimeout(() => ghost.remove(), 0);

                document.addEventListener('dragover', onDocDragOver);
            }

            function handleDragEnd() {
                isDragging = false;
                this.classList.remove('dragging');
                stopAutoScroll();
                document.removeEventListener('dragover', onDocDragOver);
                clearDragOver();
            }

            function handleDragOver(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                clearDragOver();
                const col = e.currentTarget.closest('.kanban-col') || (e.currentTarget.classList.contains(
                    'kanban-col') ? e.currentTarget : null);
                if (col) col.classList.add('drag-over');
            }

            function handleDragLeave(e) {
                const col = e.currentTarget.closest('.kanban-col') || (e.currentTarget.classList.contains(
                    'kanban-col') ? e.currentTarget : null);
                if (col && !col.contains(e.relatedTarget)) {
                    col.classList.remove('drag-over');
                }
            }

            function clearDragOver() {
                document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
            }

            async function handleDrop(e) {
                e.preventDefault();
                e.stopPropagation();
                stopAutoScroll();
                isDragging = false;
                clearDragOver();
                document.removeEventListener('dragover', onDocDragOver);

                const col = e.currentTarget.closest('.kanban-col') || (e.currentTarget.classList.contains(
                    'kanban-col') ? e.currentTarget : null);
                if (!col) return;
                const targetStageId = col.dataset.stageId;
                if (!targetStageId) return;

                let data;
                try {
                    data = JSON.parse(e.dataTransfer.getData('text/plain'));
                } catch {
                    return;
                }

                const {
                    studentId,
                    sourceStageId
                } = data;
                if (!studentId || sourceStageId === targetStageId) return;

                showLoader();
                try {
                    const res = await fetch(`/crm/students/${studentId}/stage`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrf()
                        },
                        body: JSON.stringify({
                            stage_id: parseInt(targetStageId)
                        })
                    });
                    const result = await res.json();
                    if (result.success) {
                        showToast('Student moved successfully', 'success');
                        setTimeout(() => location.reload(), 300);
                    } else {
                        throw new Error(result.error || 'Move failed');
                    }
                } catch (err) {
                    showToast(err.message, 'error');
                } finally {
                    hideLoader();
                }
            }

            function initDragAndDrop() {
                document.querySelectorAll('.student-card[draggable="true"]').forEach(card => {
                    card.ondragstart = handleDragStart;
                    card.ondragend = handleDragEnd;
                });
                document.querySelectorAll('.kanban-col-body, .kanban-col').forEach(el => {
                    el.ondragover = handleDragOver;
                    el.ondragleave = handleDragLeave;
                    el.ondrop = handleDrop;
                });
            }

            const activeFilters = {};

            function applyColFilter(stageId, filter) {
                const body = document.querySelector(`.kanban-col-body[data-stage-id="${stageId}"]`);
                const segs = document.querySelectorAll(`.col-progress-bar[data-stage="${stageId}"] .cpb-seg`);
                if (!body) return;

                if (activeFilters[stageId] === filter) {
                    activeFilters[stageId] = null;
                    body.querySelectorAll('.student-card').forEach(c => c.classList.remove('filter-hidden'));
                    segs.forEach(s => s.classList.remove('seg-active'));
                    return;
                }
                activeFilters[stageId] = filter;

                body.querySelectorAll('.student-card').forEach(card => {
                    card.classList.toggle('filter-hidden', card.dataset.taskFilter !== filter);
                });
                segs.forEach(s => {
                    s.classList.toggle('seg-active', s.dataset.filter === filter);
                });
            }

            function initProgressBars() {
                document.querySelectorAll('.cpb-seg').forEach(seg => {
                    seg.addEventListener('click', function(e) {
                        e.stopPropagation();
                        applyColFilter(this.dataset.stage, this.dataset.filter);
                    });
                });
            }

            // ============================================
            // RATING FUNCTIONS - FIXED
            // ============================================
            async function updateRating(studentId, rating) {
                showLoader();
                try {
                    const response = await fetch(`/crm/student/${studentId}/update-rating`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrf(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            rating: rating
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast(rating > 0 ? `Rating updated to ${rating}★` : 'Rating removed', 'success');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        throw new Error(data.error || 'Failed to update rating');
                    }
                } catch (err) {
                    console.error('Rating error:', err);
                    showToast('Error updating rating', 'error');
                } finally {
                    hideLoader();
                }
            }

            // ============================================
            // PIN/UNPIN FUNCTIONS
            // ============================================
            async function togglePin(studentId, pinStatus) {
                showLoader();
                try {
                    const response = await fetch(`/crm/students/${studentId}/toggle-pin`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrf(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            pinned: pinStatus
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast(pinStatus == 1 ? 'Student pinned' : 'Student unpinned', 'success');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        throw new Error(data.error || 'Failed to update pin status');
                    }
                } catch (err) {
                    console.error('Pin error:', err);
                    showToast('Error updating pin status', 'error');
                } finally {
                    hideLoader();
                }
            }

            // ============================================
            // RATING INIT - Fix event handlers
            // ============================================
            function initRatings() {
                // Handle star clicks
                document.querySelectorAll('.star-rating label').forEach(label => {
                    label.removeEventListener('click', starClickHandler);
                    label.addEventListener('click', starClickHandler);
                });

                function starClickHandler(e) {
                    e.stopPropagation();
                    e.preventDefault();

                    const starRatingDiv = this.closest('.star-rating');
                    const studentId = starRatingDiv.dataset.studentId;
                    const forId = this.getAttribute('for');
                    const radio = document.getElementById(forId);

                    if (radio && !radio.checked) {
                        radio.checked = true;
                        const ratingValue = parseInt(radio.value);
                        updateRating(studentId, ratingValue);
                    }
                }
            }

            // ============================================
            // TAG FUNCTIONS
            // ============================================
            function openTagModal(id) {
                currentStudentId = id;
                const modal = document.getElementById('tagModal');
                if (!modal) return;
                new bootstrap.Modal(modal).show();
                const input = document.getElementById('tagInput');
                if (input) input.value = '';
                loadPopularTags();
            }

            async function loadPopularTags() {
                try {
                    const res = await fetch('/crm/popular-tags', {
                        headers: {
                            'X-CSRF-TOKEN': getCsrf()
                        }
                    });
                    const data = await res.json();
                    const el = document.getElementById('suggestedTagsList');
                    if (el && data.tags) {
                        el.innerHTML = data.tags.map(t =>
                            `<span class="badge bg-light text-dark p-2 me-1 mb-1" style="cursor:pointer"
          onclick="document.getElementById('tagInput').value='${escHtml(t)}'">
          🏷️ ${escHtml(t)}
      </span>`
                        ).join('');
                    }
                } catch (e) {
                    console.error(e);
                }
            }

            async function saveTag() {
                const input = document.getElementById('tagInput');
                const tag = input?.value.trim();
                if (!tag) {
                    showToast('Please enter a tag', 'error');
                    return;
                }
                showLoader();
                try {
                    const res = await fetch(`/crm/students/${currentStudentId}/add-tag`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrf()
                        },
                        body: JSON.stringify({
                            tag
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        updateTagsInCard(currentStudentId, data.tags);
                        bootstrap.Modal.getInstance(document.getElementById('tagModal'))?.hide();
                        showToast('Tag added', 'success');
                    } else throw new Error(data.error);
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
                    const res = await fetch(`/crm/students/${id}/remove-tag`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrf()
                        },
                        body: JSON.stringify({
                            tag
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        updateTagsInCard(id, data.tags);
                        showToast('Tag removed', 'success');
                    } else throw new Error(data.error);
                } catch (err) {
                    showToast(err.message, 'error');
                } finally {
                    hideLoader();
                }
            }

            function updateTagsInCard(id, tags) {
                const card = document.querySelector(`.student-card[data-student-id="${id}"]`);
                const list = card?.querySelector('.tags-list');
                if (!list) return;
                list.innerHTML = (tags?.length > 0) ?
                    tags.map(t =>
                        `<span class="tag"><i class="fas fa-tag"></i> ${escHtml(t)}<span class="tag-remove" onclick="event.stopPropagation();window.CrmKanban.removeTag(${id},'${escHtml(t)}')">×</span></span>`
                    ).join('') : '';
            }

            function openAddStudentModal(stageId, stageName) {
                const input = document.getElementById('colStageId');
                const span = document.getElementById('colStageName');
                if (input) input.value = stageId;
                if (span) span.innerText = stageName;
                const modal = document.getElementById('addStudentToColModal');
                if (modal) new bootstrap.Modal(modal).show();
            }

            function init() {
                if (isInitialized) return;

                initScrollSync();
                initDragAndDrop();
                initRatings();
                initProgressBars();

                document.body.addEventListener('click', function(e) {
                    const btn = e.target.closest('.add-tag-btn');
                    if (btn && btn.closest('.kanban-board')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const id = btn.dataset.studentId;
                        if (id) openTagModal(id);
                    }
                });

                const saveBtn = document.getElementById('saveTagBtn');
                if (saveBtn) saveBtn.addEventListener('click', saveTag);

                isInitialized = true;
            }

            function destroy() {
                stopAutoScroll();
                document.removeEventListener('dragover', onDocDragOver);
                isInitialized = false;
                isDragging = false;
            }

            return {
                init,
                destroy,
                openAddStudentModal,
                openTagModal,
                saveTag,
                removeTag,
                updateRating,
                togglePin
            };
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const view = document.querySelector('[name="view"]')?.value || 'kanban';
            if (view === 'kanban') {
                setTimeout(() => window.CrmKanban?.init(), 150);
            }
        });
    </script>
@endpush
