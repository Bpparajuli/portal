<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function __construct(
        private readonly FolderPathResolver $folderPathResolver,
    ) {}

    /**
     * Upload file for agent (user)
     *
     * Admin   → admin/{slug}/{type}.{ext}
     * Staff   → staff/{parentSlug}/{slug}/{slug}-{type}.{ext}
     * Agent   → agents/{slug}/{type}.{ext}
     */
    public function uploadAgentFile(Request $request, $user, string $inputName, string $type)
    {
        if (!$request->hasFile($inputName)) {
            return $user->$inputName;
        }

        $file = $request->file($inputName);
        $this->validateFile($file, $type);

        $folder = $this->folderPathResolver->resolveUserFolder($user);
        $fileName = $this->folderPathResolver->resolveUserFileName($user, $type, $file->getClientOriginalExtension());

        if ($user->$inputName && Storage::disk('public')->exists($user->$inputName)) {
            Storage::disk('public')->delete($user->$inputName);
        }

        return $file->storeAs($folder, $fileName, 'public');
    }

    /**
     * Upload student photo
     *
     * Student folder resolved by FolderPathResolver:
     * - No agent / agent_id=12 → staff/idea-baneswor/{studentName}/
     * - Agent is staff          → staff/{parentSlug}/{studentName}/
     * - Agent is admin          → admin/{agentSlug}/{studentName}/
     * - Regular agent           → agents/{agentSlug}/{studentName}/
     */
    public function uploadStudentFile($file, $agent, $student, string $type, ?string $existingPath = null)
    {
        if (!$file) {
            return $existingPath;
        }

        $this->validateFile($file, $type);

        $studentName = $this->folderPathResolver->sanitizeName($student->first_name . ' ' . $student->last_name);
        $folder = $this->folderPathResolver->resolveStudentFolder($agent, $studentName);
        $fileName = $this->folderPathResolver->resolveStudentFileName($type, $file->getClientOriginalExtension());

        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        return $file->storeAs($folder, $fileName, 'public');
    }

    /**
     * Upload student Documents
     */
    public function uploadStudentDocument($file, $agent, $student, string $documentType, ?string $existingPath = null)
    {
        if (!$file) {
            return $existingPath;
        }

        $this->validateFile($file, $documentType, ['pdf', 'jpg', 'jpeg', 'png']);

        $studentName = $this->folderPathResolver->sanitizeName($student->first_name . ' ' . $student->last_name);
        $folder = $this->folderPathResolver->resolveStudentFolder($agent, $studentName);

        $cleanType = strtolower(str_replace(' ', '_', $documentType));
        $fileName = "{$cleanType}." . $file->getClientOriginalExtension();

        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        return $file->storeAs($folder, $fileName, 'public');
    }

    /**
     * Upload SOP For each Application
     */
    public function uploadStudentSOP($file, $agent, $student, ?string $existingPath = null)
    {
        if (!$file) {
            return $existingPath;
        }

        $this->validateFile($file, 'sop', ['pdf', 'doc', 'docx']);

        $studentName = $this->folderPathResolver->sanitizeName($student->first_name . ' ' . $student->last_name);
        $folder = $this->folderPathResolver->resolveStudentFolder($agent, $studentName);

        $fileName = "sop." . $file->getClientOriginalExtension();

        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        return $file->storeAs($folder, $fileName, 'public');
    }

    /**
     * Upload revenue receipt
     *
     * Stores in student's folder (same as photos/documents) as receipt_{timestamp}.{ext}
     * Path resolved by FolderPathResolver based on student's agent.
     */
    public function uploadRevenueReceipt($file, $agent, $student, ?string $existingPath = null): string
    {
        $this->validateFile($file, 'receipt', ['jpg', 'jpeg', 'png', 'pdf']);

        $studentName = $this->folderPathResolver->sanitizeName($student->first_name . ' ' . $student->last_name);
        $folder = $this->folderPathResolver->resolveStudentFolder($agent, $studentName);

        $timestamp = date('Y-m-d_H-i-s');
        $fileName = "receipt_{$timestamp}." . $file->getClientOriginalExtension();

        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        return $file->storeAs($folder, $fileName, 'public');
    }

    /**
     * Upload application message attachment
     *
     * Stores at: application_messages/{applicationId}/{timestamp}_{originalName}
     */
    public function uploadApplicationAttachment($file, $application): string
    {
        $this->validateFile($file, 'attachment', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

        $fileName = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs('application_messages/' . $application->id, $fileName, 'public');
    }

    /**
     * Upload chat attachment
     *
     * Stores at: chat_files/{auto-hashed-filename}
     */
    public function uploadChatAttachment($file): string
    {
        $this->validateFile($file, 'chat', ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'zip']);

        return $file->store('chat_files', 'public');
    }

    /**
     * Delete all files for a student
     */
    public function deleteStudentFiles($agent, $student)
    {
        $studentName = $this->folderPathResolver->sanitizeName($student->first_name . ' ' . $student->last_name);
        $folder = $this->folderPathResolver->resolveStudentFolder($agent, $studentName);

        if (Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->deleteDirectory($folder);
        }
    }

    /**
     * Get file URL
     */
    public function getFileUrl($path)
    {
        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file, string $type, array $allowedExtensions = null)
    {
        $allowed = $allowedExtensions ?? $this->getDefaultExtensions($type);
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowed)) {
            throw new \InvalidArgumentException("Invalid file type for {$type}. Allowed: " . implode(', ', $allowed));
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException("File size exceeds 5MB limit");
        }
    }

    private function getDefaultExtensions(string $type)
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
