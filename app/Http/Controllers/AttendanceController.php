<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;


class AttendanceController extends Controller
{
    public function index()
    {
        $userId = 1;
        $attendances = Attendance::where('user_id', $userId)->orderBy('work_date', 'desc')->get();
        $today = now()->toDateString();
        $todayAttendance = Attendance::where('user_id', $userId)->where('work_date', $today)->first();
        return view('attendance.index', compact('attendances', 'todayAttendance'));
    }

    public function clockIn()
    {
        $today = now()->toDateString();
        $userId = 1;

        $attendance = Attendance::where('user_id', $userId)->where('work_date', $today)->first();

        if($attendance && $attendance->clock_in){
            return back()->with('error', 'すでに出勤済みです');
        }
        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $userId,
                'work_date' => $today,
            ],
            [
                'clock_in' => now(),
            ]);
        return back()->with('success', '出勤しました');
    }

    public function clockOut()
    {
        $today = now()->toDateString();
        $userId = 1;

        $attendance = Attendance::where('user_id', $userId)->where('work_date', $today)->first();

        if(!$attendance || !$attendance->clock_in) {
            return back()->with('error', '退勤データがありません');
        }

        if($attendance->clock_out) {
            return back()->with('error', 'すでに退勤済みです');
        }

        $attendance->update([
            'clock_out' => now(),
        ]);
        return back()->with('success', '退勤しました');
    }
    public function edit($id)
    {
        $attendance = Attendance::findOrFail($id);
        return view('attendance.edit', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::where('id', $id)->where('user_id', 1)->firstOrFail();
        $request->validate([
            'clock_in' => 'nullable|date',
            'clock_out' => 'nullable|date|after:clock_in',
        ]);
        return redirect()->route('attendance.index');
    }
}
