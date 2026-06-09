<?php

namespace App\Observers;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use Illuminate\Support\Facades\Auth;

class ApplicationObserver
{
    public function updated(Application $application): void
    {
        if (!$application->isDirty('application_status_id')) {
            return;
        }

        $originalStatusId = $application->getOriginal('application_status_id');
        $newStatusId = $application->application_status_id;

        if ($originalStatusId === $newStatusId) {
            return;
        }

        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status_id' => $originalStatusId ?: null,
            'to_status_id' => $newStatusId,
            'changed_by' => Auth::id() ?? $application->agent_id,
        ]);
    }

    public function creating(Application $application): void
    {
        if ($application->application_status_id && !$application->application_number) {
            return;
        }
    }

    public function created(Application $application): void
    {
        if (!$application->application_status_id) {
            return;
        }

        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status_id' => null,
            'to_status_id' => $application->application_status_id,
            'changed_by' => Auth::id() ?? $application->agent_id,
        ]);
    }
}
