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

class RegisterController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        // ✅ Validation
        $request->validate(
            [
                'business_name'  => 'required|string|max:255',
                'owner_name'     => 'nullable|string|max:255',
                'name'           => 'required|string|max:255|unique:users,name',
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
            $user->is_agent = 1;
            $user->is_admin = 0;
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
            $user->business_logo  = FileUploadService::uploadAgentFile($request, $user, 'business_logo', 'logo');
            $user->registration   = FileUploadService::uploadAgentFile($request, $user, 'registration', 'registration');
            $user->pan            = FileUploadService::uploadAgentFile($request, $user, 'pan', 'pan');

            if ($request->hasFile('agreement_file')) {
                $user->agreement_file   = FileUploadService::uploadAgentFile($request, $user, 'agreement_file', 'agreement');
                $user->agreement_status = 'uploaded';
            } else {
                $user->agreement_status = 'not_uploaded';
            }

            // 🔥 Save again after file upload
            $user->save();

            // ✅ Notify admin (temporary id=2)
            $admin = User::find(2);
            if ($admin) {
                Notification::send($admin, new UserRegistered($user));
            }

            // ✅ Auto-login
            Auth::login($user);

            return redirect()
                ->route('auth.waiting-dash')
                ->with('success', 'Registered successfully. Please wait for admin approval.');
        } catch (QueryException $e) {
            // 🔥 Failsafe (never expose SQL errors)
            return back()
                ->withErrors(['name' => 'This user name is already in use. Please choose another.'])
                ->withInput();
        }
    }
}
