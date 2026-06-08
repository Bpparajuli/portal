<?php

namespace App\Actions;

use App\Models\Activity;
use App\Models\User;

class LogActivityAction
{
    public function execute(
        string $type,
        string $description,
        ?User $user = null,
        ?int $notifiableId = null,
        ?string $link = null,
    ): Activity {
        return Activity::create([
            'user_id' => $user?->id,
            'type' => $type,
            'description' => $description,
            'notifiable_id' => $notifiableId,
            'link' => $link,
        ]);
    }
}
