<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Illuminate\Session\TokenMismatchException $e, Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please refresh the page.'], 419);
            }
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->is_admin) return redirect('/admin/dashboard');
                if ($user->is_agent) return redirect('/agent/dashboard');
                if ($user->is_staff) return redirect('/staff/dashboard');
                return redirect('/');
            }
            return redirect('/auth/login')->with('error', 'Your session expired. Please log in again.');
        });
    })->create();
