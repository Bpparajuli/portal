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
    BackupController as AdminBackupController,
    ReminderController as AdminReminderController,
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

// Staff Controllers
use App\Http\Controllers\Staff\{
    DashboardController as StaffDashboardController,
    StudentController as StaffStudentController
};

// CRM Controllers
use App\Http\Controllers\CRM\{
    DashboardController as CrmDashboardController,
    CrmStudentController,
    CrmTasksController,
    StudentNoteController as CrmStudentNoteController,
    StudentStageController,
    StudentStageHistoryController,
};

use App\Models\UserStatus;
use App\Models\User;

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

    Route::get('dashboard', [GuestDashboardController::class, 'welcome'])
        ->name('dashboard');

    // Universities
    Route::get('universities', [GuestUniversityController::class, 'index'])->name('universities.index');
    Route::get('universities/{university}', [GuestUniversityController::class, 'show'])->name('universities.show');
    Route::get('get-cities/{country}', [GuestUniversityController::class, 'getCities'])->name('get-cities');
    Route::get('get-universities/{city}', [GuestUniversityController::class, 'getUniversities'])->name('get-universities');
    Route::get('get-course-types/{universityId}', [GuestUniversityController::class, 'getCourseTypes'])->name('get-course-types');
    Route::get('get-courses-by-type/{universityId}/{type}', [GuestUniversityController::class, 'getCoursesByType'])->name('get-courses-by-type');

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
    Route::post('/agreement/reupload', [WaitingController::class, 'reupload'])
        ->name('agreement.reupload')
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

        // Dashboard
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Chat
        Route::get('chat', [AdminChatController::class, 'usersListView'])->name('chat');
        Route::get('chat/users', [AdminChatController::class, 'usersList'])->name('chat.users');
        Route::get('chat/messages/{user}', [AdminChatController::class, 'fetchMessages'])->name('chat.messages');
        Route::post('chat/send', [AdminChatController::class, 'sendMessage'])->name('chat.send');
        Route::delete('chat/delete/{id}', [AdminChatController::class, 'delete'])->name('chat.delete');
        Route::delete('chat/clear/{user}', [AdminChatController::class, 'clear'])->name('chat.clear');

        // Notifications
        Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications');
        Route::post('notifications/mark-all', [AdminNotificationController::class, 'markAll'])->name('notifications.markAll');
        Route::post('notifications/{id}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.markRead');
        Route::post('notifications/{id}/unread', [AdminNotificationController::class, 'markAsUnread'])->name('notifications.markUnread');
        Route::get('notifications/{id}/redirect', [AdminNotificationController::class, 'readAndRedirect'])->name('notifications.readAndRedirect');

        // Users — special routes BEFORE resource
        Route::get('users/waiting', [AdminUserController::class, 'waiting'])->name('users.waiting');
        Route::put('users/{user:slug}/approve', [AdminUserController::class, 'approve'])->name('users.approve');
        Route::post('users/reminder/send', [AdminReminderController::class, 'sendAgreementReminder'])->name('reminder.send');
        Route::put('users/{user:slug}/verify-agreement', [AdminUserController::class, 'verifyAgreement'])->name('users.verifyAgreement');
        Route::delete('/users/{user:slug}/agreement/delete', [AdminUserController::class, 'deleteAgreement'])->name('users.agreement.delete');
        Route::get('users/{agent:slug}/students', [AdminUserController::class, 'students'])->name('users.students');
        Route::get('users/{agent:slug}/applications', [AdminUserController::class, 'applications'])->name('users.applications');
        Route::get('users/get-parents', [AdminUserController::class, 'getParents'])->name('users.get-parents');
        Route::resource('users', AdminUserController::class)->parameters(['users' => 'user:slug']);

        // Dynamic Data
        Route::get('get-cities/{country}', [AdminUniversityController::class, 'getCities'])->name('get-cities');
        Route::get('get-universities/{city}', [AdminUniversityController::class, 'getUniversities'])->name('get-universities');
        Route::get('get-course-types/{universityId}', [AdminUniversityController::class, 'getCourseTypes'])->name('get-course-types');
        Route::get('get-courses-by-type/{universityId}/{type}', [AdminUniversityController::class, 'getCoursesByType'])->name('get-courses-by-type');

        // Resources
        Route::resources([
            'courses'      => AdminCourseController::class,
            'students'     => AdminStudentController::class,
            'universities' => AdminUniversityController::class,
        ]);

        // Documents
        Route::prefix('students/{student}')->group(function () {
            Route::get('documents', [AdminDocumentController::class, 'index'])->name('documents.index');
            Route::get('documents/create', [AdminDocumentController::class, 'create'])->name('documents.create');
            Route::post('documents', [AdminDocumentController::class, 'store'])->name('documents.store');
            Route::post('documents/other', [AdminDocumentController::class, 'storeOther'])->name('documents.storeOther');
            Route::delete('documents/{document}/destroy', [AdminDocumentController::class, 'destroy'])->name('documents.destroy');
            Route::get('documents/{document}/download', [AdminDocumentController::class, 'download'])->name('documents.download');
            Route::get('documents/download-all', [AdminDocumentController::class, 'downloadAll'])->name('documents.downloadAll');
        });

        // Applications
        Route::get('students/{student}/applications', [AdminApplicationController::class, 'forStudent'])->name('students.applications');
        Route::patch('applications/{application}/withdraw', [AdminApplicationController::class, 'withdraw'])->name('applications.withdraw');
        Route::post('applications/{application}/add-message', [AdminApplicationController::class, 'addMessage'])->name('applications.addMessage');
        Route::delete('applications/{application}/messages/{message}', [AdminApplicationController::class, 'deleteMessage'])->name('applications.messages.delete');
        Route::resource('applications', AdminApplicationController::class);
    });

/*
|--------------------------------------------------------------------------
| Staff Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', \App\Http\Middleware\IsStaff::class])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
        Route::get('/student/{id}', [StaffStudentController::class, 'show'])->name('student');
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
        // Dashboard
        Route::get('dashboard', [AgentDashboardController::class, 'index'])->name('dashboard');

        // Chat
        Route::get('chat', [AgentChatController::class, 'usersListView'])->name('chat');
        Route::get('chat/users', [AgentChatController::class, 'usersList'])->name('chat.users');
        Route::get('chat/messages/{user}', [AgentChatController::class, 'fetchMessages'])->name('chat.messages');
        Route::post('chat/send', [AgentChatController::class, 'sendMessage'])->name('chat.send');
        Route::delete('chat/delete/{id}', [AgentChatController::class, 'delete'])->name('chat.delete');
        Route::delete('chat/clear/{user}', [AgentChatController::class, 'clear'])->name('chat.clear');

        // Notifications
        Route::get('notifications', [AgentNotificationController::class, 'index'])->name('notifications');
        Route::post('notifications/mark-all', [AgentNotificationController::class, 'markAll'])->name('notifications.markAll');
        Route::post('notifications/{id}/read', [AgentNotificationController::class, 'markAsRead'])->name('notifications.markRead');
        Route::post('notifications/{id}/unread', [AgentNotificationController::class, 'markAsUnread'])->name('notifications.markUnread');
        Route::get('notifications/{id}/redirect', [AgentNotificationController::class, 'readAndRedirect'])->name('notifications.readAndRedirect');

        // Dynamic data
        Route::get('universities', [AgentUniversityController::class, 'index'])->name('universities.index');
        Route::get('get-cities/{country}', [AgentUniversityController::class, 'getCities'])->name('get-cities');
        Route::get('get-universities/{city}', [AgentUniversityController::class, 'getUniversities'])->name('get-universities');
        Route::get('get-course-types/{universityId}', [AgentUniversityController::class, 'getCourseTypes'])->name('get-course-types');
        Route::get('get-courses-by-type/{universityId}/{type}', [AgentUniversityController::class, 'getCoursesByType'])->name('get-courses-by-type');

        // Resources
        Route::resources([
            'students'     => AgentStudentController::class,
            'universities' => AgentUniversityController::class,
            'courses'      => AgentCourseController::class,
        ]);

        // User management (own profile + staff)
        Route::get('/users/{user:slug}', [AgentUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user:slug}/edit', [AgentUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user:slug}', [AgentUserController::class, 'update'])->name('users.update');
        Route::post('/users/{user:slug}/reset-password', [AgentUserController::class, 'resetPassword'])->name('users.reset-password');

        // Staff management (agent creates/manages their own staff)
        Route::get('/staff/create', [AgentUserController::class, 'createStaff'])->name('staff.create');
        Route::post('/staff', [AgentUserController::class, 'storeStaff'])->name('staff.store');
        Route::get('/staff/{user:slug}/edit', [AgentUserController::class, 'editStaff'])->name('staff.edit');
        Route::put('/staff/{user:slug}', [AgentUserController::class, 'updateStaff'])->name('staff.update');
        Route::delete('/staff/{user:slug}', [AgentUserController::class, 'destroyStaff'])->name('staff.destroy');
        Route::get('/staff/{user:slug}', [AgentUserController::class, 'showStaff'])->name('staff.show');

        // Documents
        Route::prefix('students/{student}')->group(function () {
            Route::get('documents', [AgentDocumentController::class, 'index'])->name('documents.index');
            Route::get('documents/create', [AgentDocumentController::class, 'create'])->name('documents.create');
            Route::post('documents', [AgentDocumentController::class, 'store'])->name('documents.store');
            Route::post('documents/other', [AgentDocumentController::class, 'storeOther'])->name('documents.storeOther');
            Route::delete('documents/{document}', [AgentDocumentController::class, 'destroy'])->name('documents.destroy');
            Route::get('documents/{document}/download', [AgentDocumentController::class, 'download'])->name('documents.download');
        });

        // Applications — special routes BEFORE resource
        Route::get('students/{student}/applications', [AgentApplicationController::class, 'forStudent'])->name('students.applications');
        Route::get('applications/get-courses/{universityId}', [AgentApplicationController::class, 'getCourses'])->name('applications.get-courses');
        Route::patch('applications/{application}/withdraw', [AgentApplicationController::class, 'withdraw'])->name('applications.withdraw');
        Route::post('applications/{application}/add-message', [AgentApplicationController::class, 'addMessage'])->name('applications.addMessage');
        Route::delete('applications/{application}/messages/{message}', [AgentApplicationController::class, 'deleteMessage'])->name('applications.messages.delete');
        Route::resource('applications', AgentApplicationController::class);
    });

/*
|--------------------------------------------------------------------------
| CRM Routes
|
| Accessible by: admin (full) | agent (read-only) | staff (full, own students)
| Access control is handled inside each controller — no single-role middleware.
| URL: /crm/...   Names: crm.*
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('crm')
    ->name('crm.')
    ->group(function () {

        // ── Pipeline dashboard (Kanban / List / Table) ─────────────────────
        Route::get('/', [CrmDashboardController::class, 'index'])->name('dashboard');
        Route::get('/export', [CrmDashboardController::class, 'export'])->name('export');

        // ── Student CRM record ─────────────────────────────────────────────
        Route::get('/student/{student}', [CrmStudentController::class, 'show'])->name('student.show');
        Route::post('/student/{student}/stage', [CrmStudentController::class, 'changeStage'])->name('student.stage');
        Route::post('/student/{student}/note', [CrmStudentController::class, 'saveNote'])->name('student.note');

        // ── Tasks ──────────────────────────────────────────────────────────
        Route::post('/tasks', [CrmTasksController::class, 'store'])->name('tasks.store');
        Route::put('/tasks/{task}', [CrmTasksController::class, 'update'])->name('tasks.update');
        Route::patch('/tasks/{task}/complete', [CrmTasksController::class, 'complete'])->name('tasks.complete');
        Route::patch('/tasks/{task}/cancel', [CrmTasksController::class, 'cancel'])->name('tasks.cancel');
        Route::delete('/tasks/{task}', [CrmTasksController::class, 'destroy'])->name('tasks.destroy');

        // ── Notes ──────────────────────────────────────────────────────────
        Route::post('/notes', [CrmStudentNoteController::class, 'store'])->name('notes.store');
        Route::put('/notes/{note}', [CrmStudentNoteController::class, 'update'])->name('notes.update');
        Route::patch('/notes/{note}/pin', [CrmStudentNoteController::class, 'togglePin'])->name('notes.pin');
        Route::delete('/notes/{note}', [CrmStudentNoteController::class, 'destroy'])->name('notes.destroy');

        // ── Stage history (JSON — used by History tab fetch) ───────────────
        Route::get('/student/{student}/history', [StudentStageHistoryController::class, 'forStudent'])->name('student.history');

        // ── Configure (admin only — enforced inside controller constructor) ─
        Route::prefix('configure')->name('configure.')->group(function () {
            Route::get('/', [StudentStageController::class, 'index'])->name('index');
            Route::post('/', [StudentStageController::class, 'store'])->name('store');
            Route::put('/{stage}', [StudentStageController::class, 'update'])->name('update');
            Route::patch('/{stage}/toggle', [StudentStageController::class, 'toggleActive'])->name('toggle');
            Route::post('/reorder', [StudentStageController::class, 'reorder'])->name('reorder');
            Route::delete('/{stage}', [StudentStageController::class, 'destroy'])->name('destroy');
        });
    });

/*
|--------------------------------------------------------------------------
| Utility / Dev Routes
|--------------------------------------------------------------------------
*/
Route::get('/fix-user-status', function () {
    foreach (User::all() as $user) {
        UserStatus::firstOrCreate(
            ['user_id' => $user->id],
            ['is_online' => false, 'last_seen' => now()]
        );
    }
    return "User statuses created successfully!";
});
