<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Notifications\NewUserRegistered;
use Illuminate\Support\Facades\Notification;

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

        $logoName = null;

        if ($request->hasFile('business_logo')) {
            $extension = $request->file('business_logo')->getClientOriginalExtension();
            $safeBusinessName = str_replace(' ', '_', strtolower($request->business_name ?? 'agent'));
            $logoName = $safeBusinessName . '.' . $extension;

            // Save logo to public/images/Agents_logo
            $request->file('business_logo')->move(public_path('images/Agents_logo'), $logoName);
        }

        $user = User::create([
            'business_name' => $request->business_name,
            'owner_name' => $request->owner_name,
            'name' => $request->name,
            'contact' => $request->contact,
            'address' => $request->address,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'business_logo' => $logoName,
            'is_admin' => $request->input('is_admin', 0),
            'is_agent' => $request->input('is_agent', 1),
            'active' => $request->input('active', 0),
        ]);

        $admins = User::where('is_admin', 1)->get();
        Notification::send($admins, new NewUserRegistered($user));
        // Optionally log in the user after registration
        // Auth::login($user); // Uncomment if you want to log in the user immediately
        return redirect()->route('login')->with('success', 'Registered successfully. Please wait for admins approvel to log in.');
    }
}
