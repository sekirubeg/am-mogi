<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminLoginController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'index'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login']);

    Route::middleware('auth:admin')->group(function () {
        Route::get('/admin/attendance/list', function () {
            return view('admin.attendance.index');
        })->name('attendance.list');
    });

    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
});
