<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use App\Notifications\UserRegistered;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     * This ensures only guests can access registration
     */
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {
        // Only guests can access registration
        $this->middleware('guest')->except('logout');

        // Alternative: You can also use middleware in routes file
        // But adding here ensures protection even if routes are misconfigured
    }

    /**
     * Show registration form
     * Redirects to dashboard if user is already logged in
     */
    public function showRegistrationForm()
    {
        // Check if user is already logged in
        if (Auth::check()) {
            $user = Auth::user();

            // Redirect based on user role and status
            if ($user->is_admin) {
                return redirect()->route('admin.dashboard')->with('info', 'You are already logged in as admin.');
            } elseif ($user->is_agent) {
                // Check agreement status
                if ($user->agreement_status === 'verified') {
                    return redirect()->route('agent.dashboard')->with('info', 'You are already registered and verified.');
                } else {
                    return redirect()->route('auth.waiting-dash')->with('info', 'Your registration is pending verification.');
                }
            }

            // Default fallback
            return redirect('/')->with('info', 'You are already logged in.');
        }

        // Show registration form for guests only
        return view('guest.auth.register');
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        // Additional check to prevent logged-in users from registering again
        if (Auth::check()) {
            return redirect()->route('home')->with('error', 'You are already logged in.');
        }

        // ✅ Validation
        $request->validate(
            [
                'business_name'  => 'required|string|max:255',
                'owner_name'     => 'nullable|string|max:255',
                'name' => 'required|string|max:255|unique:users,name',
                'contact'        => 'required|string|max:20',
                'address'        => 'nullable|string|max:255',
                'email'          => 'required|string|email|max:255|unique:users,email',
                'password'       => 'required|string|min:6|confirmed',

                'business_logo'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
                'registration'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480',
                'pan'            => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480',
                'agreement_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',

                'terms'          => 'accepted',
            ],
            [
                'name.unique'    => 'This user name already exists. Please choose another.',
                'email.unique'   => 'This email is already registered.',
                'terms.accepted' => 'You must agree to the Terms & Conditions.',
            ]
        );

        try {
            // ✅ Create user
            $user = new User();
            $user->business_name = $request->business_name;
            $user->owner_name    = $request->owner_name;
            $user->name          = $request->name;
            $user->contact       = $request->contact;
            $user->address       = $request->address;
            $user->email         = $request->email;
            $user->password      = Hash::make($request->password);

            // Default roles/status
            $user->role     = 'agent';
            $user->active   = 1;

            // ✅ Generate unique slug
            $baseSlug = Str::slug($request->business_name);
            $slug = $baseSlug;
            $count = 1;

            while (User::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $count++;
            }

            $user->slug = $slug;

            // 🔥 Save user first (needed for file upload)
            $user->save();

            // ✅ File uploads
            $user->business_logo  = $this->fileUploadService->uploadAgentFile($request, $user, 'business_logo', 'logo');
            $user->registration   = $this->fileUploadService->uploadAgentFile($request, $user, 'registration', 'registration');
            $user->pan            = $this->fileUploadService->uploadAgentFile($request, $user, 'pan', 'pan');

            if ($request->hasFile('agreement_file')) {
                $user->agreement_file   = $this->fileUploadService->uploadAgentFile($request, $user, 'agreement_file', 'agreement');
                $user->agreement_status = 'uploaded';
            } else {
                $user->agreement_status = 'not_uploaded';
            }

            // 🔥 Save again after file upload
            $user->save();

            // ✅ Notify admin (find admin users instead of hardcoded id=2)
            $admins = User::admins()->get();
            if ($admins->count() > 0) {
                foreach ($admins as $admin) {
                    Notification::send($admin, new UserRegistered($user));
                }
            }

            // ✅ Auto-login
            Auth::login($user);

            return redirect()
                ->route('auth.waiting-dash')
                ->with('success', 'Registered successfully! Please upload your agreement to complete registration.');
        } catch (QueryException $e) {
            // 🔥 Failsafe (never expose SQL errors)
            Log::error('Registration error: ' . $e->getMessage());

            return back()
                ->withErrors(['error' => 'Registration failed. Please try again or contact support.'])
                ->withInput();
        }
    }
}
