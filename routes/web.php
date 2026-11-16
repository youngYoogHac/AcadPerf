<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('dashboard') 
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}', [DashboardController::class, 'showGroup'])->name('groups.show');
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');

    Route::post('/groups/{group}/subjects', [JournalController::class, 'storeSubject'])->name('subjects.store');
    Route::delete('/groups/{group}/subjects/{subject}', [JournalController::class, 'destroySubject'])->name('subjects.destroy');

    Route::get('/journal/{group}/{subject}', [JournalController::class, 'grid'])->name('journal.grid');
    Route::post('/journal/update', [JournalController::class, 'updateGrid'])->name('journal.update-grid');
    
    Route::post('/journal/{group}/{subject}/import', [JournalController::class, 'import'])->name('journal.import');
    Route::get('/journal/{group}/{subject}/export', [JournalController::class, 'export'])->name('journal.export');
    Route::get('/journal/{group}/{subject}/template', [JournalController::class, 'downloadTemplate'])->name('journal.template');

    Route::post('/journal/{group}/{subject}/schedule', [JournalController::class, 'storeSchedule'])->name('journal.schedule.store');
    Route::put('/journal/schedule/{schedule}', [JournalController::class, 'updateSchedule'])->name('journal.schedule.update');
    Route::put('/journal/schedule/{schedule}/type', [JournalController::class, 'updateScheduleType'])->name('journal.schedule.type');
    Route::delete('/journal/schedule/{schedule}', [JournalController::class, 'destroySchedule'])->name('journal.schedule.destroy');

    Route::post('/journal/{group}/student', [StudentController::class, 'store'])->name('journal.student.store');
    Route::put('/journal/student/{student}', [StudentController::class, 'update'])->name('journal.student.update');
    Route::delete('/journal/student/{student}', [StudentController::class, 'destroy'])->name('journal.student.destroy');

});