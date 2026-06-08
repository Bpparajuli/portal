<?php

namespace App\Policies;

use App\Models\University;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UniversityPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, University $university): bool { return true; }
    public function create(User $user): bool { return $user->is_admin || $user->is_staff; }
    public function update(User $user, University $university): bool { return $user->is_admin || $user->is_staff; }
    public function delete(User $user, University $university): bool { return $user->is_admin; }
}
