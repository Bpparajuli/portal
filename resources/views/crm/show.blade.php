{{-- resources/views/crm/show.blade.php --}}
@extends('layouts.crm')

@section('title', $student->full_name ?? $student->first_name . ' ' . $student->last_name)

@push('styles')
    @include('crm.components.show.styles')
@endpush

@section('content')

    {{-- NEXT PREVIOUS BUTTON FOR TODAYS TASK --}}

    @if (isset($todayTaskNavigation) &&
            $todayTaskNavigation &&
            ($todayTaskNavigation['prev'] || $todayTaskNavigation['next']))
        <div
            style="background: linear-gradient(135deg, #1a0262 0%, #820b5c 100%); padding: 6px 12px;  display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px; white-space: nowrap;">
                <span
                    style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 20px; font-size: 11px; color: white;">📅
                    Today</span>
                <span
                    style="color: white; font-size: 12px; font-weight: 500;">{{ $todayTaskNavigation['current_position'] }}/{{ $todayTaskNavigation['total'] }}</span>
                <div
                    style="width: 60px; height: 3px; background: rgba(255,255,255,0.2); border-radius: 2px; overflow: hidden;">
                    <div
                        style="width: {{ ($todayTaskNavigation['current_position'] / $todayTaskNavigation['total']) * 100 }}%; height: 100%; background: white;">
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 6px;">
                @if ($todayTaskNavigation['prev'])
                    <a href="{{ route('crm.student.show', $todayTaskNavigation['prev']['id']) }}"
                        style="background: white; color: #1a0262; padding: 3px 10px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 500; display: flex; align-items: center; gap: 4px;">
                        ← {{ $todayTaskNavigation['prev']['first_name'] }}
                    </a>
                @else
                    <span
                        style="background: rgba(255,255,255,0.2); color: rgb(255, 255, 255); padding: 3px 10px; border-radius: 6px; font-size: 11px;">←
                        Prev</span>
                @endif
                @if ($todayTaskNavigation['next'])
                    <a href="{{ route('crm.student.show', $todayTaskNavigation['next']['id']) }}"
                        style="background: white; color: #1a0262; padding: 3px 10px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 500; display: flex; align-items: center; gap: 4px;">
                        {{ $todayTaskNavigation['next']['first_name'] }} →
                    </a>
                @else
                    <span
                        style="background: rgba(255,255,255,0.2); color: rgba(255,255,255); padding: 3px 10px; border-radius: 6px; font-size: 11px;">Next
                        →</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Single student with tasks today --}}
    @if (isset($todayTaskNavigation) &&
            $todayTaskNavigation &&
            !$todayTaskNavigation['prev'] &&
            !$todayTaskNavigation['next'] &&
            $todayTaskNavigation['total'] == 1)
        <div style="background: #e3f2fd; border-radius: 6px; padding: 4px 10px; margin-bottom: 12px;">
            <small style="color: #1976d2; font-size: 11px;">📅 Only student with tasks today</small>
        </div>
    @endif
    {{-- Back bar --}}
    <div class="crm-back-bar">
        <a href="{{ route('crm.dashboard') }}">← Back to Pipeline</a>
        <div class="d-flex align-items-center gap-2">
            @if ($canEdit)
                <a href="{{ route('crm.student.edit', $student) }}" class="btn btn-sm btn-outline-primary">✏️ Edit
                    Student</a>
            @endif
            @if (!$canEdit)
                <span class="read-only-badge">👁 Read-only</span>
            @endif
            @if (auth()->user()->is_admin)
                <span class="admin-badge">👑 Admin</span>
            @endif
        </div>
    </div>
    {{-- Student Header Component --}}
    @include('crm.components.show.student-header')

    {{-- Stage Pipeline Component --}}
    @include('crm.components.show.stage-pipeline')

    {{-- Main body --}}
    <div class="crm-body">
        {{-- LEFT SECTION --}}
        <div>
            {{-- Notes Section --}}
            <div class="crm-section">
                <div class="crm-section-header">
                    <span>📝 Internal Notes</span>
                    @if ($canEdit)
                        <button class="btn btn-sm btn-outline-primary" onclick="openLogNoteModal()">📋 Log
                            Activity</button>
                    @endif
                </div>
                <div class="crm-section-body">
                    <div id="notesContainer">
                        {{-- Pinned Notes --}}
                        @include('crm.components.show.regular-notes', ['pinned' => true])

                        {{-- Regular Notes --}}
                        @include('crm.components.show.regular-notes', ['pinned' => false])
                    </div>

                    @if ($notes->isEmpty())
                        <div class="text-muted text-center py-3" id="noNotesMessage">No notes yet.</div>
                    @endif

                    @if ($canEdit)
                        <div id="noteForm" class="mt-3">
                            <form action="{{ route('crm.notes.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <input type="hidden" name="type" value="internal">
                                <textarea name="content" rows="5" class="form-control mb-2" placeholder="Write a quick note…" required></textarea>
                                <div class="d-flex gap-2 justify-content-between align-items-center">
                                    <div>
                                        <input type="checkbox" name="is_pinned" value="1" id="pin_note">
                                        <label for="pin_note">Pin this note</label>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary">Save Note</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tabs Section --}}
            <div class="crm-section">
                <div class="crm-tabs">
                    <button class="crm-tab active" onclick="switchTab('tasks', this)">Tasks</button>
                    <button class="crm-tab" onclick="switchTab('documents', this)">Documents</button>
                    <button class="crm-tab" onclick="switchTab('history', this)">History</button>
                </div>

                {{-- TASKS TAB --}}
                <div id="tab-tasks">
                    @include('crm.components.tasks.task-list')
                </div>

                {{-- DOCUMENTS TAB --}}
                <div id="tab-documents" style="display:none">
                    <div class="crm-section-body">
                        @forelse($student->documents as $doc)
                            <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                                <span class="fs-5">📄</span>
                                <div class="flex-grow-1">
                                    <div class="fw-medium small">{{ $doc->document_type }}</div>
                                    <div class="text-muted" style="font-size:.72rem">Uploaded
                                        {{ $doc->created_at->format('d M Y') }}</div>
                                </div>
                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                    class="btn btn-sm btn-outline-primary">View</a>
                            </div>
                        @empty
                            <div class="text-muted text-center py-4">No documents uploaded.</div>
                        @endforelse
                    </div>
                </div>

                {{-- HISTORY TAB --}}
                <div id="tab-history" style="display:none">
                    <div class="crm-section-body">
                        <div id="stageHistoryContent">
                            <div class="text-center text-muted py-3 small">Loading history…</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT SIDEBAR --}}
        @include('crm.components.show.student-detail')
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Keyboard navigation for next/previous
            document.addEventListener('keydown', function(e) {
                // Don't trigger if user is typing in an input or textarea
                if (document.activeElement.tagName === 'INPUT' ||
                    document.activeElement.tagName === 'TEXTAREA' ||
                    document.activeElement.isContentEditable) {
                    return;
                }

                if (e.key === 'ArrowLeft') {
                    const prevBtn = document.querySelector('.today-task-navigation a:first-child');
                    if (prevBtn && prevBtn.href && !prevBtn.disabled) {
                        window.location.href = prevBtn.href;
                    }
                } else if (e.key === 'ArrowRight') {
                    const nextBtn = document.querySelector('.today-task-navigation a:last-child');
                    if (nextBtn && nextBtn.href && !nextBtn.disabled) {
                        window.location.href = nextBtn.href;
                    }
                }
            });
        });
    </script>
    {{-- All Modals --}}
    @include('crm.components.show.show-modals')

@endsection

@push('scripts')
    @include('crm.components.show.scripts')
@endpush
