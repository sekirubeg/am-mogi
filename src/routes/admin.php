<?php

use App\Http\Controllers\Admin\AttendController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminLoginController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'index'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login']);

    Route::middleware('auth:admin')->group(function () {
        Route::get('/attendance/list/{date?}', [AttendController::class, 'index'])->name('attendance.list');
        Route::get('/attendance/detail/{id}', [AttendController::class, 'detail'])->name('attendance.detail');
        Route::post('/attendance/application/{id}', [AttendController::class, 'apply'])->name('attendance.application');
        Route::get('staff/list', [AttendController::class, 'staff'])->name('staff.list');
        Route::get('/attendance/staff/{id}', [AttendController::class, 'show'])->name('staff.attendance');
        Route::get('/attendance/application.list', [AttendController::class, "list"])->name('attendance.application.list');
        Route::get('/attendance/show/{id}',[AttendController::class, "approve"])->name('attendance.show');
        Route::post('/attendance/approve/{id}', [AttendController::class, "update"])->name('attendance.approve');
    });

    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
});
