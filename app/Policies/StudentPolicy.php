<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function view(User $user, Student $student): bool
    {
        return $user->is_admin || $user->is_staff || $user->id === $student->agent_id;
    }

    public function create(User $user): bool
    {
        return $user->is_admin || $user->is_agent;
    }

    public function update(User $user, Student $student): bool
    {
        return $user->is_admin || $user->id === $student->agent_id;
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->is_admin;
    }
}
