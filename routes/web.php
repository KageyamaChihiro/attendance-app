<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/', fn() => redirect('/attendance'));

Route::post('/clock-in', [AttendanceController::class, 'clockIn']);
Route::post('/clock-out', [AttendanceController::class, 'clockOut']);

Route::get('/attendance', [AttendanceController::class, 'index']);
Route::get('/attendance/{id}/edit', [AttendanceController::class, 'edit']);
Route::put('/attendance/{id}', [AttendanceController::class, 'update']);
