<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
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
