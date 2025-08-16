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
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\StudentController;

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

    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard')
        ->middleware('auth'); // optional, ensures only logged-in users can access

    // Agent dashboard with manual check
    Route::get('/agent/dashboard', function () {
        if (!Auth::user()->is_agent) {
            abort(403, 'Unauthorized');
        }
        return view('agent.dashboard');
    })->name('agent.dashboard');

    // Other pages

    // User management routes with manual check inside controller
    Route::get('/user', [UserController::class, 'list'])->name('user.list');
    Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
    Route::post('/user/store', [UserController::class, 'store'])->name('user.store');
    Route::get('/user/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
    Route::put('/user/update/{id}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/{id}/delete', [UserController::class, 'destroy'])->name('user.delete');
    Route::get('/user/waiting', [UserController::class, 'waitingList'])->name('user.waiting');
    Route::post('/user/approve/{id}', [UserController::class, 'approve'])->name('user.approve');
    Route::get('/user/{id}/profile', [UserController::class, 'profile'])->name('user.profile');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark', [NotificationController::class, 'markAsRead'])->name('notifications.mark');
    Route::post('/notifications/mark-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAll');
    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll'])->name('notifications.deleteAll');
});
// Public routes
Route::get('universities', [UniversityController::class, 'index'])->name('universities.index');

// Authenticated routes for admins
Route::middleware(['auth',  AdminMiddleware::class])->group(function () {
    Route::get('universities/create', [UniversityController::class, 'create'])->name('universities.create');
    Route::post('universities', [UniversityController::class, 'store'])->name('universities.store');
    Route::get('universities/{id}/edit', [UniversityController::class, 'edit'])->name('universities.edit');
    Route::put('universities/{id}', [UniversityController::class, 'update'])->name('universities.update');
    Route::delete('universities/{id}', [UniversityController::class, 'destroy'])->name('universities.destroy');

    // Courses management
    Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::put('courses/{id}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('courses/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');
});

// This must come LAST so it doesn't override create/edit
Route::get('universities/{id}', [UniversityController::class, 'profile'])->name('universities.profile');


//students routes

Route::middleware(['auth'])->group(function () {
    // Student CRUD
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/student/list', [StudentController::class, 'list'])->name('students.list');
    Route::get('/student/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/student', [StudentController::class, 'store'])->name('students.store');
    Route::get('/student/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/student/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::get('/student/{student}', [StudentController::class, 'show'])->name('students.show');

    // Apply Now
    Route::get('/student/{student}/apply', [StudentController::class, 'apply'])->name('students.apply');
    Route::post('/student/{student}/submit-application', [StudentController::class, 'submitApplication'])->name('students.submitApplication');

    // Documents
    Route::get('/application/{application}/documents', [StudentController::class, 'documents'])->name('applications.documents');
    Route::post('/application/{application}/documents', [StudentController::class, 'storeDocuments'])->name('applications.documents.store');

    // Chat
    Route::get('/application/{application}/chat', [StudentController::class, 'chat'])->name('applications.chat');
    Route::post('/application/{application}/chat', [StudentController::class, 'storeChat'])->name('applications.chat.store');

    // Profile
    Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
});
