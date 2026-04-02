<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AgreementSubmitted;
use App\Services\FileUploadService;

class WaitingController extends Controller
{
    /**
     * Show waiting dashboard
     */
    public function show()
    {
        $user = Auth::user();
        return view('auth.waiting-dash', compact('user'));
    }

    /**
     * Upload agreement file
     */
    public function upload(Request $request)
    {
        $user = Auth::user();

        // Only agents can upload
        if (!$user->is_agent) {
            abort(403, 'Unauthorized.');
        }

        // ✅ Validation
        $request->validate([
            'agreement_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
        ]);

        // ✅ Use FileUploadService for consistency
        $storedPath = FileUploadService::uploadAgentFile($request, $user, 'agreement_file', 'agreement');

        // ✅ Update user record
        $user->agreement_file = $storedPath;
        $user->agreement_status = 'uploaded';
        $user->save();

        // ✅ Notify admin (id=2)
        $admin = User::find(2);
        if ($admin) {
            Notification::send($admin, new AgreementSubmitted($user));
        }

        return redirect()->route('auth.waiting-dash')
            ->with('success', 'Agreement uploaded successfully. Waiting for admin verification.');
    }
}
