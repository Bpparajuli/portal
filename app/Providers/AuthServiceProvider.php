<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('view-students', function (User $user) {
            return $user->is_admin || $user->is_agent || $user->is_staff;
        });

        Gate::define('manage-students', function (User $user) {
            return $user->is_admin || $user->is_agent;
        });

        Gate::define('manage-applications', function (User $user) {
            return $user->is_admin || $user->is_agent;
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
