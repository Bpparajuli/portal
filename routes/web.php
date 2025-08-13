<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\CourseController;

/*
|--------------------------------------------------------------------------
| Public / Guest Routes
|--------------------------------------------------------------------------
*/

Route::view('/', 'welcome')->name('welcome');
Route::view('/contact', 'contact')->name('contact');
Route::view('/terms', 'terms')->name('terms');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

Route::get('/dashboard', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->is_agent) {
            return redirect()->route('agent.dashboard');
        } else {
            return redirect('/user/dashboard'); // fallback
        }
    }
    return view('dashboard'); // guest view
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/logout', [LoginController::class, 'logout']); // optional fallback

    // Admin dashboard with manual check
    Route::get('/admin/dashboard', function () {
        if (!Auth::user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Agent dashboard with manual check
    Route::get('/agent/dashboard', function () {
        if (!Auth::user()->is_agent) {
            abort(403, 'Unauthorized');
        }
        return view('agent.dashboard');
    })->name('agent.dashboard');

    // Other pages
    Route::view('/student/list', 'student.list')->name('student.list');
    Route::view('/university/list', 'university.list')->name('university.list');

    // User management routes with manual check inside controller
    Route::get('/users', [UserController::class, 'list'])->name('user.list');
    Route::get('/users/create', [UserController::class, 'create'])->name('user.create');
    Route::post('/users/store', [UserController::class, 'store'])->name('user.store');
    Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
    Route::put('/users/update/{id}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/{id}/delete', [UserController::class, 'destroy'])->name('user.delete');
    Route::get('/users/waiting', [UserController::class, 'waitingList'])->name('user.waiting');
    Route::post('/users/approve/{id}', [UserController::class, 'approve'])->name('user.approve');
    Route::get('/users/{id}/profile', [UserController::class, 'profile'])->name('user.profile');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark', [NotificationController::class, 'markAsRead'])->name('notifications.mark');
    Route::post('/notifications/mark-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAll');
    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll'])->name('notifications.deleteAll');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('universities', UniversityController::class);
});
Route::resource('courses', CourseController::class)->except(['index', 'show', 'create']);
