<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\AttendanceController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\EventController;
use App\Http\Controllers\Api\Admin\StaffController;
use App\Http\Controllers\Api\Public\RSVPController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — EventHub
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api/v1 via the route service provider.
| Public routes require no authentication.
| Admin routes are protected with auth:sanctum.
|
*/

Route::prefix('v1')->group(function (): void {

    // -------------------------------------------------------------------------
    // Public — RSVP (no auth required)
    // -------------------------------------------------------------------------
    Route::prefix('public')->name('public.')->group(function (): void {
        Route::get('/rsvp/{token}',  [RSVPController::class, 'show'])->name('rsvp.show');
        Route::post('/rsvp/{token}', [RSVPController::class, 'submit'])->name('rsvp.submit');
    });

    // -------------------------------------------------------------------------
    // Auth — Login (no auth required)
    // -------------------------------------------------------------------------
    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });

    // -------------------------------------------------------------------------
    // Admin — Protected (auth:sanctum required)
    // -------------------------------------------------------------------------
    Route::prefix('admin')->name('admin.')->middleware('auth:sanctum')->group(function (): void {

        // Auth — Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Events — full CRUD
        Route::apiResource('events', EventController::class);

        // Staff — master list + invite to event
        Route::get('/staff',         [StaffController::class, 'index'])->name('staff.index');
        Route::post('/staff',        [StaffController::class, 'store'])->name('staff.store');
        Route::post('/staff/invite', [StaffController::class, 'inviteToEvent'])->name('staff.invite');

        // Attendance — QR scan check-in
        Route::post('/attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
    });
});
