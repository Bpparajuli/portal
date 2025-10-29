<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewUserRegistered;
use Illuminate\Support\Facades\Storage;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'business_name' => 'nullable|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'business_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $logoPath = null;
        $safeBusinessName = str_replace(' ', '_', strtolower($request->business_name ?? 'agent'));

        if ($request->hasFile('business_logo')) {
            // Store in storage/app/public/agents/{business_name}/
            $logoPath = $request->file('business_logo')
                ->storeAs(
                    'agents/' . $safeBusinessName,       // folder inside storage/app/public
                    $safeBusinessName . '_logo.png',    // file name
                    'public'                            // disk = public
                );
        }

        $user = User::create([
            'business_name' => $request->business_name,
            'owner_name' => $request->owner_name,
            'name' => $request->name,
            'contact' => $request->contact,
            'address' => $request->address,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'business_logo' => $logoPath, // stored as storage path like "agents/acme/acme_logo.png"
            'is_admin' => $request->input('is_admin', 0),
            'is_agent' => $request->input('is_agent', 1),
            'active' => $request->input('active', 0),
        ]);


        // Notify all admins
        $admins = User::where('is_admin', 1)->get();
        Notification::send($admins, new NewUserRegistered($user));

        return redirect()->route('auth.login')->with('success', 'Registered successfully. Please wait for admin approval.');
    }
}
