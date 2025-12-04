<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserRegistered;
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
            'business_name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'business_logo' => 'nullable|file|max:20480',
            'registration' => 'required|file|max:20480',
            'pan' => 'required|file|max:20480',
            'agreement_file' => 'nullable|file|max:20480',
        ]);

        $user = new User();
        $user->business_name  = $request->business_name;
        $user->owner_name     = $request->owner_name;
        $user->name           = $request->name;
        $user->contact        = $request->contact;
        $user->address        = $request->address;
        $user->email          = $request->email;
        $user->password       = Hash::make($request->password);

        // IMPORTANT DEFAULTS
        $user->role        = 'agent';
        $user->is_agent    = 1;
        $user->is_admin    = 0;
        $user->active      = 1;

        // Slug only for URLs
        $user->slug        = strtolower(str_replace(' ', '-', $request->business_name));

        $user->save();

        // UPLOAD FILES
        $user->business_logo  = $this->uploadFile($request, $user, 'business_logo', 'logo');
        $user->registration   = $this->uploadFile($request, $user, 'registration', 'registration');
        $user->pan            = $this->uploadFile($request, $user, 'pan', 'pan');

        if ($request->hasFile('agreement_file')) {
            $user->agreement_file   = $this->uploadFile($request, $user, 'agreement_file', 'agreement');
            $user->agreement_status = 'uploaded';
        } else {
            $user->agreement_status = 'not_uploaded';
        }

        $user->save();

        // Notify main admin (id=2)
        $admin = User::find(2);
        if ($admin) Notification::send($admin, new UserRegistered($user));

        Auth::login($user);

        return redirect()->route('auth.waiting-dash')
            ->with('success', 'Registered successfully. Please wait for admin approval.');
    }

    private function uploadFile(Request $request, User $user, string $inputName, string $suffix)
    {
        if (!$request->hasFile($inputName)) return $user->$inputName;

        $safeName = str_replace([' ', '.', '-'], '_', strtolower($user->business_name));
        $folder   = "agents/$safeName";

        $file = $request->file($inputName);
        $fileName = $safeName . '_' . $suffix . '.' . $file->getClientOriginalExtension();

        // DELETE OLD FILE IF EXISTS
        if ($user->$inputName && Storage::disk('public')->exists($user->$inputName)) {
            Storage::disk('public')->delete($user->$inputName);
        }

        return $file->storeAs($folder, $fileName, 'public');
    }
}
