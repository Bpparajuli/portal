<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AgreementSubmitted;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Convert slug to the real User model
     */
    private function getUserBySlug($slug)
    {
        $name = str_replace('-', ' ', $slug);

        return User::whereRaw('LOWER(business_name) = ?', [strtolower($name)])
            ->firstOrFail();
    }

    /**
     * Show user profile
     */
    public function show($slug)
    {
        $auth = Auth::user();
        $user = $this->getUserBySlug($slug);

        if ($auth->is_agent && $auth->id != $user->id) {
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
     * Edit profile page
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
     * Update user
     */
    public function update(Request $request, $slug)
    {
        $user = $this->getUserBySlug($slug);

        if (Auth::id() != $user->id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'email' => 'required|email',
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'business_logo' => 'nullable|file|max:20480',
            'registration' => 'nullable|file|max:20480',
            'pan' => 'nullable|file|max:20480',
            'agreement_file' => 'nullable|file|max:20480',
        ]);

        // Update basic fields
        $user->email = $request->email;
        $user->contact = $request->contact;
        $user->address = $request->address;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // File uploads
        $user->business_logo = $this->uploadFile($request, $user, 'business_logo', 'logo');
        $user->registration  = $this->uploadFile($request, $user, 'registration', 'registration');
        $user->pan           = $this->uploadFile($request, $user, 'pan', 'pan');

        $agreementChanged = false;

        if ($request->hasFile('agreement_file')) {
            $user->agreement_file = $this->uploadFile($request, $user, 'agreement_file', 'agreement');
            $user->agreement_status = 'uploaded';
            $agreementChanged = true;
        }

        $user->save();

        // Notify admin if agreement updated
        if ($agreementChanged) {
            $admin = User::find(2);
            if ($admin) {
                $admin->notify(new AgreementSubmitted($user));
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

    /**
     * Upload files helper
     */
    private function uploadFile(Request $request, User $user, string $inputName, string $suffix)
    {
        if (!$request->hasFile($inputName)) return $user->$inputName;

        $safeName = str_replace([' ', '.', '-'], '_', strtolower($user->business_name));
        $folder   = "agents/$safeName";

        $file = $request->file($inputName);
        $fileName = $safeName . '_' . $suffix . '.' . $file->getClientOriginalExtension();

        // Delete old file if exists
        if ($user->$inputName && Storage::disk('public')->exists($user->$inputName)) {
            Storage::disk('public')->delete($user->$inputName);
        }

        return $file->storeAs($folder, $fileName, 'public');
    }
}
