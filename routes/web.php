<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
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
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('admin')->group(function () {
        
        Route::resource('groups', GroupController::class)
            ->only(['index', 'store', 'destroy']);
            
        Route::resource('students', StudentController::class)
            ->only(['index', 'store', 'destroy']);
            
        Route::resource('users', UserController::class)
            ->only(['index', 'store', 'destroy']);
            
        Route::resource('schedules', ScheduleController::class)
            ->only(['index', 'create', 'store', 'destroy']);
    });

    Route::middleware('teacher')->group(function () {
        
        Route::get('/my-schedule', [ScheduleController::class, 'teacherSchedule'])
            ->name('teacher.schedule');
            
        Route::get('/journal/group/{group}/subject/{subject}', [JournalController::class, 'grid'])
            ->name('journal.grid');

        Route::post('/journal/group/{group}/subject/{subject}/import', [JournalController::class, 'import'])
            ->name('journal.import');
            
        Route::get('/journal/group/{group}/subject/{subject}/export', [JournalController::class, 'exportTemplate'])
            ->name('journal.export');

        Route::post('/journal/update-grid', [JournalController::class, 'updateGrid'])
            ->name('journal.update-grid');

        Route::get('/journal/{schedule}', [JournalController::class, 'edit'])
            ->name('journal.edit');

        Route::post('/journal/{schedule}', [JournalController::class, 'update'])
            ->name('journal.update');
    });
});