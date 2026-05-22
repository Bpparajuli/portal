<?php
// app/Http/Controllers/StudentIntakeController.php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class StudentIntakeController extends Controller
{
    /**
     * MAIN UNIVERSAL API ENDPOINT
     * Handles ALL incoming students from ANYWHERE (WhatsApp, Facebook, Web, API)
     */
    public function intake(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'country' => 'nullable|string|max:100',
            'course' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:50',
            'agent_id' => 'nullable|exists:users,id',
            'return_format' => 'nullable|in:json,redirect',  // ← Added this
        ]);

        if ($validator->fails()) {
            // Check if request expects JSON
            if ($request->expectsJson() || $request->input('return_format') === 'json') {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // For web form, redirect back with errors
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Split full_name into first_name and last_name
        $nameParts = explode(' ', $data['full_name'], 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        // Get default agent if not specified
        $agentId = $data['agent_id'] ?? $this->getDefaultAgent();

        // Get starting stage
        $initialStageId = StudentStage::where('stage_order', 1)->first()?->id;

        try {
            // Create student - matching your exact schema
            $student = Student::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $data['email'] ?? null,
                'phone_number' => $data['phone_number'],
                'preferred_country' => $data['country'] ?? null,
                'preferred_course' => $data['course'] ?? null,
                'qualification' => $data['qualification'] ?? null,
                'agent_id' => $agentId,
                'current_stage_id' => $initialStageId,
                'source' => $data['source'] ?? 'api_intake',
            ]);

            // Store student in session for thank you page
            session(['last_student' => $student]);

            // Log the submission
            Log::info('New student added via intake API', [
                'student_id' => $student->id,
                'name' => $student->full_name,
                'source' => $data['source'] ?? 'unknown',
                'ip' => $request->ip()
            ]);

            // Check if response should be JSON
            if ($request->expectsJson() || ($data['return_format'] ?? '') === 'json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Student added successfully!',
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->full_name,
                        'phone' => $student->phone_number,
                        'crm_url' => route('admin.students.show', $student)
                    ]
                ]);
            }

            // For web form submissions - Redirect to thank you page
            return redirect()->route('thank-you')->with('success', 'Student added successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to add student: ' . $e->getMessage());

            // Check if response should be JSON
            if ($request->expectsJson() || ($data['return_format'] ?? '') === 'json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add student: ' . $e->getMessage()
                ], 500);
            }

            // For web form, redirect back with error
            return redirect()->back()->with('error', 'Failed to add student: ' . $e->getMessage());
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
        if (!$request->name || !$request->phone) {
            return "❌ Error: Please provide name and phone<br><br>
                    <a href='?name=John+Doe&phone=1234567890'>Click here to test: ?name=John+Doe&phone=1234567890</a>";
        }

        // Split name into first and last
        $nameParts = explode(' ', $request->name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        try {
            $student = Student::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone_number' => $request->phone,
                'email' => $request->email,
                'preferred_country' => $request->country,
                'source' => $request->source ?? 'quick_add',
                'agent_id' => 1,
                'current_stage_id' => 1,
            ]);

            return "✅ Student Added Successfully!<br><br>
                    Student ID: {$student->id}<br>
                    Name: {$student->full_name}<br>
                    Phone: {$student->phone_number}<br>
                    Email: {$student->email}<br>
                    Country: {$student->preferred_country}";
        } catch (\Exception $e) {
            return "❌ Error: " . $e->getMessage();
        }
    }

    private function getDefaultAgent()
    {
        $agent = User::where('role', 'agent')->first();
        return $agent?->id ?? 1;
    }
}
