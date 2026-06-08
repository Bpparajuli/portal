<?php

namespace App\Listeners;

use App\Actions\LogActivityAction;
use App\Events\ApplicationSubmitted;
use Illuminate\Support\Facades\Auth;

class LogApplicationActivity
{
    public function handle(ApplicationSubmitted $event): void
    {
        app(LogActivityAction::class)->execute(
            'application_submitted',
            "Application {$event->application->application_number} was submitted",
            Auth::user(),
            $event->application->id,
            route('admin.students.show', $event->application->student)
        );
    }
}
