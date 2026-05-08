<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->month ? \Carbon\Carbon::parse($request->month): now();
        $attendances = Attendance::whereYear('work_date', $month->year)
            ->wheremonth('work_date', $month->month)
            ->orderBy('work_date', 'desc')
            ->get();

        $today = now()->toDateString();

        $todayAttendance = Attendance::where('work_date', $today)->first();

        $totalMinutes = $attendances->sum(function ($a) {
            if($a->clock_in && $a->clock_out) {
                $work = \Carbon\Carbon::parse($a->clock_in)->diffInMinutes(\Carbon\Carbon::parse($a->clock_out));
                return $work - ($a->break_time ?? 0);
            }
            return 0;
        });
        return view('attendance.index', compact(
            'attendances',
            'todayAttendance',
            'totalMinutes',
            'month'
            ));
    }

    public function clockIn()
    {
        $today = now()->toDateString();
        $attendance = Attendance::where('work_date', $today)->first();

        if($attendance && $attendance->clock_in){
            return back()->with('error', 'すでに出勤済みです');
        }
        $attendance = Attendance::updateOrCreate(
            [
                'work_date' => $today,
            ],
            [
                'clock_in' => now(),
                'scheduled_start' => '09:00',
                'scheduled_end' => '18:00',
            ]);
        return back()->with('success', '出勤しました');
    }

    public function clockOut()
    {
        $today = now()->toDateString();
        $attendance = Attendance::where('work_date', $today)->first();

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
        $attendance = Attendance::findOrFail($id);
        $request->validate([
            'clock_in' => 'nullable|date',
            'clock_out' => 'nullable|date|after:clock_in',
        ]);
        $attendance->update([
            'work_date' => $request->work_date,
            'clock_in' => $request->clock_in ? Carbon::parse($request->clock_in)->format('H:i:s'): null,
            'clock_out' => $request->clock_out ? Carbon::parse($request->clock_out)->format('H:i:s'): null,
            'work_type' => $request->work_type,
            'scheduled_start' => $request->scheduled_start,
            'scheduled_end' => $request->scheduled_end,
        ]);
        return redirect()->route('attendance.index')->with('success', '更新しました');
    }
    public function updateBreak(Request $request)
    {
        $request->validate(['break_time' => 'required|integer|min:0|max:180',]);

        $today = date('Y-m-d');
        $attendance = Attendance::where('work_date', $today)->first();

        if(!$attendance) {
            return back()->with('error', 'データがありません');
        }
        $attendance->break_time = $request->break_time;
        $attendance->save();
        return back()->with('success', '休憩時間を更新しました');
    }
    public function setDefaultTime()
    {
        $today = Carbon::today()->toDatestring();
        $attendance = Attendance::firstOrCreate(
            [
                'work_date' => $today
            ]
        );
        $attendance->scheduled_start = '09:00';
        $attendance->scheduled_end = '18:00';

        $attendance->save();

        return redirect()->back();
    }


}
