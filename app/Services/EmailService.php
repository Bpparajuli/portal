<?php
namespace App\Services;

use App\Models\Email;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailService
{
    /**
     * Get inbox emails for the authenticated user.
     */
    public function getInbox(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Email::where('receiver_id', Auth::id())
            ->where('type', 'inbox')
            ->latest()
            ->paginate(20);
    }

    /**
     * Get sent emails for the authenticated user.
     */
    public function getSent(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Email::where('sender_id', Auth::id())
            ->where('type', 'sent')
            ->latest()
            ->paginate(20);
    }

    /**
     * Get draft emails for the authenticated user.
     */
    public function getDrafts(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Email::where('sender_id', Auth::id())
            ->where('type', 'draft')
            ->latest()
            ->paginate(20);
    }

    /**
     * Store a new email (sent or draft).
     */
    public function store(Request $request): Email
    {
        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject'     => 'required|string|max:255',
            'body'        => 'required|string',
            'type'        => 'required|in:sent,draft',
        ]);

        $data['sender_id'] = Auth::id();
        return Email::create($data);
    }

    /**
     * Reply to an existing email.
     */
    public function reply(Email $originalEmail, string $body): Email
    {
        return Email::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $originalEmail->sender_id,
            'subject'     => 'Re: ' . $originalEmail->subject,
            'body'        => $body,
            'type'        => 'sent',
        ]);
    }

    /**
     * Save as draft.
     */
    public function saveDraft(Request $request): Email
    {
        $data = $request->validate([
            'receiver_id' => 'nullable|exists:users,id',
            'subject'     => 'nullable|string|max:255',
            'body'        => 'nullable|string',
        ]);

        $data['sender_id'] = Auth::id();
        $data['type'] = 'draft';
        return Email::create($data);
    }
}
