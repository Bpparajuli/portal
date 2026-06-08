<?php

namespace App\Actions;

use App\Models\Student;
use App\Models\Application;
use App\Models\User;
use App\Notifications\StudentAdded;
use App\Notifications\StudentDeleted;
use App\Notifications\ApplicationSubmitted;
use Illuminate\Support\Facades\Notification;

class NotifyUserAction
{
    public function notifyStudentAdded(Student $student, User $agent): void
    {
        $admins = User::whereIn('role', ['superadmin', 'admin'])->get();
        Notification::send($admins, new StudentAdded($agent, $student));
    }

    public function notifyStudentDeleted(Student $student): void
    {
        $admins = User::whereIn('role', ['superadmin', 'admin'])->get();
        Notification::send($admins, new StudentDeleted($student));
    }

    public function notifyApplicationSubmitted(Application $application): void
    {
        $admins = User::whereIn('role', ['superadmin', 'admin'])->get();
        Notification::send($admins, new ApplicationSubmitted($application));
    }

    public function notifyUser(User $user, $notification): void
    {
        $user->notify($notification);
    }

    public function notifyAdmins($notification): void
    {
        $admins = User::whereIn('role', ['superadmin', 'admin'])->get();
        Notification::send($admins, $notification);
    }
}
