<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Use the Auth facade
use App\Models\Document;

class DocumentController extends Controller
{
    /**
     * Display a listing of the documents for agents.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // Check if a user is authenticated
        if (Auth::check()) {
            // Get the authenticated user's ID
            $agentId = Auth::id();

            // Fetch documents related to the authenticated agent
            $documents = Document::where('agent_id', $agentId)->get();
            return view('agent.documents.index', compact('documents'));
        }

        // If not authenticated, redirect to the login page
        return redirect()->route('auth.login');
    }

    // Add other resource methods (create, store, show, edit, update, destroy) as needed.
}
