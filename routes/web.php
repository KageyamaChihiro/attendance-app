<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/', fn() => redirect('/attendance'));

Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('clock.in');
Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('clock.out');

Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::get('/attendance/{id}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
