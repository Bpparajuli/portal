<?php
namespace App\Services;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnquiryService
{
    /**
     * Create an enquiry from the public contact form.
     *
     * Automatically logs an activity entry.
     */
    public function create(Request $request): Enquiry
    {
        $enquiry = Enquiry::create($request->validated());

        Activity::create([
            'user_id'       => Auth::check() ? Auth::id() : null,
            'type'          => 'enquiry_created',
            'description'   => "Enquiry created by {$enquiry->name} ({$enquiry->email})",
            'notifiable_id' => $enquiry->id,
        ]);

        return $enquiry;
    }

    /**
     * Send a reply to an enquiry (updates the replied_at timestamp).
     */
    public function reply(Enquiry $enquiry, string $replyMessage): Enquiry
    {
        $enquiry->update([
            'reply'      => $replyMessage,
            'replied_at' => now(),
            'replied_by' => Auth::id(),
        ]);
        return $enquiry;
    }

    /**
     * Get paginated enquiries.
     */
    public function getPaginated(int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Enquiry::latest()->paginate($perPage);
    }
}
