<?php

use App\Http\Controllers\CRM\DashboardController;
use App\Http\Controllers\CRM\CrmStudentController;
use App\Http\Controllers\CRM\CrmTasksController;
use App\Http\Controllers\CRM\StudentNoteController;
use App\Http\Controllers\CRM\StudentStageController;
use App\Http\Controllers\CRM\StudentStageHistoryController;
use Illuminate\Support\Facades\Route;

// =============================================================================
// CRM Routes
//
// Middleware:
//   'auth'         — must be logged in
//   'crm.access'   — allows admin, agent, staff  (create this middleware or
//                    use: middleware(['auth'])->whereIn('role', [...]) )
//   'crm.editor'   — allows admin and staff only (not agent — they are read-only)
//   'admin'        — admin only (for configure page)
//
// If you don't have crm.access / crm.editor middleware yet, the simplest
// approach is to put the logic inside each controller (already done) and
// just use 'auth' here until you create those middleware.
// =============================================================================

Route::prefix('crm')->name('crm.')->middleware(['auth'])->group(function () {

    // ─── Dashboard (Kanban / List / Table pipeline) ────────────────────────
    // Admin: all students | Agent: read-only their territory | Staff: own students
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/export', [DashboardController::class, 'export'])->name('export');

    // ─── Student CRM record ───────────────────────────────────────────────
    Route::get('/student/{student}', [CrmStudentController::class, 'show'])->name('student.show');

    // Stage change from show page (admin + staff only)
    Route::post('/student/{student}/stage', [CrmStudentController::class, 'changeStage'])->name('student.stage');

    // Inline note save from the notes panel on show page (admin + staff only)
    Route::post('/student/{student}/note', [CrmStudentController::class, 'saveNote'])->name('student.note');

    // ─── Tasks ───────────────────────────────────────────────────────────
    Route::post('/tasks', [CrmTasksController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [CrmTasksController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/complete', [CrmTasksController::class, 'complete'])->name('tasks.complete');
    Route::patch('/tasks/{task}/cancel', [CrmTasksController::class, 'cancel'])->name('tasks.cancel');
    Route::delete('/tasks/{task}', [CrmTasksController::class, 'destroy'])->name('tasks.destroy');

    // ─── Notes ───────────────────────────────────────────────────────────
    Route::post('/notes', [StudentNoteController::class, 'store'])->name('notes.store');
    Route::put('/notes/{note}', [StudentNoteController::class, 'update'])->name('notes.update');
    Route::patch('/notes/{note}/pin', [StudentNoteController::class, 'togglePin'])->name('notes.pin');
    Route::delete('/notes/{note}', [StudentNoteController::class, 'destroy'])->name('notes.destroy');

    // ─── Stage History (read — returns JSON for History tab) ──────────────
    Route::get('/student/{student}/history', [StudentStageHistoryController::class, 'forStudent'])->name('student.history');

    // ─── Configure (admin only) ───────────────────────────────────────────
    Route::middleware('isAdmin')->prefix('configure')->name('configure.')->group(function () {
        Route::get('/', [StudentStageController::class, 'index'])->name('index');
        Route::post('/', [StudentStageController::class, 'store'])->name('store');
        Route::put('/{stage}', [StudentStageController::class, 'update'])->name('update');
        Route::patch('/{stage}/toggle', [StudentStageController::class, 'toggleActive'])->name('toggle');
        Route::post('/reorder', [StudentStageController::class, 'reorder'])->name('reorder');
        Route::delete('/{stage}', [StudentStageController::class, 'destroy'])->name('destroy');
    });
});
