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
            return $this->redirectToDashboard(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'active' => 1
        ])) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials or account not active.'],
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectToDashboard(Auth::user());
    }

    /**
     * Centralized redirect logic
     */
    protected function redirectToDashboard($user)
    {
        if ($user->is_admin) {
            return redirect()->intended('/admin/dashboard');
        }

        if ($user->is_staff) {
            return redirect()->intended('/staff/dashboard');
        }

        if ($user->is_agent) {
            return redirect()->intended('/agent/dashboard');
        }

        // fallback
        return redirect('/');
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
