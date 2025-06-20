<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;

use App\Models\AttendanceRequestBreak;


class AttendController extends Controller
{
    //
    public function index(Request $request, $date = null){
        $date = $date ?? Carbon::today()->toDateString();
        $targetDate = Carbon::parse($date);

        $attendances = Attendance::with(['user', 'attendance_breaks'])
            ->whereDate('attendance_date', $targetDate)
            ->get()
            ->groupBy(function ($a) {
                return $a->attendance_date;
            });

        $prevDay = $targetDate->copy()->subDay();
        $nextDay = $targetDate->copy()->addDay();

        return view('admin/attendance/index', compact('attendances', 'prevDay', 'nextDay', 'targetDate'));
    }

    public function detail($id){
        $attendance = Attendance::findOrFail($id);
        $attendanceRequestBreaks = AttendanceRequestBreak::where('attendance_request_id', $attendance->id)->get();
        $attendanceRequest = $attendance->request; // リレーションがある前提
        $date = Carbon::parse($attendance->attendance_date);
        $formatted = [
            'year' => $date->year,
            'month_day' => $date->format('n月j日')
        ];
        return view('admin.attendance.detail', compact('attendance', 'formatted', 'date', 'attendanceRequest', 'attendanceRequestBreaks'));
    }

    public function apply(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $date = Carbon::parse($attendance->attendance_date);

        // 勤怠の出勤・退勤を直接更新
        $attendance->update([
            'clock_in' => $date->format('Y-m-d') . ' ' . $request->input('clock_in'),
            'clock_out' => $date->format('Y-m-d') . ' ' . $request->input('clock_out'),
            'remarks' => $request->input('remarks'),
        ]);

        // 既存の休憩を削除
        $attendance->attendance_breaks()->delete();

        // 入力された休憩を保存
        $breaks = $request->input('breaks', []);
        foreach ($breaks as $break) {
            if (!empty($break['break_start']) || !empty($break['break_end'])) {
                $attendance->attendance_breaks()->create([
                    'break_start' => $date->format('Y-m-d') . ' ' . ($break['break_start'] ?? null),
                    'break_end' => $date->format('Y-m-d') . ' ' . ($break['break_end'] ?? null),
                ]);
            }
        }

        return redirect()->route('admin.attendance.detail', ['id' => $id])
            ->with('message', '勤怠を更新しました');
    }

}
