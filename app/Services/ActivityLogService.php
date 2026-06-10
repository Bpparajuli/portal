<?php
namespace App\Services;

use App\Models\Activity;

class ActivityLogService
{
    /**
     * Log an activity entry.
     *
     * @param  int|null    $userId       The user who performed the action.
     * @param  string      $type         Activity type identifier.
     * @param  string      $description  Human-readable description.
     * @param  int|null    $notifiableId Related model ID.
     * @param  string|null $link         Optional URL to the resource.
     */
    public function log(?int $userId, string $type, string $description, ?int $notifiableId = null, ?string $link = null): Activity
    {
        return Activity::create([
            'user_id'       => $userId,
            'type'          => $type,
            'description'   => $description,
            'notifiable_id' => $notifiableId,
            'link'          => $link,
        ]);
    }

    /**
     * Get paginated activity logs.
     */
    public function getPaginated(int $perPage = 50): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Activity::with('user')->latest()->paginate($perPage);
    }

    /**
     * Delete all activity logs.
     */
    public function clearAll(): void
    {
        Activity::truncate();
    }
}
