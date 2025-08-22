<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Student;
use App\Models\StudentApplication;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Student $student)
    {
        $chats = $student->chats()->with('application')->get();
        return view('chats.index', compact('student', 'chats'));
    }

    public function store(Request $request, Student $student)
    {
        $request->validate([
            'message' => 'required'
        ]);

        $student->chats()->create([
            'application_id' => $request->application_id,
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        return back()->with('success', 'Message sent.');
    }
}
