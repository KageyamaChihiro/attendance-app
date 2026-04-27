<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $attendances = Attendance::where('user_id', $userId)->orderBy('work_date', 'desc')->get();
        $today = now()->toDateString();
        $todayAttendance = Attendance::where('user_id', $userId)->where('work_date', $today)->first();
        return view('attendance.index', compact('attendances', 'todayAttendance'));
    }

    public function clockIn()
    {
        
        $today = now()->toDateString();
        $userId = Auth::id();

        $attendance = Attendance::where('user_id', $userId)->where('work_date', $today)->first();

        if($attendance && $attendance->clock_in){
            return back()->with('error', 'すでに出勤済みです');
        }

        if(!$attendance){
            Attendance::create([
                'user_id' => $userId,
                'work_date' => $today,
                'clock_in' => now(),
            ]);
        } else {
            $attendance->update([
                'clock_in' => now(),
            ]);
        }
        return back()->with('success', '出勤しました');
    }

    public function clockOut()
    {
        $today = now()->toDateString();
        $userId = Auth::id();

        $attendance = Attendance::where('user_id', $userId)->where('work_date', $today)->first();

        if(!$attendance) {
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
        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
        ]);
        return redirect('/attendance')->with('success', '更新しました');
    }
    
}
