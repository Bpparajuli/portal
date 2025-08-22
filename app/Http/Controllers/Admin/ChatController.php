<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        // For main chat (application_id is null)
        $messages = Message::whereNull('application_id')->get();
        return view('admin.chat.main', compact('messages'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate(['message' => 'required|string']);

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => null, // Or a specific admin/agent ID
            'message' => $request->message,
            'application_id' => null, // Main chat
        ]);

        return back();
    }

    public function applicationChatIndex(Application $application)
    {
        // For application-specific chat
        $messages = $application->messages;
        return view('admin.chat.application', compact('application', 'messages'));
    }

    public function sendApplicationMessage(Request $request, Application $application)
    {
        $request->validate(['message' => 'required|string']);

        $application->messages()->create([
            'sender_id' => Auth::id(),
            'message' => $request->message,
        ]);

        return back();
    }
}
