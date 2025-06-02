<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance; // Attendanceモデルを使用する場合
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    //
    public function index()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = $user->attendances()->whereDate('clock_in', $today)->first();
        return view('attendance.index', ['attendance' => $attendance]);
    }

    public function start(Request $request)
    {
        // ここで出勤処理を行う
        // 例えば、データベースに出勤時間を保存するなど

        // 成功メッセージをセッションに保存
        Attendance::create([
            'user_id' => Auth::id(),
            'attendance_date' => now()->toDateString(),
            'clock_in' => now(),
        ]);

        // 出勤後の画面にリダイレクト
        return redirect()->route('attendance');
    }

    public function end(Request $request)
    {
        // ここで退勤処理を行う
        // 例えば、データベースに退勤時間を保存するなど

        // 成功メッセージをセッションに保存
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', now()->toDateString())
            ->first();

        if ($attendance) {
            $attendance->clock_out = now();
            $attendance->save();
        }

        // 退勤後の画面にリダイレクト
        return redirect()->route('attendance');
    }
}
