<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    /**
     * Upload file for agent (user)
     */
    public static function uploadAgentFile(Request $request, $user, string $inputName, string $type)
    {
        // If no file uploaded → keep old value
        if (!$request->hasFile($inputName)) {
            return $user->$inputName;
        }

        $file = $request->file($inputName);

        // ✅ Use slug (VERY IMPORTANT)
        $folder = "agents/{$user->slug}";

        // Example: logo.png, pan.pdf
        $fileName = "{$type}." . $file->getClientOriginalExtension();

        // ✅ Delete old file if exists
        if ($user->$inputName && Storage::disk('public')->exists($user->$inputName)) {
            Storage::disk('public')->delete($user->$inputName);
        }

        // ✅ Store file
        return $file->storeAs($folder, $fileName, 'public');
    }
    /**
     * Upload student photo
     */
    public static function uploadStudentFile($file, $agent, $student, string $type)
    {
        if (!$file) {
            return null;
        }

        // ✅ Agent slug (already exists)
        $agentSlug = $agent->slug;

        // ✅ Student folder name (clean)
        $studentName = strtolower(str_replace(' ', '-', $student->first_name . '-' . $student->last_name));

        // ✅ Final folder
        $folder = "agents/{$agentSlug}/{$studentName}";

        // ✅ File name
        $fileName = "{$type}." . $file->getClientOriginalExtension();

        // ✅ Delete old file (if exists)
        if ($student->$type && Storage::disk('public')->exists($student->$type)) {
            Storage::disk('public')->delete($student->$type);
        }

        return $file->storeAs($folder, $fileName, 'public');
    }
    /**
     * Upload student Documents
     */
    public static function uploadStudentDocument($file, $agent, $student, string $documentType)
    {
        if (!$file) {
            return null;
        }

        // ✅ Agent slug
        $agentSlug = $agent->slug;

        // ✅ Student folder
        $studentName = strtolower(str_replace(' ', '-', $student->first_name . '-' . $student->last_name));

        $folder = "agents/{$agentSlug}/{$studentName}";

        // ✅ Clean document name
        $cleanType = strtolower(str_replace(' ', '_', $documentType));

        $fileName = "{$cleanType}." . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $fileName, 'public');
    }
    /**
     * Upload SOP For each Application
     */
    public static function uploadStudentSOP($file, $agent, $student)
    {
        if (!$file) {
            return null;
        }

        $agentSlug = $agent->slug;

        $studentName = strtolower(str_replace(' ', '-', $student->first_name . '-' . $student->last_name));

        $folder = "agents/{$agentSlug}/{$studentName}";

        $fileName = "sop." . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $fileName, 'public');
    }
}
