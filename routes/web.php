<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Guest\CourseController as GuestCourseController;
use App\Http\Controllers\Guest\UniversityController as GuestUniversityController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ContactController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\UniversityController as AdminUniversityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Agent\ApplicationController as AgentApplicationController;
use App\Http\Controllers\Agent\ChatController as AgentChatController;
use App\Http\Controllers\Agent\CourseController as AgentCourseController;
use App\Http\Controllers\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Agent\DocumentController as AgentDocumentController;
use App\Http\Controllers\Agent\NotificationController as AgentNotificationController;
use App\Http\Controllers\Agent\StudentController as AgentStudentController;
use App\Http\Controllers\Agent\UniversityController as AgentUniversityController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file contains all the routes for your application. These
| routes are loaded by the RouteServiceProvider within a group
| which contains the "web" middleware group.
|
*/

// Welcome page
Route::view('/', 'welcome')->name('welcome');

// Guest routes - Accessible to all
Route::prefix('guest')->name('guest.')->group(function () {
    Route::controller(GuestUniversityController::class)->group(function () {
        Route::get('universities', 'index')->name('universities.index');
        Route::get('universities/{university}', 'show')->name('universities.show');
    });

    Route::controller(GuestCourseController::class)->group(function () {
        Route::get('courses', 'index')->name('courses.index');
        Route::get('courses/{course}', 'show')->name('courses.show');
    });

    // Assuming this is a guest dashboard or homepage.
    Route::get('dashboard', function () {
        return view('guest.dashboard');
    })->name('dashboard');
});

// Add a route alias for `universities.show` to redirect to the correct prefixed route
Route::get('universities/{university}', function ($university) {
    return redirect()->route('guest.universities.show', ['university' => $university]);
})->name('universities.show');


// Authentication routes
Route::prefix('auth')->name('auth.')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        // Updated login routes to support both GET and POST methods
        Route::get('login', 'showLoginForm')->name('login');
        Route::post('login', 'login')->name('login.post');
    });

    Route::controller(RegisterController::class)->group(function () {
        Route::get('register', 'showRegistrationForm')->name('register');
        Route::post('register', 'register');
    });

    // Updated contact routes to handle both GET (show form) and POST (submit form)
    Route::get('contact', [ContactController::class, 'showForm'])->name('contact');
    Route::post('contact', [ContactController::class, 'submit'])->name('contact.submit');

    // Terms page
    Route::view('terms', 'auth.terms')->name('terms');
});

// A route to handle logout requests
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');


// Add route aliases for 'login' and 'register' to avoid redirect errors from middleware
Route::get('/login', function () {
    return redirect()->route('auth.login');
})->name('login');

Route::get('/register', function () {
    return redirect()->route('auth.register');
})->name('register');


// Admin routes - Protected by the 'admin' middleware
// USING THE FULL CLASS PATH TO AVOID KERNEL ALIAS ISSUES
Route::middleware(['auth', \App\Http\Middleware\IsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('chat', [AdminChatController::class, 'index'])->name('chat');
    Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications');
    Route::get('users/waiting', [AdminUserController::class, 'waiting'])->name('users.waiting');

    // Resource routes for CRUD operations
    Route::resource('applications', AdminApplicationController::class);
    Route::resource('courses', AdminCourseController::class);
    Route::resource('students', AdminStudentController::class);
    Route::resource('universities', AdminUniversityController::class);
    Route::resource('users', controller: AdminUserController::class);
    Route::resource('documents', AdminDocumentController::class);
});

// Agent routes - Protected by the 'agent' middleware
// USING THE FULL CLASS PATH TO AVOID KERNEL ALIAS ISSUES
Route::middleware(['auth', \App\Http\Middleware\IsAgent::class])->prefix('agent')->name('agent.')->group(function () {
    Route::get('dashboard', [AgentDashboardController::class, 'index'])->name('dashboard');
    Route::get('chat', [AgentChatController::class, 'index'])->name('chat');
    Route::get('notifications', [AgentNotificationController::class, 'index'])->name('notifications');

    // Resource routes for CRUD operations
    Route::resource('applications', AgentApplicationController::class)->only(['index', 'show', 'edit', 'update']);
    Route::resource('documents', AgentDocumentController::class);
    Route::resource('students', AgentStudentController::class);
    Route::resource('universities', AgentUniversityController::class)->only(['index', 'show']);
    Route::resource('courses', AgentCourseController::class)->only(['index', 'show']);
});

// User routes (assuming regular users or students)
// Add any routes here for students/users who are logged in but not admins or agents.
Route::middleware('auth')->group(function () {
    //
});
