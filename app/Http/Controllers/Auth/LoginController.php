<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return redirect()->intended($user->is_admin ? '/admin/dashboard' : '/agent/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting (optional, add throttle middleware or manually)
        // attempt login
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password, 'active' => 1])) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials or account not active.'],
            ]);
        }

        // Regenerate session
        $request->session()->regenerate();

        $user = Auth::user();
        return redirect()->intended($user->is_admin ? '/admin/dashboard' : '/agent/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
