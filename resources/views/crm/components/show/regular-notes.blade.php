{{-- resources/views/crm/components/show/regular-notes.blade.php --}}
@php
    $notesToDisplay = $pinned ? $notes->where('is_pinned', true) : $notes->where('is_pinned', false);
@endphp

@foreach ($notesToDisplay as $note)
    <div class="note-item {{ $pinned ? 'pinned' : '' }}" data-note-id="{{ $note->id }}">
        <div class="d-flex justify-content-between align-items-start">
            <div class="note-display">{{ $note->content }}</div>
            @if ($canEdit)
                <div class="note-actions">
                    <button class="edit-note-btn"
                        onclick="openEditNoteModal({{ $note->id }}, '{{ addslashes($note->content) }}', {{ $note->is_pinned ? 'true' : 'false' }})">
                        ✏️
                    </button>
                    <button class="note-pin-btn"
                        onclick="togglePin({{ $note->id }})">{{ $pinned ? '📌' : '📍' }}</button>
                    <form action="{{ route('crm.notes.destroy', $note) }}" method="POST"
                        onsubmit="return confirm('Delete note?')" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="note-pin-btn">🗑️</button>
                    </form>
                </div>
            @endif
        </div>
        <div class="text-muted mt-1" style="font-size:.72rem">
            By {{ $note->creator?->name ?? 'Unknown' }} &bull;
            {{ $note->created_at->format('d M Y, g:i A') }}
        </div>
    </div>
@endforeach
