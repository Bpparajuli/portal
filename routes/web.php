<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\UserStatus;
use App\Models\User;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\StudentIntakeController;


// Guest Controllers
use App\Http\Controllers\Guest\{
    CourseController as GuestCourseController,
    UniversityController as GuestUniversityController,
    DashboardController as GuestDashboardController,
    EnquiryController as GuestEnquiryController,
    PageController as GuestPageController
};

// Auth Controllers
use App\Http\Controllers\Auth\{
    LoginController,
    RegisterController,

    WaitingController,
};

// Admin Controllers
use App\Http\Controllers\Admin\{
    ChatController as AdminChatController,
    DashboardController as AdminDashboardController,
    StudentController as AdminStudentController,
    UserController as AdminUserController,
    BackupController as AdminBackupController,
    ReminderController as AdminReminderController,
    ApplicationStatusController as AdminApplicationStatusController,
    EmailController as AdminEmailController,
    PageController as AdminPageController,
    SettingController as AdminSettingController,
    EnquiryController as AdminEnquiryController,
};

// Agent Controllers
use App\Http\Controllers\Agent\{
    ChatController as AgentChatController,
    DashboardController as AgentDashboardController,
    StudentController as AgentStudentController,
    UserController as AgentUserController
};

// Staff Controllers
use App\Http\Controllers\Staff\{
    DashboardController as StaffDashboardController,
    StudentController as StaffStudentController
};

// CRM Controllers
use App\Http\Controllers\CRM\{
    CrmNotificationController,
    CrmStudentController,
    CrmTasksController,
    DashboardController as CrmDashboardController,
    RevenueController as CrmRevenueController,
    StudentNoteController as CrmStudentNoteController,
    StudentStageController as CrmStudentStageController,
    StudentStageHistoryController as CrmStudentStageHistoryController,
};
use App\Models\CrmTasks;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Home / Guest Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return match (true) {
            in_array($user->role, ['admin', 'superadmin']) => redirect()->route('admin.dashboard'),
            $user->role === 'agent' => redirect()->route('agent.dashboard'),
            $user->role === 'staff' => $user->paid_crm ? redirect()->route('crm.dashboard') : redirect()->route('staff.dashboard'),
            default => redirect()->route('home'),
        };
    }

    return app(GuestDashboardController::class)->welcome(request());
})->name('home');

// Public routes at root level (no /guest prefix)
Route::name('guest.')->group(function () {

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

    // Contact / Enquiries
    Route::get('contact', [GuestEnquiryController::class, 'create'])->name('enquiries.create');
    Route::post('contact', [GuestEnquiryController::class, 'store'])->name('enquiries.store');

    // Dynamic Pages
    Route::get('p/{slug}', [GuestPageController::class, 'show'])->name('pages.show');
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

        // Backup routes
        Route::prefix('backups')->name('backup.')->group(function () {
            Route::get('/', [AdminBackupController::class, 'index'])->name('index');
            Route::post('/create', [AdminBackupController::class, 'create'])->name('create');
            Route::post('/download-sql', [AdminBackupController::class, 'downloadSql'])->name('download-sql');
            Route::get('/download-zip', [AdminBackupController::class, 'downloadZip'])->name('download-zip');
            Route::get('/download-file/{filename}', [AdminBackupController::class, 'downloadFile'])->name('download-file');
            Route::delete('/delete/{filename}', [AdminBackupController::class, 'delete'])->name('delete');
        });

        // QR Code page
        Route::get('/qr-code', [\App\Http\Controllers\Admin\QrCodeController::class, 'index'])->name('qr-code');



        // Dashboard
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Chat
        Route::get('chat', [AdminChatController::class, 'usersListView'])->name('chat');
        Route::get('chat/users', [AdminChatController::class, 'usersList'])->name('chat.users');
        Route::get('chat/messages/{user}', [AdminChatController::class, 'fetchMessages'])->name('chat.messages');
        Route::get('chat/new', [AdminChatController::class, 'fetchNewMessages'])->name('chat.new');
        Route::post('chat/send', [AdminChatController::class, 'sendMessage'])->name('chat.send');
        Route::delete('chat/delete/{id}', [AdminChatController::class, 'delete'])->name('chat.delete');
        Route::delete('chat/clear/{user}', [AdminChatController::class, 'clear'])->name('chat.clear');
        Route::post('chat/typing', [AdminChatController::class, 'typing'])->name('chat.typing');

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/mark-all', [NotificationController::class, 'markAll'])->name('notifications.markAll');
        Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markRead');
        Route::post('notifications/{id}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.markUnread');
        Route::get('notifications/{id}/redirect', [NotificationController::class, 'readAndRedirect'])->name('notifications.readAndRedirect');
        Route::delete('notifications/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');
        Route::delete('notifications', [NotificationController::class, 'deleteAll'])->name('notifications.deleteAll');

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

        Route::post('/users/reminder/preview', [AdminReminderController::class, 'previewEmail'])->name('reminder.preview');
        // Dynamic Data
        Route::get('get-cities/{country}', [UniversityController::class, 'getCities'])->name('get-cities');
        Route::get('get-universities/{city}', [UniversityController::class, 'getUniversities'])->name('get-universities');
        Route::get('get-course-types/{universityId}', [UniversityController::class, 'getCourseTypes'])->name('get-course-types');
        Route::get('get-courses-by-type/{universityId}/{type}', [UniversityController::class, 'getCoursesByType'])->name('get-courses-by-type');

        // Resources
        Route::resources([
            'courses'      => CourseController::class,
            'students'     => AdminStudentController::class,
            'universities' => UniversityController::class,
        ]);

        // Documents
        Route::prefix('students/{student}')->group(function () {
            Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
            Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
            Route::post('documents/other', [DocumentController::class, 'storeOther'])->name('documents.storeOther');
            Route::delete('documents/{document}/destroy', [DocumentController::class, 'destroy'])->name('documents.destroy');
            Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
            Route::get('documents/download-all', [DocumentController::class, 'downloadAll'])->name('documents.downloadAll');
        });

        // Applications
        Route::get('applications/get-courses/{universityId}', [ApplicationController::class, 'getCourses'])->name('applications.get-courses');
        Route::get('students/{student}/applications', [ApplicationController::class, 'forStudent'])->name('students.applications');
        Route::patch('applications/{application}/withdraw', [ApplicationController::class, 'withdraw'])->name('applications.withdraw');
        Route::post('applications/{application}/add-message', [ApplicationController::class, 'addMessage'])->name('applications.addMessage');
        Route::delete('applications/{application}/messages/{message}', [ApplicationController::class, 'deleteMessage'])->name('applications.messages.delete');
        Route::resource('applications', ApplicationController::class);

        // Export routes
        Route::get('students/export', [AdminStudentController::class, 'export'])->name('students.export');
        Route::get('applications/export', [ApplicationController::class, 'export'])->name('applications.export');
        Route::get('exports', [\App\Http\Controllers\Admin\ExportController::class, 'index'])->name('exports.index');
        Route::post('exports/export', [\App\Http\Controllers\Admin\ExportController::class, 'export'])->name('exports.export');

        // Revenue routes
        Route::get('revenues', [\App\Http\Controllers\Admin\RevenueController::class, 'index'])->name('revenues.index');
        Route::get('revenues/{revenue}/edit', [\App\Http\Controllers\Admin\RevenueController::class, 'edit'])->name('revenues.edit');
        Route::put('revenues/{revenue}', [\App\Http\Controllers\Admin\RevenueController::class, 'update'])->name('revenues.update');
        Route::delete('revenues/{revenue}', [\App\Http\Controllers\Admin\RevenueController::class, 'destroy'])->name('revenues.destroy');

        /*
        |--------------------------------------------------------------------------
        | Application Status Management
        |--------------------------------------------------------------------------
        */

        Route::get('application-status', [AdminApplicationStatusController::class, 'index'])
            ->name('application-status.index');

        Route::post('application-status', [AdminApplicationStatusController::class, 'store'])
            ->name('application-status.store');

        Route::put('application-status/{id}', [AdminApplicationStatusController::class, 'update'])
            ->name('application-status.update');

        Route::delete('application-status/{id}', [AdminApplicationStatusController::class, 'destroy'])
            ->name('application-status.destroy');

        // ========== NEW: EMAIL SYSTEM ==========
        Route::prefix('emails')->name('emails.')->group(function () {
            Route::get('inbox', [AdminEmailController::class, 'inbox'])->name('inbox');
            Route::get('sent', [AdminEmailController::class, 'sent'])->name('sent');
            Route::get('drafts', [AdminEmailController::class, 'drafts'])->name('drafts');
            Route::get('create', [AdminEmailController::class, 'create'])->name('create');
            Route::post('store', [AdminEmailController::class, 'store'])->name('store');
            Route::get('{email}', [AdminEmailController::class, 'show'])->name('show');
            Route::post('{email}/reply', [AdminEmailController::class, 'reply'])->name('reply');
            Route::post('save-draft', [AdminEmailController::class, 'saveDraft'])->name('save-draft');
            Route::delete('{email}', [AdminEmailController::class, 'destroy'])->name('destroy');
            Route::post('{email}/toggle-star', [AdminEmailController::class, 'toggleStar'])->name('toggle-star');
            Route::get('{email}/download/{index}', [AdminEmailController::class, 'downloadAttachment'])->name('download-attachment');
        });

        // ========== NEW: DYNAMIC PAGES ==========
        Route::resource('pages', AdminPageController::class);
        Route::get('pages/dynamic/content', [\App\Http\Controllers\Admin\PageController::class, 'dynamic'])->name('pages.dynamic');
        Route::post('pages/dynamic/update', [\App\Http\Controllers\Admin\PageController::class, 'updateDynamic'])->name('pages.dynamic.update');

        // ========== NEW: SETTINGS ==========
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [AdminSettingController::class, 'index'])->name('index');
            Route::put('/{setting}', [AdminSettingController::class, 'update'])->name('update');
            Route::post('/create', [AdminSettingController::class, 'store'])->name('store');
            Route::delete('{setting}', [AdminSettingController::class, 'destroy'])->name('destroy');
            Route::post('/upload-image', [AdminSettingController::class, 'uploadImage'])->name('upload-image');
            Route::get('/images', [AdminSettingController::class, 'listImages'])->name('images');
        });

        // ========== NEW: ENQUIRIES ==========
        Route::resource('enquiries', AdminEnquiryController::class)->only(['index', 'show', 'destroy']);
        Route::post('enquiries/{enquiry}/reply', [AdminEnquiryController::class, 'reply'])->name('enquiries.reply');

        // ========== TESTIMONIALS ==========
        Route::resource('testimonials', \App\Http\Controllers\Admin\TestimonialController::class);

        // ========== ACTIVITY LOG ==========
        Route::prefix('activities')->name('activities.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('index');
            Route::get('{activity}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'show'])->name('show');
            Route::delete('{activity}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'destroy'])->name('destroy');
            Route::post('clear-all', [\App\Http\Controllers\Admin\ActivityLogController::class, 'clearAll'])->name('clearAll');
        });
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

        // Student management
        Route::get('/students', [StaffStudentController::class, 'index'])->name('students.index');
        Route::get('/student/{student}', [StaffStudentController::class, 'show'])->name('student.show');
        Route::get('/student/{student}/edit', [StaffStudentController::class, 'edit'])->name('student.edit');
        Route::put('/student/{student}', [StaffStudentController::class, 'update'])->name('student.update');

        // Staff document management
        Route::prefix('student/{student}/documents')->name('documents.')->group(function () {
            Route::get('/', [DocumentController::class, 'index'])->name('index');
            Route::post('/', [DocumentController::class, 'store'])->name('store');
            Route::post('/other', [DocumentController::class, 'storeOther'])->name('storeOther');
            Route::delete('{document}', [DocumentController::class, 'destroy'])->name('destroy');
            Route::get('{document}/download', [DocumentController::class, 'download'])->name('download');
            Route::get('download-all', [DocumentController::class, 'downloadAll'])->name('downloadAll');
        });

        // University & Course - read only
        Route::get('/universities', [UniversityController::class, 'index'])->name('universities');
        Route::get('/universities/{university}', [UniversityController::class, 'show'])->name('universities.show');
        Route::get('/courses', [CourseController::class, 'index'])->name('courses');
        Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

        // University & Course - edit & create (staff can edit/create, not delete)
        Route::get('/universities/create', [UniversityController::class, 'create'])->name('universities.create');
        Route::post('/universities', [UniversityController::class, 'store'])->name('universities.store');
        Route::get('/universities/{university}/edit', [UniversityController::class, 'edit'])->name('universities.edit');
        Route::put('/universities/{university}', [UniversityController::class, 'update'])->name('universities.update');
        Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');

        // Applications
        Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
        Route::patch('/applications/{application}/status', [ApplicationController::class, 'updateStatus'])->name('applications.updateStatus');

        // Chat
        Route::prefix('chat')->name('chat.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Staff\ChatController::class, 'index'])->name('index');
            Route::get('/users', [\App\Http\Controllers\Staff\ChatController::class, 'usersList'])->name('users');
            Route::get('/messages/{user}', [\App\Http\Controllers\Staff\ChatController::class, 'fetchMessages'])->name('messages');
            Route::get('/new', [\App\Http\Controllers\Staff\ChatController::class, 'fetchNewMessages'])->name('new');
            Route::post('/send', [\App\Http\Controllers\Staff\ChatController::class, 'sendMessage'])->name('send');
            Route::delete('/delete/{id}', [\App\Http\Controllers\Staff\ChatController::class, 'delete'])->name('delete');
            Route::delete('/clear/{user}', [\App\Http\Controllers\Staff\ChatController::class, 'clear'])->name('clear');
            Route::post('/typing', [\App\Http\Controllers\Staff\ChatController::class, 'typing'])->name('typing');
        });

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/mark-all', [NotificationController::class, 'markAll'])->name('markAll');
            Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('markRead');
            Route::get('/{id}/redirect', [NotificationController::class, 'readAndRedirect'])->name('readAndRedirect');
            Route::delete('/{id}', [NotificationController::class, 'delete'])->name('delete');
            Route::delete('/all/delete', [NotificationController::class, 'deleteAll'])->name('deleteAll');
        });
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
        Route::get('chat/new', [AgentChatController::class, 'fetchNewMessages'])->name('chat.new');
        Route::post('chat/send', [AgentChatController::class, 'sendMessage'])->name('chat.send');
        Route::delete('chat/delete/{id}', [AgentChatController::class, 'delete'])->name('chat.delete');
        Route::delete('chat/clear/{user}', [AgentChatController::class, 'clear'])->name('chat.clear');
        Route::post('chat/typing', [AgentChatController::class, 'typing'])->name('chat.typing');

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/mark-all', [NotificationController::class, 'markAll'])->name('notifications.markAll');
        Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markRead');
        Route::post('notifications/{id}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.markUnread');
        Route::get('notifications/{id}/redirect', [NotificationController::class, 'readAndRedirect'])->name('notifications.readAndRedirect');
        Route::delete('notifications/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');
        Route::delete('notifications', [NotificationController::class, 'deleteAll'])->name('notifications.deleteAll');

        // Dynamic data
        Route::get('universities', [UniversityController::class, 'index'])->name('universities.index');
        Route::get('get-cities/{country}', [UniversityController::class, 'getCities'])->name('get-cities');
        Route::get('get-universities/{city}', [UniversityController::class, 'getUniversities'])->name('get-universities');
        Route::get('get-course-types/{universityId}', [UniversityController::class, 'getCourseTypes'])->name('get-course-types');
        Route::get('get-courses-by-type/{universityId}/{type}', [UniversityController::class, 'getCoursesByType'])->name('get-courses-by-type');

        // Resources (agents get full CRUD on students, read-only on universities/courses)
        Route::resource('students', AgentStudentController::class);
        Route::get('universities/{university}', [UniversityController::class, 'show'])->name('universities.show');
        Route::resource('courses', CourseController::class)->only(['index', 'show']);

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

        // Applications - special routes BEFORE resource
        // Quick start route - MUST be before the resource routes
        Route::get('applications/quick-start', [ApplicationController::class, 'quickStart'])->name('applications.quick-start');

        Route::get('students/{student}/applications', [ApplicationController::class, 'forStudent'])->name('students.applications');
        Route::get('applications/get-courses/{universityId}', [ApplicationController::class, 'getCourses'])->name('applications.get-courses');
        Route::patch('applications/{application}/withdraw', [ApplicationController::class, 'withdraw'])->name('applications.withdraw');
        Route::post('applications/{application}/add-message', [ApplicationController::class, 'addMessage'])->name('applications.addMessage');
        Route::delete('applications/{application}/messages/{message}', [ApplicationController::class, 'deleteMessage'])->name('applications.messages.delete');

        Route::resource('applications', ApplicationController::class);

        // Documents
        Route::prefix('students/{student}')->group(function () {
            Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
            Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
            Route::post('documents/other', [DocumentController::class, 'storeOther'])->name('documents.storeOther');
            Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
            Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        });
    });

/*
|--------------------------------------------------------------------------
| CRM Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', \App\Http\Middleware\IsPaidCrm::class])
    ->prefix('crm')
    ->name('crm.')
    ->group(function () {

        // ========== DASHBOARD ROUTES ==========
        Route::get('/', [CrmDashboardController::class, 'index'])->name('dashboard');
        Route::get('/export', [CrmDashboardController::class, 'export'])->name('export');
        Route::put('/student/{id}/update-rating', [CrmStudentController::class, 'updateRating'])->name('dashboard.updateRating');
        Route::put('/students/{id}/rating-simple', [CrmDashboardController::class, 'updateRatingSimple'])->name('dashboard.updateRatingSimple');
        Route::put('/students/{student}/toggle-pin', [CrmDashboardController::class, 'togglePin'])->name('student.toggle-pin');

        // ========== TAG MANAGEMENT ==========
        Route::put('/students/{student}/add-tag', [CrmDashboardController::class, 'addTag'])->name('student.addTag');
        Route::delete('/students/{student}/remove-tag', [CrmDashboardController::class, 'removeTag'])->name('student.removeTag');
        Route::get('/popular-tags', [CrmDashboardController::class, 'getPopularTags'])->name('student.popularTags');
        Route::put('/students/{student}/stage', [CrmDashboardController::class, 'updateStage'])->name('student.update-stage');

        // ========== STUDENT CRM RECORDS ==========
        Route::put('/students/{student}/mini-update', [CrmStudentController::class, 'miniUpdate'])->name('student.miniUpdate');
        Route::get('/student/{student}/edit', [CrmStudentController::class, 'edit'])->name('student.edit');
        Route::put('/student/{student}', [CrmStudentController::class, 'update'])->name('student.update');
        Route::get('/student/{student}', [CrmStudentController::class, 'show'])->name('student.show');
        Route::delete('/student/{student}', [CrmStudentController::class, 'destroy'])->name('student.destroy');
        Route::post('/student/store', [CrmStudentController::class, 'store'])->name('student.store');
        Route::post('/student/{student}/stage', [CrmStudentController::class, 'changeStage'])->name('student.stage');
        Route::post('/student/{student}/note', [CrmStudentController::class, 'saveNote'])->name('student.saveNote');

        // ========== REVENUE ROUTES ==========
        Route::post('/students/{student}/revenues', [CrmRevenueController::class, 'store'])->name('student.revenues.store');
        Route::get('/students/{student}/revenues/{revenue}', [CrmRevenueController::class, 'show'])->name('student.revenues.show');
        Route::put('/students/{student}/revenues/{revenue}', [CrmRevenueController::class, 'update'])->name('student.revenues.update');
        Route::delete('/students/{student}/revenues/{revenue}', [CrmRevenueController::class, 'destroy'])->name('student.revenues.destroy');
        Route::get('/students/{student}/revenues/{revenue}/download', [CrmRevenueController::class, 'downloadReceipt'])->name('student.revenues.download');
        // ========== TASKS ROUTES ==========
        Route::post('/tasks', [CrmTasksController::class, 'store'])->name('tasks.store');
        Route::put('/tasks/{task}', [CrmTasksController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [CrmTasksController::class, 'destroy'])->name('tasks.destroy');
        Route::patch('/tasks/{task}/complete', [CrmTasksController::class, 'complete'])->name('tasks.complete');
        Route::patch('/tasks/{task}/cancel', [CrmTasksController::class, 'cancel'])->name('tasks.cancel');
        Route::patch('/tasks/{task}/undo', [CrmTasksController::class, 'undoComplete'])->name('tasks.undo');
        Route::patch('/tasks/{task}/undo-cancel', [CrmTasksController::class, 'undoCancel'])->name('tasks.undo-cancel');
        Route::get('/tasks/{task}/data', [CrmTasksController::class, 'getEditData'])->name('tasks.data');
        Route::patch('/tasks/{task}/reschedule', [CrmTasksController::class, 'reschedule'])->name('tasks.reschedule');
        Route::post('/tasks/check-due', [CrmTasksController::class, 'checkDueTasks'])->name('tasks.check-due');
        Route::delete('/tasks/{task}', [CrmTasksController::class, 'destroy'])
            ->name('tasks.destroy');
        Route::get('/my-today-tasks', [CrmStudentController::class, 'getMyTodayTasksStudents'])
            ->name('my-today-tasks');
        Route::post('/tasks/check-duplicate/{studentId}', [CrmTasksController::class, 'checkDuplicate'])
            ->name('tasks.check-duplicate');

        // ========== TASK STATISTICS ROUTES ==========
        Route::get('/task-stats', [CrmDashboardController::class, 'getTaskStats'])->name('task-stats');
        Route::get('/task-stats/details/{type}', [CrmDashboardController::class, 'getTaskDetails'])->name('task-stats.details');
        Route::get('/today-task-students', [CrmStudentController::class, 'getTodayTaskStudentIds'])
            ->name('today-task-students');

        // ========== NOTES ROUTES ==========
        Route::post('/notes', [CrmStudentNoteController::class, 'store'])->name('notes.store');
        Route::put('/notes/{note}', [CrmStudentNoteController::class, 'update'])->name('notes.update');
        Route::patch('/notes/{note}/pin', [CrmStudentNoteController::class, 'togglePin'])->name('notes.pin');
        Route::delete('/notes/{note}', [CrmStudentNoteController::class, 'destroy'])->name('notes.destroy');

        // ========== STAGE HISTORY ==========
        Route::get('/student/{student}/history', [CrmStudentStageHistoryController::class, 'forStudent'])->name('student.history');

        // ========== CALENDAR ROUTES ==========
        Route::get('/weekly-tasks', [CrmDashboardController::class, 'weeklyTasks'])->name('weekly.tasks');
        Route::get('/calendar/staff-tasks', [CrmDashboardController::class, 'staffTasksForDate'])->name('calendar.staff-tasks');
        Route::post('/calendar/events', [CrmDashboardController::class, 'calendarEvents'])->name('calendar.events');

        // ========== CRM NOTIFICATION ROUTES ==========
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/fetch', [CrmNotificationController::class, 'fetch'])->name('fetch');
            Route::post('/mark-all-read', [CrmNotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::post('/{id}/mark-read', [CrmNotificationController::class, 'markAsRead'])->name('mark-read');
            Route::get('/{id}/redirect', [CrmNotificationController::class, 'markAsReadAndRedirect'])->name('redirect');

            // Important: Put delete routes BEFORE any wildcard routes
            Route::delete('/read/all', [CrmNotificationController::class, 'destroyRead'])->name('destroy-read');
            Route::delete('/{id}/delete-ajax', [CrmNotificationController::class, 'deleteNotification'])->name('delete-ajax');

            Route::get('/settings', [CrmNotificationController::class, 'settings'])->name('settings');
            Route::post('/settings', [CrmNotificationController::class, 'updateSettings'])->name('update-settings');
            Route::get('/all', [CrmNotificationController::class, 'all'])->name('all');
        });

        // ========== CONFIGURE ROUTES (Admin only) ==========
        Route::prefix('configure')->name('configure.')->group(function () {
            Route::get('/', [CrmStudentStageController::class, 'index'])->name('index');
            Route::post('/', [CrmStudentStageController::class, 'store'])->name('store');
            Route::put('/{stage}', [CrmStudentStageController::class, 'update'])->name('update');
            Route::patch('/{stage}/toggle', [CrmStudentStageController::class, 'toggleActive'])->name('toggle');
            Route::post('/reorder', [CrmStudentStageController::class, 'reorder'])->name('reorder');
            Route::delete('/{stage}', [CrmStudentStageController::class, 'destroy'])->name('destroy');
        });

        Route::get('/debug/staff-role', [CrmStudentController::class, 'debugStaffRole'])->name('debug.staff-role');

        Route::get('/debug/task-visibility/{student}', [CrmStudentController::class, 'debugAllTasksForStaff'])->name('debug.task-visibility');
    });


/*
|--------------------------------------------------------------------------
| Global Chat Unread Count (all roles)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/chat/unread-count', function () {
    return response()->json([
        'count' => \App\Models\ChatMessage::where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->count()
    ]);
})->name('chat.unread');

/*
|--------------------------------------------------------------------------
| AI Assistant Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('ai')->name('ai.')->group(function () {
    Route::get('/assistant', [\App\Http\Controllers\AI\AssistantController::class, 'index'])->name('assistant');
    Route::post('/chat', [\App\Http\Controllers\AI\AssistantController::class, 'chat'])->name('chat');
    Route::post('/analyze', [\App\Http\Controllers\AI\AssistantController::class, 'analyze'])->name('analyze');
    Route::get('/settings', [\App\Http\Controllers\AI\AssistantController::class, 'settings'])->name('settings');
    Route::post('/settings', [\App\Http\Controllers\AI\AssistantController::class, 'updateSettings'])->name('settings.update');
});

// ============================================
// STUDENT INTAKE FORM - Public Form
// ============================================

// Show the web form
Route::get('/student-intake-form', [StudentIntakeController::class, 'showForm'])
    ->name('student.intake.form');

// Quick add via GET (for testing)
Route::get('/student/quick-add', [StudentIntakeController::class, 'quickAdd'])
    ->name('student.quick.add');

// API endpoint for all submissions (WhatsApp, Facebook, Web Form)
Route::post('/api/student/intake', [StudentIntakeController::class, 'intake'])
    ->name('api.student.intake');
// Thank You Page
Route::get('/thank-you', function () {
    // Get the last created student from session or parameter
    $student = session('last_student');

    if (!$student) {
        // If no student in session, redirect to form
        return redirect()->route('student.intake.form');
    }

    return view('intake.thank-you', compact('student'));
})->name('thank-you');


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
// Add this at the end of your web.php file, before the closing PHP tag if any
Route::get('/receipt-view/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404, 'File not found');
    }

    // Get file extension
    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

    // Set appropriate headers based on file type
    $contentTypes = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];

    $contentType = $contentTypes[$extension] ?? 'application/octet-stream';

    return response()->file($fullPath, [
        'Content-Type' => $contentType,
        'Content-Disposition' => 'inline',
        'Cache-Control' => 'public, max-age=3600',
        'X-Content-Type-Options' => 'nosniff'
    ]);
})->where('path', '.*')->name('receipt.view');
