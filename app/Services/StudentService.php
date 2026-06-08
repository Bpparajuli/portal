<?php
// app/Services/StudentService.php

namespace App\Services;

use App\Contracts\FileUploadServiceInterface;
use App\Contracts\StudentServiceInterface;
use App\Models\Student;
use App\Models\StudentStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StudentService implements StudentServiceInterface
{
    public function __construct(
        private readonly FileUploadServiceInterface $fileUploadService,
    ) {}

    /**
     * Check for duplicate student
     * Returns existing student if found, null otherwise
     */
    public function findDuplicate(Request $request, ?Student $student = null): ?Student
    {
        // For updates, exclude the current student
        $query = Student::query();

        if ($student) {
            $query->where('id', '!=', $student->id);
        }

        // Check by email (if provided)
        if ($request->filled('email')) {
            $existing = $query->where('email', $request->email)->first();
            if ($existing) {
                return $existing;
            }
        }

        // Check by phone number (if provided)
        if ($request->filled('phone_number')) {
            // Clean phone number for comparison (remove spaces, dashes, etc.)
            $cleanPhone = $this->cleanPhoneNumber($request->phone_number);

            $existing = Student::where('id', '!=', $student?->id)
                ->where(function ($q) use ($cleanPhone, $request) {
                    $q->where('phone_number', $request->phone_number)
                        ->orWhere('phone_number', $cleanPhone);
                })->first();

            if ($existing) {
                return $existing;
            }
        }

        // Check by first_name + last_name + phone (for walk-ins without email)
        if ($request->filled('first_name') && $request->filled('last_name') && $request->filled('phone_number')) {
            $existing = Student::where('id', '!=', $student?->id)
                ->where('first_name', $request->first_name)
                ->where('last_name', $request->last_name)
                ->where(function ($q) use ($request) {
                    $q->where('phone_number', $request->phone_number)
                        ->orWhere('phone_number', $this->cleanPhoneNumber($request->phone_number));
                })->first();

            if ($existing) {
                return $existing;
            }
        }

        return null;
    }

    /**
     * Clean phone number for comparison
     */
    private function cleanPhoneNumber($phone): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Remove country code if present (assume 10 digits is standard)
        if (strlen($cleaned) > 10) {
            $cleaned = substr($cleaned, -10);
        }

        return $cleaned;
    }

    /**
     * Get duplicate message with details
     */
    public function getDuplicateMessage(Student $existingStudent): string
    {
        $details = [];

        if ($existingStudent->email) {
            $details[] = "Email: {$existingStudent->email}";
        }
        if ($existingStudent->phone_number) {
            $details[] = "Phone: {$existingStudent->phone_number}";
        }

        $detailText = !empty($details) ? " (" . implode(', ', $details) . ")" : "";

        return "Duplicate student found! Student '{$existingStudent->full_name}' already exists{$detailText}. Last added on: {$existingStudent->created_at->format('Y-m-d H:i')}";
    }

    /**
     * Create or update student with consistent folder structure
     */
    public function saveStudent(Request $request, ?Student $student = null): Student
    {
        $isNew = !$student;

        // Check for duplicates BEFORE creating new student
        if ($isNew) {
            $duplicate = $this->findDuplicate($request);
            if ($duplicate) {
                throw new \Exception($this->getDuplicateMessage($duplicate));
            }
        }

        // Prepare student data
        $data = $this->prepareStudentData($request, $student);

        if ($isNew) {
            $student = Student::create($data);
        } else {
            $student->update($data);
        }

        // Handle photo upload using your FileUploadService
        if ($request->hasFile('students_photo')) {
            $agent = $this->getAgentForStudent($request, $student);

            if ($agent) {
                try {
                    $photoPath = $this->fileUploadService->uploadStudentFile(
                        file: $request->file('students_photo'),
                        agent: $agent,
                        student: $student,
                        type: 'photo',
                        existingPath: $student->students_photo
                    );

                    $student->students_photo = $photoPath;
                    $student->saveQuietly();
                } catch (\Exception $e) {
                    Log::error('Failed to upload student photo: ' . $e->getMessage());
                }
            }
        }

        // Ensure folder structure exists
        $this->ensureStudentFolderExists($student);

        return $student;
    }

    /**
     * Prepare student data from request
     */
    private function prepareStudentData(Request $request, ?Student $student = null): array
    {
        $data = [];

        // Capture source value (can be ANY text: person name, channel, etc.)
        $source = $request->input('source');

        // Determine intake method for default logic
        $intakeMethod = null;
        if ($request->has('_intake_method')) {
            $intakeMethod = $request->input('_intake_method');
        } elseif ($source === 'api_intake' || $source === 'quick_add') {
            $intakeMethod = $source;
        }

        // Define all possible fields
        $fieldMappings = [
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'email' => 'email',
            'phone_number' => 'phone_number',
            'gender' => 'gender',
            'dob' => 'dob',
            'marital_status' => 'marital_status',
            'permanent_address' => 'permanent_address',
            'temporary_address' => 'temporary_address',
            'nationality' => 'nationality',
            'passport_number' => 'passport_number',
            'passport_expiry' => 'passport_expiry',
            'applying_for' => 'applying_for',
            'qualification' => 'qualification',
            'passed_year' => 'passed_year',
            'gap' => 'gap',
            'last_grades' => 'last_grades',
            'education_board' => 'education_board',
            'preferred_country' => 'preferred_country',
            'preferred_city' => 'preferred_city',
            'preferred_course' => 'preferred_course',
            'preferred_university' => 'preferred_university',
            'remarks' => 'remarks',
            'rating' => 'rating',
            'expected_revenue' => 'expected_revenue',
            'current_stage_id' => 'current_stage_id',
            'created_by' => 'created_by',
        ];

        // Map all regular fields
        foreach ($fieldMappings as $requestField => $dbField) {
            if ($request->has($requestField)) {
                $data[$dbField] = $request->input($requestField);
            }
        }

        // Clean phone number before saving (optional - store both raw and cleaned)
        if ($request->has('phone_number') && !empty($request->phone_number)) {
            $data['phone_number'] = $this->cleanPhoneNumber($request->phone_number);
        }

        // 🔥 SOURCE HANDLING - Smart defaults based on intake method
        if ($request->has('source') && !empty($request->input('source'))) {
            // User provided a custom source (person name, channel, etc.)
            $data['source'] = $request->input('source');
        } elseif (!$student) {
            // New student with no source provided - use intake method as default
            if ($intakeMethod === 'api_intake') {
                $data['source'] = 'api_intake';
            } elseif ($intakeMethod === 'quick_add') {
                $data['source'] = 'quick_add';
            } else {
                $data['source'] = 'manual';
            }
        }
        // For updates, if source not provided, keep existing (don't overwrite)

        // Handle tags
        if ($request->has('tags')) {
            $tags = $request->input('tags');
            if (is_string($tags)) {
                $data['tags'] = json_decode($tags, true) ?: [];
            } elseif (is_array($tags)) {
                $data['tags'] = $tags;
            }
        }

        // Handle agent assignment for NEW students only
        if (!$student) {
            // Priority 1: Explicit agent_id in request
            if ($request->has('agent_id') && !empty($request->input('agent_id'))) {
                $data['agent_id'] = $request->input('agent_id');
            }
            // Priority 2: For API intake/quick add - force agent 12
            elseif ($intakeMethod === 'api_intake' || $intakeMethod === 'quick_add') {
                $data['agent_id'] = $this->getDefaultAgentId();
            }
            // Priority 3: Logged-in user if they are an agent or staff
            elseif (Auth::check() && (Auth::user()->is_agent || Auth::user()->is_agent_staff || Auth::user()->is_staff)) {
                $data['agent_id'] = Auth::id();
            }
            // Priority 4: Default agent
            else {
                $data['agent_id'] = $this->getDefaultAgentId();
            }
        }

        // Handle stage for new students (if not already set)
        if (!$student && empty($data['current_stage_id'])) {
            $initialStage = StudentStage::where('stage_order', 1)->first();
            if ($initialStage) {
                $data['current_stage_id'] = $initialStage->id;
            }
        }

        // Always set created_by for new students if not set
        if (!$student && empty($data['created_by']) && Auth::check()) {
            $data['created_by'] = Auth::id();
        }

        return $data;
    }

    /**
     * Get agent for student (from request or student record)
     */
    private function getAgentForStudent(Request $request, Student $student): ?User
    {
        // Try to get from request first (for updates)
        if ($request->has('agent_id') && $request->input('agent_id')) {
            return User::find($request->input('agent_id'));
        }

        // Otherwise use student's existing agent
        if ($student->agent_id) {
            return $student->agent;
        }

        // Fallback to default agent
        return User::find($this->getDefaultAgentId());
    }

    /**
     * Get default agent ID
     */
    private function getDefaultAgentId(): int
    {
        $agent = User::where('role', 'agent')->first();
        return $agent?->id ?? 12;
    }

    /**
     * Ensure student folder structure exists using your FileUploadService structure
     */
    public function ensureStudentFolderExists(Student $student): void
    {
        $agent = $student->agent;

        if (!$agent) {
            Log::warning('Cannot create folder for student without agent', ['student_id' => $student->id]);
            return;
        }

        // Use the same sanitization as FileUploadService
        $studentName = $this->sanitizeName($student->first_name . ' ' . $student->last_name);
        $folderPath = "agents/{$agent->slug}/{$studentName}";

        // Create folder if it doesn't exist
        if (!Storage::disk('public')->exists($folderPath)) {
            Storage::disk('public')->makeDirectory($folderPath);
            Log::info('Created student folder', ['path' => $folderPath, 'student_id' => $student->id]);
        }
    }

    /**
     * Sanitize name for folder usage (matching FileUploadService)
     */
    private function sanitizeName($name): string
    {
        return strtolower(\Illuminate\Support\Str::slug($name));
    }

    /**
     * Delete student and all associated files using your FileUploadService
     */
    public function deleteStudent(Student $student): void
    {
        // Delete all files using your FileUploadService
        if ($student->agent) {
            try {
                $this->fileUploadService->deleteStudentFiles($student->agent, $student);
            } catch (\Exception $e) {
                Log::error('Failed to delete student files: ' . $e->getMessage());
            }
        }

        // Delete the student record
        $student->delete();
    }
}
