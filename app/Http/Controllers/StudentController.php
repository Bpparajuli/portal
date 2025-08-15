<?php

namespace App\Http\Controllers;

use App\Models\User;

class StudentController extends Controller
{
    public function list()
    {
        // Get all agents
        $agents = User::where('is_agent', 1)->get();

        // Pass agents to the view
        return view('student.list', compact('agents'));
    }
}
