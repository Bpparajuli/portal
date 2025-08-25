<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chat; // Correctly import the Chat model

class ChatController extends Controller
{
    /**
     * Display the chat dashboard for admins.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Example: Fetch recent chat messages or a list of users to chat with
        // Note: orderBy() is used here. In a real application with a lot of data,
        // you might want to use pagination for better performance.
        $chats = Chat::orderBy('created_at', 'desc')->get();
        return view('admin.chat', compact('chats'));
    }
}
