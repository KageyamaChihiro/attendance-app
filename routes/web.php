<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/', [AttendanceController::class, 'index']);

Route::post('/clock-in', [AttendanceController::class, 'clockIn']);
Route::post('/clock-out', [AttendanceController::class, 'clockOut']);

Route::get('/attendance', [AttendanceController::class, 'index']);
Route::get('/attendance/edit/{id}', [AttendanceController::class, 'edit']);
Route::post('/attendance/update/{id}', [AttendanceController::class, 'update']);
