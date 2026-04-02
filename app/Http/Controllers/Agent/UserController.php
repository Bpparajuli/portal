<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AgreementSubmitted;
use App\Services\FileUploadService;

class UserController extends Controller
{
    /**
     * Get user by slug
     */
    private function getUserBySlug($slug)
    {
        return User::where('slug', $slug)->firstOrFail();
    }

    /**
     * Show profile
     */
    public function show($slug)
    {
        $auth = Auth::user();
        $user = $this->getUserBySlug($slug);

        if ($auth->id != $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $studentActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['student_added', 'student_deleted'])
            ->latest()->take(5)->get();

        $documentActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['document_uploaded', 'document_deleted'])
            ->latest()->take(5)->get();

        $applicationActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['application_submitted', 'application_withdrawn'])
            ->latest()->take(5)->get();

        return view('agent.users.show', compact(
            'user',
            'studentActivities',
            'documentActivities',
            'applicationActivities'
        ));
    }

    /**
     * Edit profile
     */
    public function edit($slug)
    {
        $user = $this->getUserBySlug($slug);

        if (Auth::id() != $user->id) {
            abort(403, 'Unauthorized.');
        }

        return view('agent.users.edit', compact('user'));
    }

    /**
     * Update profile
     */
    public function update(Request $request, $slug)
    {
        $user = $this->getUserBySlug($slug);

        if (Auth::id() != $user->id) {
            abort(403, 'Unauthorized.');
        }

        // ✅ Validation
        $request->validate([
            'email'          => 'required|email|max:255|unique:users,email,' . $user->id,
            'contact'        => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'password'       => 'nullable|string|min:6|confirmed',

            'business_logo'  => 'nullable|file|mimes:jpg,jpeg,png|max:20480',
            'registration'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
            'pan'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
            'agreement_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        // ✅ Basic fields
        $user->email   = $request->email;
        $user->contact = $request->contact;
        $user->address = $request->address;

        // ✅ Password update
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        /**
         * ⚠️ VERY IMPORTANT
         * Make sure slug exists (FileUploadService depends on it)
         */
        if (!$user->slug) {
            $user->slug = strtolower(str_replace(' ', '-', $user->business_name));
        }

        // ✅ Save before file upload (ensures clean state)
        $user->save();

        // ===============================
        // FILE UPLOADS (SERVICE BASED)
        // ===============================
        $user->business_logo = FileUploadService::uploadAgentFile($request, $user, 'business_logo', 'logo');
        $user->registration  = FileUploadService::uploadAgentFile($request, $user, 'registration', 'registration');
        $user->pan           = FileUploadService::uploadAgentFile($request, $user, 'pan', 'pan');

        // Agreement handling
        $agreementChanged = false;

        if ($request->hasFile('agreement_file')) {
            $user->agreement_file   = FileUploadService::uploadAgentFile($request, $user, 'agreement_file', 'agreement');
            $user->agreement_status = 'uploaded';
            $agreementChanged = true;
        }

        $user->save();

        // ===============================
        // NOTIFICATION
        // ===============================
        if ($agreementChanged) {
            $admin = User::find(1); // safer than 2
            if ($admin) {
                Notification::send($admin, new AgreementSubmitted($user));
            }
        }

        return redirect()->route('agent.users.show', $user->slug)
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Reset password
     */
    public function resetPassword($slug)
    {
        $user = $this->getUserBySlug($slug);

        if (Auth::id() != $user->id) {
            abort(403, 'Unauthorized.');
        }

        $newPassword = Str::random(10);

        $user->password = Hash::make($newPassword);
        $user->save();

        return back()->with('success', "Password reset successfully. New password: $newPassword");
    }
}
