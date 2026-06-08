<?php
// app/Http/Controllers/StudentIntakeController.php

namespace App\Http\Controllers;

use App\Contracts\StudentServiceInterface;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentIntakeController extends Controller
{
    public function __construct(
        private readonly StudentServiceInterface $studentService,
    ) {}
    /**
     * Main intake endpoint for API/form submissions
     */
    public function intake(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'preferred_country' => 'nullable|string|max:100',
            'preferred_course' => 'nullable|string|max:255',
            'applying_for' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'agent_id' => 'nullable|exists:users,id',
            'return_format' => 'nullable|in:json,redirect',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->input('return_format') === 'json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Split full_name into first_name and last_name
        $nameParts = explode(' ', $data['full_name'], 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        // Create request with proper format for StudentService
        $serviceRequest = new Request([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'],
            'preferred_country' => $data['preferred_country'] ?? null,
            'preferred_course' => $data['preferred_course'] ?? null,
            'applying_for' => $data['applying_for'] ?? null,
            'agent_id' => $data['agent_id'] ?? null,
            'source' => $data['source'] ?? 'api_intake',
            '_intake_method' => 'api_intake',
        ]);

        try {
            // Check for duplicate before creating
            $duplicate = $this->studentService->findDuplicate($serviceRequest);
            if ($duplicate) {
                $duplicateMessage = $this->studentService->getDuplicateMessage($duplicate);

                if ($request->expectsJson() || ($data['return_format'] ?? '') === 'json') {
                    return response()->json([
                        'success' => false,
                        'message' => $duplicateMessage,
                        'duplicate_student' => [
                            'id' => $duplicate->id,
                            'name' => $duplicate->full_name,
                            'phone' => $duplicate->phone_number,
                            'email' => $duplicate->email,
                            'created_at' => $duplicate->created_at->format('Y-m-d H:i:s')
                        ]
                    ], 409); // 409 Conflict
                }

                return redirect()->back()
                    ->with('error', $duplicateMessage)
                    ->with('duplicate_student', $duplicate)
                    ->withInput();
            }

            // Use StudentService to create student
            $student = $this->studentService->saveStudent($serviceRequest);

            // Store in session for thank you page
            session(['last_student' => $student]);

            $successMessage = sprintf(
                "Student added successfully! Name: %s, Phone: %s",
                $student->full_name,
                $student->phone_number
            );

            Log::info('New student added via intake API', [
                'student_id' => $student->id,
                'name' => $student->full_name,
                'source' => $student->source,
                'folder_created' => true
            ]);

            if ($request->expectsJson() || ($data['return_format'] ?? '') === 'json') {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->full_name,
                        'phone' => $student->phone_number,
                        'email' => $student->email,
                        'source' => $student->source,
                    ]
                ], 201); // 201 Created
            }

            return redirect()->route('thank-you')
                ->with('success', $successMessage)
                ->with('student_created', $student);
        } catch (\Exception $e) {
            Log::error('Failed to add student: ' . $e->getMessage());

            $errorMessage = 'Failed to add student: ' . $e->getMessage();

            if ($request->expectsJson() || ($data['return_format'] ?? '') === 'json') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    /**
     * Show public form for QR code and website embedding
     */
    public function showForm()
    {
        return view('intake.universal-form');
    }

    /**
     * Simple GET endpoint for quick testing
     */
    public function quickAdd(Request $request)
    {
        // Validate required fields
        if (!$request->name || !$request->phone) {
            return "❌ Error: Please provide name and phone<br><br>
                    <a href='?name=John+Doe&phone=1234567890'>Click here to test: ?name=John+Doe&phone=1234567890</a>";
        }

        // Split name into first and last
        $nameParts = explode(' ', $request->name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        // Create request for StudentService
        $serviceRequest = new Request([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone_number' => $request->phone,
            'email' => $request->email,
            'preferred_country' => $request->preferred_country,
            'preferred_course' => $request->preferred_course,
            'applying_for' => $request->applying_for,
            'source' => $request->source ?? 'quick_add',
            'agent_id' => 12,
            '_intake_method' => 'quick_add',
        ]);

        try {
            // Check for duplicate
            $duplicate = $this->studentService->findDuplicate($serviceRequest);
            if ($duplicate) {
                return sprintf(
                    "⚠️ DUPLICATE STUDENT DETECTED!\n\nStudent: %s\nPhone: %s\nEmail: %s\nCreated: %s\n\nPlease check existing record before adding again.",
                    $duplicate->full_name,
                    $duplicate->phone_number ?? 'N/A',
                    $duplicate->email ?? 'N/A',
                    $duplicate->created_at->format('Y-m-d H:i:s')
                );
            }

            // Use StudentService for creation
            $student = $this->studentService->saveStudent($serviceRequest);

            return sprintf(
                "✅ STUDENT ADDED SUCCESSFULLY!\n\nStudent ID: %d\nName: %s\nPhone: %s\nEmail: %s\nApplying For: %s\nCountry: %s\nSource: %s",
                $student->id,
                $student->full_name,
                $student->phone_number,
                $student->email ?? 'Not provided',
                $student->applying_for ?? 'Not specified',
                $student->preferred_country ?? 'Not specified',
                $student->source
            );
        } catch (\Exception $e) {
            Log::error('Quick add failed: ' . $e->getMessage());
            return "❌ Error: " . $e->getMessage();
        }
    }
}
