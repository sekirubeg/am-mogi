<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminLoginController;


Route::get('/', function () {
    return view('welcome');
});

// 認証メール確認画面
Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

// 認証メールのリンククリック時（自動処理）
Route::get(
    '/email/verify/{id}/{hash}',
    [\App\Http\Controllers\Auth\VerificationController::class, 'verify']
)->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メール再送信
Route::post(
    '/email/verification-notification',
    [\App\Http\Controllers\Auth\VerificationController::class, 'resend']
)->middleware(['auth', 'throttle:6,1'])->name('verification.send');


Route::get('/register', [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');
    Route::post('/attendance/break/start', [AttendanceController::class, 'startBreak'])->name('attendance.break.start');
    Route::post('/attendance/break/end', [AttendanceController::class, 'endBreak'])->name('attendance.break.end');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/application/{id}', [AttendanceController::class, 'apply'])->name('attendance.application');
    Route::get('/attendance/application/list', [AttendanceController::class, 'applicationList'])->name('attendance.application.list');
});

require __DIR__.'/admin.php';
