<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserRegistered;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // VALIDATION
        $request->validate([
            'business_name' => 'nullable|string|max:255',
            'owner_name'    => 'nullable|string|max:255',
            'name'          => 'required|string|max:255',
            'contact'       => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => 'required|string|min:6|confirmed',
            'business_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10280',
            // any file type
            'agreement_file' => 'nullable|file',
        ]);

        // MAKE SAFE FOLDER NAME
        $safeBusinessName = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($request->business_name ?? 'agent'));
        // LOGO UPLOAD
        $logoPath = null;

        if ($request->hasFile('business_logo')) {
            $logoPath = $request->file('business_logo')
                ->storeAs(
                    'agents/' . $safeBusinessName,
                    $safeBusinessName . '_logo.' . $request->file('business_logo')->getClientOriginalExtension(),
                    'public'
                );
        }

        // AGREEMENT FILE UPLOAD (same folder)
        $agreementFilePath = null;
        if ($request->hasFile('agreement_file')) {

            $agreementFileName = $safeBusinessName . '_agreement.' .
                $request->file('agreement_file')->getClientOriginalExtension();

            $agreementFilePath = $request->file('agreement_file')
                ->storeAs(
                    'agents/' . $safeBusinessName,
                    $agreementFileName,
                    'public'
                );
        }

        // CREATE USER
        $user = User::create([
            'business_name'    => $request->business_name,
            'owner_name'       => $request->owner_name,
            'name'             => $request->name,
            'contact'          => $request->contact,
            'address'          => $request->address,
            'email'            => $request->email,
            'password'         => Hash::make($request->password),
            'business_logo'    => $logoPath,
            // agreement
            'agreement_file'   => $agreementFilePath,
            'agreement_status' => $agreementFilePath ? 'uploaded' : 'not_uploaded',

            'is_admin' => 0,
            'is_agent' => 1,
            'active'   => 1,
        ]);
        auth()->login($user);


        // SEND NOTIFICATION TO ADMIN (id=2)
        $admin = User::find(2);
        if ($admin) {
            Notification::send($admin, new UserRegistered($user));
        }

        return redirect()->route('auth.waiting-dash')
            ->with('success', 'Registered successfully. Please wait for admin approval.');
    }
}
