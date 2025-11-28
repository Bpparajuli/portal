<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AgreementSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AgreementUploaded; // optional: notify admin

class WaitingController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('auth.waiting-dash', compact('user'));
    }

    public function upload(Request $request)
    {
        $user = Auth::user();

        // Only agents should use this (extra safety; your isAgent middleware will also protect routes)
        if (!$user->is_agent) {
            abort(403, 'Unauthorized.');
        }

        // Validate file (PDF only, max 5MB)
        $request->validate([
            'agreement_file' => 'required|file|max:10240', // 10mb
        ]);

        // Build safe folder name (match your RegisterController logic)
        $safeBusinessName = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($user->business_name ?? ($user->username ?? 'agent')));

        // Prepare file name
        $file = $request->file('agreement_file');
        $fileName = $safeBusinessName . '_agreement.' . $file->getClientOriginalExtension();

        // Path in 'public' disk: agents/{safeBusinessName}/{fileName}
        $path = 'agents/' . $safeBusinessName . '/' . $fileName;

        // Delete previous file if exists (optional)
        if ($user->agreement_file && Storage::disk('public')->exists($user->agreement_file)) {
            Storage::disk('public')->delete($user->agreement_file);
        }

        // Store file
        $storedPath = $file->storeAs('agents/' . $safeBusinessName, $fileName, 'public');

        // Update user record
        $user->agreement_file = $storedPath;
        $user->agreement_status = 'uploaded'; // admin will change to 'verified' later
        $user->save();

        // Notify admin2 only if agreement updated
        $admin = User::find(2);
        if ($admin) {
            $admin->notify(new AgreementSubmitted($user));
        }
        return redirect()->route('auth.waiting-dash')->with('success', 'Agreement uploaded successfully. Waiting for admin verification.');
    }
}
