<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin || $user->is_staff || $user->is_agent;
    }

    public function view(User $user, Document $document): bool
    {
        if ($user->is_admin || $user->is_admin_staff) return true;
        $student = $document->student;
        if ($user->is_agent && (int) $student->agent_id === (int) $user->id) return true;
        if ($user->is_agent_staff) return in_array((int) $student->agent_id, [(int) $user->id, (int) $user->parent_id]);
        return false;
    }

    public function create(User $user): bool
    {
        return $user->is_admin || $user->is_admin_staff || $user->is_agent;
    }

    public function update(User $user, Document $document): bool
    {
        return $this->delete($user, $document);
    }

    public function delete(User $user, Document $document): bool
    {
        if ($user->is_admin || $user->is_admin_staff) return true;
        $student = $document->student;
        if ($user->is_agent && (int) $student->agent_id === (int) $user->id) return true;
        if ($user->is_agent_staff) return in_array((int) $student->agent_id, [(int) $user->id, (int) $user->parent_id]);
        return false;
    }

    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }
}
