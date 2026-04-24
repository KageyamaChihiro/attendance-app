<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use function Symfony\Component\Clock\now;

class AttendanceController extends Controller
{
    public function clockIn()
    {
        $today = date('Y-m-d');
        $attendance = Attendance::where('work_date', $today)->first();

        if($attendance && $attendance->clock_in){
            return back()->with('error', 'すでに出勤済みです');
        }

        if(!$attendance){
            Attendance::create([
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
        $today = date('Y-m-d');
        $attendance = Attendance::where('work_date', $today)->first();

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
        $attendance->update([
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
        ]);
        return redirect('/attendance')->with('success', '更新しました');
    }
    public function index()
    {
        $attendances = [
            ['id' => 1, 'date' => '2026-04-24', 'status' => '出勤'],
            ['id' => 2, 'date' => '2026-04-23', 'status' => '退勤'],
            ];
            return view('attendance.index', compact('attendances'));
    }
}
