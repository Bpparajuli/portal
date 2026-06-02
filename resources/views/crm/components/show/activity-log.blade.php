{{-- resources/views/crm/components/show/activity-log.blade.php --}}
<div class="mt-4">
    <div class="task-box-header">📋 Activity Log</div>
    <div id="activityLogContainer">
        @forelse($activityLogs ?? [] as $log)
            <div class="note-item log-entry" data-note-id="{{ $log->id }}">
                <div class="d-flex justify-content-between">
                    <div class="log-icon">📝 {{ $log->title ?? 'Activity Log' }}
                    </div>
                    @if ($canEdit)
                        <div class="note-actions">
                            <button class="edit-note-btn"
                                onclick="openEditNoteModal({{ $log->id }}, '{{ addslashes($log->content) }}', false)">
                                ✏️
                            </button>
                            <form action="{{ route('crm.notes.destroy', $log) }}" method="POST"
                                onsubmit="return confirm('Delete this log entry?')" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="note-pin-btn" title="Delete log">🗑️</button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="log-content">
                    <div class="log-title">
                        <div class="log-description">
                            <pre class="log-pre">{{ $log->content }}</pre>
                        </div>
                        <div class="log-meta">
                            By {{ $log->creator?->name ?? 'Unknown' }} •
                            {{ $log->created_at->format('d M Y, g:i A') }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-muted text-center py-3">No activity logs yet. Click "Log Activity"
                to add one.
            </div>
        @endforelse
    </div>
</div>
