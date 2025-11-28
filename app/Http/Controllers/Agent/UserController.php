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

class UserController extends Controller
{
    /**
     * Convert slug to the real business_name
     */
    private function businessNameFromSlug($slug)
    {
        // Replace dashes with spaces
        $name = str_replace('-', ' ', $slug);

        // Laravel fallback: match case-insensitive
        return User::whereRaw('LOWER(business_name) = ?', [strtolower($name)])
            ->with(['documents', 'applications'])
            ->firstOrFail();
    }

    /**
     * Show user profile
     */
    public function show($slug)
    {
        $auth = Auth::user();

        // Convert slug → actual user model
        $user = $this->businessNameFromSlug($slug);

        // Authorization (agents can ONLY view their own profile)
        if ($auth->is_agent && $auth->id != $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $agentId = $auth->id;

        // Recent activities (same as your original logic)
        $studentActivities = Activity::where('user_id', $agentId)
            ->whereIn('type', ['student_added', 'student_deleted'])
            ->latest()->take(5)->get();

        $documentActivities = Activity::where('user_id', $agentId)
            ->whereIn('type', ['document_uploaded', 'document_deleted'])
            ->latest()->take(5)->get();

        $applicationActivities = Activity::where('user_id', $agentId)
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
     * Edit user profile page
     */
    public function edit($slug)
    {
        $user = $this->businessNameFromSlug($slug);

        return view('agent.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, $slug)
    {
        $user = $this->businessNameFromSlug($slug);

        $request->validate([
            'email' => 'required|email',
            'password' => 'nullable|min:6|confirmed',
            'business_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'agreement_file' => 'nullable|file',
        ]);

        $user->email = $request->email;
        $user->contact = $request->contact;
        $user->address = $request->address;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $safeBusinessName = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($request->business_name ?? 'agent'));

        // Logo
        if ($request->hasFile('business_logo')) {
            $logoPath = $request->file('business_logo')->storeAs(
                'agents/' . $safeBusinessName,
                $safeBusinessName . '_logo.' . $request->file('business_logo')->getClientOriginalExtension(),
                'public'
            );
            $user->business_logo = $logoPath;
        }

        // Agreement File
        $agreementFileChanged = false;

        if ($request->hasFile('agreement_file')) {

            $agreementFileName = $safeBusinessName . '_agreement.' .
                $request->file('agreement_file')->getClientOriginalExtension();

            $newFilePath = $request->file('agreement_file')->storeAs(
                'agents/' . $safeBusinessName,
                $agreementFileName,
                'public'
            );

            if ($user->agreement_file !== $newFilePath) {
                $agreementFileChanged = true;
                $user->agreement_file = $newFilePath;
            }
        }

        $user->save();

        // Notify admin2 only if agreement updated
        if ($agreementFileChanged) {
            $admin = User::find(2);
            if ($admin) {
                $admin->notify(new AgreementSubmitted($user));
            }
        }

        return redirect()->route('agent.users.show', $user->business_name_slug)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Reset password
     */
    public function resetPassword($slug)
    {
        $user = $this->businessNameFromSlug($slug);
        $newPassword = Str::random(10);

        $user->password = Hash::make($newPassword);
        $user->save();

        return back()->with('success', "Password reset successfully. New password: $newPassword");
    }
}
