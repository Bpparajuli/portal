<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface FileUploadServiceInterface
{
    public function uploadAgentFile(Request $request, $user, string $inputName, string $type);
    public function uploadStudentFile($file, $agent, $student, string $type, ?string $existingPath = null);
    public function uploadStudentDocument($file, $agent, $student, string $documentType, ?string $existingPath = null);
    public function uploadStudentSOP($file, $agent, $student, ?string $existingPath = null);
    public function deleteStudentFiles($agent, $student);
    public function getFileUrl($path);
}
