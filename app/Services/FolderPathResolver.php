<?php

namespace App\Services;

use App\Models\User;

class FolderPathResolver
{
    /**
     * Resolve folder path for a user's own files (logo, pan, registration, agreement).
     *
     * Admin   → admin/{slug}/
     * Staff   → staff/{parentSlug}/{slug}/
     * Agent   → agents/{slug}/
     */
    public function resolveUserFolder(User $user): string
    {
        if ($user->role === 'admin') {
            return "admin/{$user->slug}";
        }

        if ($user->role === 'staff') {
            $parentSlug = $user->parent?->slug ?? 'unknown';
            return "staff/{$parentSlug}/{$user->slug}";
        }

        return "agents/{$user->slug}";
    }

    /**
     * Resolve folder path for student files (photo, documents, SOP).
     *
     * - Student has no agent or agent_id = 12 → staff/idea-baneswor/{studentName}/
     * - Student's agent is staff              → staff/{agentParentSlug}/{studentName}/
     * - Student's agent is admin              → admin/{agentSlug}/{studentName}/
     * - Otherwise (regular agent)             → agents/{agentSlug}/{studentName}/
     */
    public function resolveStudentFolder(?User $studentAgent, string $studentName): string
    {
        if (!$studentAgent || $studentAgent->id === 12) {
            return "staff/idea-baneswor/{$studentName}";
        }

        if ($studentAgent->role === 'staff') {
            $parentSlug = $studentAgent->parent?->slug ?? 'unknown';
            return "staff/{$parentSlug}/{$studentName}";
        }

        if ($studentAgent->role === 'admin') {
            return "admin/{$studentAgent->slug}/{$studentName}";
        }

        return "agents/{$studentAgent->slug}/{$studentName}";
    }

    /**
     * File name for a user upload.
     *
     * Staff → {slug}-{type}.{ext}
     * Others → {type}.{ext}
     */
    public function resolveUserFileName(User $user, string $type, string $extension): string
    {
        if ($user->role === 'staff') {
            return "{$user->slug}-{$type}.{$extension}";
        }
        return "{$type}.{$extension}";
    }

    /**
     * File name for a student file.
     * Always {type}.{ext}
     */
    public function resolveStudentFileName(string $type, string $extension): string
    {
        return "{$type}.{$extension}";
    }

    /**
     * Sanitize name for filesystem folder names.
     */
    public function sanitizeName(string $name): string
    {
        return strtolower(\Illuminate\Support\Str::slug($name));
    }
}
