<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    RoomController,
    EquipmentController,
    ReservationController,
    CourseController,
    AssessmentController,
    GradeController,
    StaffProfileController,
    PerformanceRecordController,
    PayrollEntryController,
    AnnouncementController,
    MessageController,
    EventController
};

Route::get('/', function () { return redirect()->route('dashboard'); });

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('rooms', RoomController::class);
    Route::resource('equipment', EquipmentController::class);
    Route::resource('reservations', ReservationController::class);
    Route::resource('courses', CourseController::class);
    Route::resource('assessments', AssessmentController::class);
    Route::resource('grades', GradeController::class);
    Route::resource('staff-profiles', StaffProfileController::class);
    Route::resource('performance-records', PerformanceRecordController::class);
    Route::resource('payroll-entries', PayrollEntryController::class);
    Route::resource('announcements', AnnouncementController::class);
    Route::resource('messages', MessageController::class);
    Route::resource('events', EventController::class);
});

require __DIR__.'/auth.php';
