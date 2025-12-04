<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest Controllers
use App\Http\Controllers\Guest\{
    CourseController as GuestCourseController,
    UniversityController as GuestUniversityController,
    DashboardController as GuestDashboardController
};

// Auth Controllers
use App\Http\Controllers\Auth\{
    LoginController,
    RegisterController,
    ContactController,
    WaitingController,
};

// Admin Controllers
use App\Http\Controllers\Admin\{
    ApplicationController as AdminApplicationController,
    ChatController as AdminChatController,
    CourseController as AdminCourseController,
    DashboardController as AdminDashboardController,
    DocumentController as AdminDocumentController,
    NotificationController as AdminNotificationController,
    StudentController as AdminStudentController,
    UniversityController as AdminUniversityController,
    UserController as AdminUserController,
    BackupController as AdminBackupController
};

// Agent Controllers
use App\Http\Controllers\Agent\{
    ApplicationController as AgentApplicationController,
    ChatController as AgentChatController,
    CourseController as AgentCourseController,
    DashboardController as AgentDashboardController,
    DocumentController as AgentDocumentController,
    NotificationController as AgentNotificationController,
    StudentController as AgentStudentController,
    UniversityController as AgentUniversityController,
    UserController as AgentUserController
};


/*
|--------------------------------------------------------------------------
| Home / Guest Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->is_admin
            ? redirect()->route('admin.dashboard')
            : (Auth::user()->is_agent
                ? redirect()->route('agent.dashboard')
                : redirect()->route('guest.dashboard'));
    }

    return app(GuestDashboardController::class)->welcome(request());
})->name('home');

Route::prefix('guest')->name('guest.')->group(function () {
    Route::get('dashboard', fn() => view('guest.dashboard'))->name('dashboard');

    // Universities
    Route::get('universities', [GuestUniversityController::class, 'index'])->name('universities.index');
    Route::get('universities/{university}', [GuestUniversityController::class, 'show'])->name('universities.show');

    // Ajax Filters
    Route::get('get-cities/{country}', [GuestUniversityController::class, 'getCities'])->name('get-cities');
    Route::get('get-universities/{city}', [GuestUniversityController::class, 'getUniversities'])->name('get-universities');
    Route::get('get-courses/{universityId}', [GuestUniversityController::class, 'getCourses'])->name('get-courses');

    // Courses
    Route::controller(GuestCourseController::class)->group(function () {
        Route::get('courses', 'index')->name('courses.index');
        Route::get('courses/{course}', 'show')->name('courses.show');
    });
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->name('auth.')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('login', 'showLoginForm')->name('login');
        Route::post('login', 'login')->name('login.post');
    });

    Route::controller(RegisterController::class)->group(function () {
        Route::get('register', 'showRegistrationForm')->name('register');
        Route::post('register', 'register');
    });

    Route::get('contact', [ContactController::class, 'showForm'])->name('contact');
    Route::post('contact', [ContactController::class, 'submit'])->name('contact.submit');

    Route::view('terms', 'auth.terms')->name('terms');

    Route::get('/waiting-dash', [WaitingController::class, 'show'])
        ->name('waiting-dash')
        ->middleware('auth');

    Route::post('/agreement/upload', [WaitingController::class, 'upload'])
        ->name('agreement.upload')
        ->middleware('auth');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Alias routes (for redirects)
Route::redirect('/login', '/auth/login')->name('login');
Route::redirect('/register', '/auth/register')->name('register');


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', \App\Http\Middleware\IsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/backup-files', [AdminBackupController::class, 'backupFilesIfChanged'])
            ->name('backup.files');

        // ---------------------------
        // Dashboard & Chat
        // ---------------------------
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('chat', [AdminChatController::class, 'index'])->name('chat');

        // ---------------------------
        // Notifications
        // ---------------------------
        Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications');
        Route::post('notifications/mark-all', [AdminNotificationController::class, 'markAll'])->name('notifications.markAll');
        Route::post('notifications/{id}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.markRead');
        Route::post('notifications/{id}/unread', [AdminNotificationController::class, 'markAsUnread'])->name('notifications.markUnread');
        Route::get('notifications/{id}/redirect', [AdminNotificationController::class, 'readAndRedirect'])->name('notifications.readAndRedirect');

        // ALL Users routes 
        // WAITING FOR ACCOUNT APPROVAL
        Route::get('users/waiting', [AdminUserController::class, 'waiting'])
            ->name('users.waiting');

        // APPROVE USER
        Route::put('users/{user:slug}/approve', [AdminUserController::class, 'approve'])
            ->name('users.approve');

        // VERIFY AGREEMENT
        Route::put('users/{user:slug}/verify-agreement', [AdminUserController::class, 'verifyAgreement'])
            ->name('users.verifyAgreement');

        // DELETE AGREEMENT
        Route::delete('/users/{user:slug}/agreement/delete', [AdminUserController::class, 'deleteAgreement'])
            ->name('users.agreement.delete');

        // STUDENTS
        Route::get('users/{agent:slug}/students', [AdminUserController::class, 'students'])
            ->name('users.students');

        // APPLICATIONS
        Route::get('users/{agent:slug}/applications', [AdminUserController::class, 'applications'])
            ->name('users.applications');

        // RESOURCE route (must come last)
        Route::resource('users', AdminUserController::class)
            ->parameters(['users' => 'user:slug']);

        // ---------------------------
        // Dynamic Data
        // ---------------------------
        Route::get('get-cities/{country}', [AdminUniversityController::class, 'getCities'])->name('get-cities');
        Route::get('get-universities/{city}', [AdminUniversityController::class, 'getUniversities'])->name('get-universities');
        Route::get('get-courses/{universityId}', [AdminUniversityController::class, 'getCourses'])->name('get-courses');

        // ---------------------------
        // Resources
        // ---------------------------
        Route::resources([
            'courses' => AdminCourseController::class,
            'students' => AdminStudentController::class,
            'universities' => AdminUniversityController::class,
        ]);

        // ---------------------------
        // Documents
        // ---------------------------
        Route::prefix('students/{student}')->group(function () {
            Route::get('documents', [AdminDocumentController::class, 'index'])->name('documents.index');
            Route::get('documents/create', [AdminDocumentController::class, 'create'])->name('documents.create');
            Route::post('documents', [AdminDocumentController::class, 'store'])->name('documents.store');
            Route::post('documents/other', [AdminDocumentController::class, 'storeOther'])->name('documents.storeOther');
            Route::delete('documents/{document}/destroy', [AdminDocumentController::class, 'destroy'])->name('documents.destroy');
            Route::get('documents/{document}/download', [AdminDocumentController::class, 'download'])->name('documents.download');
            Route::get('documents/download-all', [AdminDocumentController::class, 'downloadAll'])->name('documents.downloadAll');
        });

        // ---------------------------
        // Applications
        // ---------------------------
        Route::get('students/{student}/applications', [AdminApplicationController::class, 'forStudent'])->name('students.applications');
        Route::patch('applications/{application}/withdraw', [AdminApplicationController::class, 'withdraw'])->name('applications.withdraw');
        Route::post('applications/{application}/add-message', [AdminApplicationController::class, 'addMessage'])->name('applications.addMessage');
        Route::delete('applications/{application}/messages/{message}', [AdminApplicationController::class, 'deleteMessage'])->name('applications.messages.delete');
        Route::resource('applications', AdminApplicationController::class);
    });

/*
|--------------------------------------------------------------------------
| Agent Routes
|--------------------------------------------------------------------------
*/


Route::middleware(['auth', \App\Http\Middleware\IsAgent::class])
    ->prefix('agent')
    ->name('agent.')
    ->group(function () {

        Route::get('dashboard', [AgentDashboardController::class, 'index'])->name('dashboard');
        Route::get('chat', [AgentChatController::class, 'index'])->name('chat');

        // Notifications
        Route::get('notifications', [AgentNotificationController::class, 'index'])->name('notifications');
        Route::post('notifications/mark-all', [AgentNotificationController::class, 'markAll'])->name('notifications.markAll');
        Route::post('notifications/{id}/read', [AgentNotificationController::class, 'markAsRead'])->name('notifications.markRead');
        Route::post('notifications/{id}/unread', [AgentNotificationController::class, 'markAsUnread'])->name('notifications.markUnread');
        Route::get('notifications/{id}/redirect', [AgentNotificationController::class, 'readAndRedirect'])->name('notifications.readAndRedirect');


        // Filters
        Route::get('get-cities/{country}', [AgentUniversityController::class, 'getCities'])->name('get-cities');
        Route::get('get-universities/{city}', [AgentUniversityController::class, 'getUniversities'])->name('get-universities');
        Route::get('get-courses/{universityId}', [AgentUniversityController::class, 'getCourses'])->name('get-courses');

        // Resources
        Route::resources([
            'students' => AgentStudentController::class,
            'universities' => AgentUniversityController::class,
            'courses' => AgentCourseController::class,
        ]);

        Route::get('/users/{user:slug}', [AgentUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user:slug}/edit', [AgentUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user:slug}', [AgentUserController::class, 'update'])->name('users.update');
        Route::post('/users/{user:slug}/reset-password', [AgentUserController::class, 'resetPassword'])->name('users.reset-password');

        // Documents
        Route::prefix('students/{student}')->group(function () {
            Route::get('documents', [AgentDocumentController::class, 'index'])->name('documents.index');
            Route::get('documents/create', [AgentDocumentController::class, 'create'])->name('documents.create');
            Route::post('documents', [AgentDocumentController::class, 'store'])->name('documents.store');
            Route::post('documents/other', [AgentDocumentController::class, 'storeOther'])->name('documents.storeOther');
            Route::delete('documents/{document}', [AgentDocumentController::class, 'destroy'])->name('documents.destroy');
            Route::get('documents/{document}/download', [AgentDocumentController::class, 'download'])->name('documents.download');
        });

        // Applications
        Route::get('students/{student}/applications', [AgentApplicationController::class, 'forStudent'])
            ->name('students.applications');
        Route::get('applications/get-courses/{universityId}', [AgentApplicationController::class, 'getCourses'])
            ->name('applications.get-courses');
        Route::patch('applications/{application}/withdraw', [AgentApplicationController::class, 'withdraw'])
            ->name('applications.withdraw');
        Route::post('applications/{application}/add-message', [AgentApplicationController::class, 'addMessage'])
            ->name('applications.addMessage');

        // MUST BE BEFORE RESOURCE
        Route::delete(
            'applications/{application}/messages/{message}',
            [AgentApplicationController::class, 'deleteMessage']
        )
            ->name('applications.messages.delete');

        // Resource conflicts must always come last
        Route::resource('applications', AgentApplicationController::class);
    });
