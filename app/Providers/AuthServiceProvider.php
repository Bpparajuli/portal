<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Course;
use App\Models\Document;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Policies\ApplicationPolicy;
use App\Policies\CoursePolicy;
use App\Policies\DocumentPolicy;
use App\Policies\StudentPolicy;
use App\Policies\UniversityPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Application::class => ApplicationPolicy::class,
        Course::class => CoursePolicy::class,
        Document::class => DocumentPolicy::class,
        Student::class => StudentPolicy::class,
        University::class => UniversityPolicy::class,
    ];

    public function boot(): void
    {
        Gate::define('view-students', function (User $user) {
            return $user->is_admin || $user->is_agent || $user->is_staff;
        });

        Gate::define('manage-students', function (User $user) {
            return $user->is_admin || $user->is_agent;
        });

        Gate::define('manage-applications', function (User $user) {
            return $user->is_admin || $user->is_agent || $user->is_admin_staff;
        });

        Gate::define('view-crm', function (User $user) {
            return $user->is_admin || $user->isPaidCrm();
        });

        Gate::define('manage-settings', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('access-admin', function (User $user) {
            return $user->is_admin;
        });
    }
}
