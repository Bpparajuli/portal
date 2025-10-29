<?php

namespace App\Helpers;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log an activity.
     *
     * @param string $type        Activity type (e.g., student_added, document_uploaded, application_submitted)
     * @param string $description Human-readable description
     * @param int|null $notifiableId Related model ID (student, document, application)
     * @param string|null $link     Optional URL to the resource
     * @param int|null $userId      Optional user ID (defaults to logged-in user)
     */
    public static function log(
        string $type,
        string $description,
        ?int $notifiableId = null,
        ?string $link = null,
        ?int $userId = null
    ) {
        Activity::create([
            'user_id' => $userId ?? Auth::id(),
            'type' => $type,
            'description' => $description,
            'notifiable_id' => $notifiableId,
            'link' => $link,
        ]);
    }
}
