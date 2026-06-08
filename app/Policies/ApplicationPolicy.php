<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->is_admin || $user->is_staff || $user->is_agent;
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->is_admin) return true;
        if ($user->is_agent) return $user->id === $application->agent_id;
        if ($user->is_staff) {
            if ($user->is_admin_staff) return true;
            if ($user->is_agent_staff) return in_array((int) $application->agent_id, [$user->id, $user->parent_id]);
            return $application->agent_id === $user->id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->is_admin || $user->is_agent;
    }

    public function update(User $user, Application $application): bool
    {
        if ($user->is_admin) return true;
        if ($user->is_agent) return $user->id === $application->agent_id;
        return false;
    }

    public function updateStatus(User $user, Application $application): bool
    {
        return $user->is_admin || $user->is_staff;
    }

    public function withdraw(User $user, Application $application): bool
    {
        return $user->is_agent && $user->id === $application->agent_id;
    }

    public function delete(User $user, Application $application): bool
    {
        return $user->is_admin;
    }
}
