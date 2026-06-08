<?php

namespace App\Models\Traits;

use App\Models\Student;
use App\Models\User;

trait HasRoles
{
    public function getIsAdminAttribute($value): bool
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    public function getIsAgentAttribute($value): bool
    {
        return $this->role === 'agent';
    }

    public function getIsStaffAttribute(): bool
    {
        return $this->role === 'staff';
    }

    public function getIsStudentAttribute(): bool
    {
        return $this->role === 'student';
    }

    public function getIsUniversityAttribute(): bool
    {
        return $this->role === 'university';
    }

    public function getIsSuperAdminAttribute(): bool
    {
        return $this->role === 'superadmin';
    }

    public function getIsAdminStaffAttribute(): bool
    {
        if (!$this->is_staff) {
            return false;
        }
        if (!$this->parent_id) {
            return true;
        }
        return $this->parent?->is_admin ?? false;
    }

    public function getIsAgentStaffAttribute(): bool
    {
        if (!$this->is_staff || !$this->parent_id) {
            return false;
        }
        return $this->parent?->is_agent ?? false;
    }

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['superadmin', 'admin']);
    }

    public function scopeAgents($query)
    {
        return $query->where('role', 'agent');
    }

    public function scopeStaff($query)
    {
        return $query->where('role', 'staff');
    }

    public function getAccessibleStudents()
    {
        if ($this->is_admin) {
            return Student::all();
        }

        if ($this->is_staff && $this->is_admin_staff) {
            return Student::all();
        }

        if ($this->is_agent) {
            $staffIds = User::where('parent_id', $this->id)->where('role', 'staff')->pluck('id')->toArray();
            $allowedAgentIds = array_merge([$this->id], $staffIds);
            return Student::whereIn('agent_id', $allowedAgentIds)->get();
        }

        if ($this->is_staff && $this->is_agent_staff) {
            return Student::whereIn('agent_id', [$this->id, $this->parent_id])->get();
        }

        return collect();
    }
}
