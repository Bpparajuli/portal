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
                    <button class="edit-note-btn" data-id="{{ $note->id }}" data-content="{{ $note->content }}" data-pinned="{{ $note->is_pinned ? '1' : '0' }}"
                        onclick="openEditNoteModal(this.dataset.id, this.dataset.content, this.dataset.pinned === '1')">
                        ✏️
                    </button>
                    <button class="note-pin-btn"
                        onclick="togglePin({{ $note->id }})">{{ $pinned ? '📌' : '📍' }}</button>
                    <form action="{{ route('crm.notes.convert', $note) }}" method="POST" class="d-inline" onsubmit="return confirm('Move to activity log?')">
                        @csrf @method('PATCH')
                        <input type="hidden" name="type" value="log">
                        <button type="submit" class="note-pin-btn" title="Move to Activity Log">📋</button>
                    </form>
                    <x-confirm-delete
                        url="{{ route('crm.notes.destroy', $note) }}"
                        label=""
                        title="Delete note?"
                        message="Delete note?"
                        class="note-pin-btn"
                    />
                </div>
            @endif
        </div>
        <div class="text-muted mt-1" style="font-size:.72rem">
            By {{ $note->creator?->name ?? 'Unknown' }} &bull;
            {{ $note->created_at->format('d M Y, g:i A') }}
        </div>
    </div>
@endforeach
