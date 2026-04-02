<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;

class StudentController extends Controller
{
    public function show($id)
    {
        $student = (object)[
            'id' => $id,
            'name' => 'Arjun Patel',
            'uid' => 'STU-2026-0492',
            'email' => 'arjun.patel@gmail.com',
            'phone' => '+91 98765 43210',
            'country' => 'United Kingdom',
            'visa_type' => 'Student Visa (Subclass 500)',
            'assigned_to' => 'Sarah Smith',
            'source' => 'Facebook Lead',
            'created_at' => '12 Jan 2026',
            'current_step' => 'Documentation',
            'steps' => ['Lead', 'Contacted', 'Documentation', 'Submission', 'Decision', 'Completed'],
            'stats' => [
                ['label' => 'Total Tasks', 'value' => '12', 'color' => 'text-primary'],
                ['label' => 'Docs Pending', 'value' => '3', 'color' => 'text-warning'],
                ['label' => 'Days Active', 'value' => '42', 'color' => 'text-success'],
            ],
            'activities' => [
                ['user' => 'Sarah Smith', 'msg' => 'moved lead to Documentation', 'time' => '2h ago', 'type' => 'status'],
                ['user' => 'System', 'msg' => 'Email sent: Document Request List', 'time' => '5h ago', 'type' => 'email'],
                ['user' => 'Arjun Patel', 'msg' => 'uploaded Passport_Scan.pdf', 'time' => 'Yesterday', 'type' => 'upload'],
                ['user' => 'Admin', 'msg' => 'updated Visa Category', 'time' => '3 days ago', 'type' => 'edit'],
            ]
        ];

        return view('staff.student', compact('student'));
    }
}
