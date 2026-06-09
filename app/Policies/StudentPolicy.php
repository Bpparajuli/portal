<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin || $user->is_staff || $user->is_agent;
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->is_admin || $user->is_admin_staff) return true;
        if ($user->is_agent && (int) $student->agent_id === (int) $user->id) return true;
        if ($user->is_agent_staff) return in_array((int) $student->agent_id, [(int) $user->id, (int) $user->parent_id]);
        return false;
    }

    public function create(User $user): bool
    {
        return $user->is_admin || $user->is_agent || $user->is_admin_staff;
    }

    public function update(User $user, Student $student): bool
    {
        if ($user->is_admin || $user->is_admin_staff) return true;
        if ($user->is_agent && (int) $student->agent_id === (int) $user->id) return true;
        if ($user->is_agent_staff) return in_array((int) $student->agent_id, [(int) $user->id, (int) $user->parent_id]);
        return false;
    }

    public function delete(User $user, Student $student): bool
    {
        if ($user->is_admin || $user->is_admin_staff) return true;
        if ($user->is_agent && (int) $student->agent_id === (int) $user->id) return true;
        return false;
    }
}
