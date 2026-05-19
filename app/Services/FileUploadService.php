<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        // Validate file
        self::validateFile($file, $type);

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
    public static function uploadStudentFile($file, $agent, $student, string $type, ?string $existingPath = null)
    {
        if (!$file) {
            return $existingPath;
        }

        self::validateFile($file, $type);

        // ✅ Agent slug (already exists)
        $agentSlug = $agent->slug;

        // ✅ Student folder name (clean)
        $studentName = self::sanitizeName($student->first_name . ' ' . $student->last_name);

        // ✅ Final folder
        $folder = "agents/{$agentSlug}/{$studentName}";

        // ✅ File name
        $fileName = "{$type}." . $file->getClientOriginalExtension();

        // ✅ Delete old file (if exists)
        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        return $file->storeAs($folder, $fileName, 'public');
    }

    /**
     * Upload student Documents
     */
    public static function uploadStudentDocument($file, $agent, $student, string $documentType, ?string $existingPath = null)
    {
        if (!$file) {
            return $existingPath;
        }

        self::validateFile($file, $documentType, ['pdf', 'jpg', 'jpeg', 'png']);

        // ✅ Agent slug
        $agentSlug = $agent->slug;

        // ✅ Student folder
        $studentName = self::sanitizeName($student->first_name . ' ' . $student->last_name);

        $folder = "agents/{$agentSlug}/{$studentName}";

        // ✅ Clean document name
        $cleanType = strtolower(str_replace(' ', '_', $documentType));

        $fileName = "{$cleanType}." . $file->getClientOriginalExtension();

        // ✅ Delete old file if exists
        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        return $file->storeAs($folder, $fileName, 'public');
    }

    /**
     * Upload SOP For each Application
     */
    public static function uploadStudentSOP($file, $agent, $student, ?string $existingPath = null)
    {
        if (!$file) {
            return $existingPath;
        }

        self::validateFile($file, 'sop', ['pdf', 'doc', 'docx']);

        $agentSlug = $agent->slug;

        $studentName = self::sanitizeName($student->first_name . ' ' . $student->last_name);

        $folder = "agents/{$agentSlug}/{$studentName}";

        $fileName = "sop." . $file->getClientOriginalExtension();

        // ✅ Delete old file if exists
        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        return $file->storeAs($folder, $fileName, 'public');
    }

    /**
     * Delete all files for a student
     */
    public static function deleteStudentFiles($agent, $student)
    {
        $studentName = self::sanitizeName($student->first_name . ' ' . $student->last_name);
        $folder = "agents/{$agent->slug}/{$studentName}";

        if (Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->deleteDirectory($folder);
        }
    }

    /**
     * Get file URL
     */
    public static function getFileUrl($path)
    {
        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Sanitize name for folder usage
     */
    private static function sanitizeName($name)
    {
        return strtolower(Str::slug($name));
    }

    /**
     * Validate uploaded file
     */
    private static function validateFile($file, string $type, array $allowedExtensions = null)
    {
        $allowed = $allowedExtensions ?? self::getDefaultExtensions($type);
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowed)) {
            throw new \InvalidArgumentException("Invalid file type for {$type}. Allowed: " . implode(', ', $allowed));
        }

        // Max file size: 5MB
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException("File size exceeds 5MB limit");
        }
    }

    /**
     * Get default extensions based on file type
     */
    private static function getDefaultExtensions(string $type)
    {
        $imageTypes = ['logo', 'photo', 'signature'];
        $documentTypes = ['agreement', 'pan', 'ielts', 'toefl', 'transcript'];
        $sopTypes = ['sop'];

        if (in_array($type, $imageTypes)) {
            return ['jpg', 'jpeg', 'png', 'webp'];
        } elseif (in_array($type, $documentTypes)) {
            return ['pdf', 'jpg', 'jpeg', 'png'];
        } elseif (in_array($type, $sopTypes)) {
            return ['pdf', 'doc', 'docx'];
        }

        return ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    }
}
