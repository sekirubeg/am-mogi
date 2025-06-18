<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance; // Attendanceモデルを使用する場合
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use function PHPSTORM_META\map;

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

    public function startBreak(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', now()->toDateString())
            ->first();
        $attendance->attendance_breaks()->create(
            [
                'break_start' => now(),
            ]
        );
        return redirect()->route('attendance');
    }

    public function endBreak(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', now()->toDateString())
            ->first();
        if ($attendance) {
            $break = $attendance->attendance_breaks()->whereNull('break_end')->latest()->first();
            if ($break) {
                $break->update(['break_end' => now()]);
            }
        }

        return redirect()->route('attendance');
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $month = $request->query('month', now()->format('Y-m'));

        $attendances = Attendance::where('user_id', $user->id)
            ->where('attendance_date', 'like', $month . '%')
            ->orderBy('attendance_date')
            ->get();


        // 1日〜末日までの日付を生成
        $startOfMonth = \Carbon\Carbon::parse($month)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::parse($month)->endOfMonth();

        $datesInMonth = collect();
        $cursor = $startOfMonth->copy();
        while ($cursor->lte($endOfMonth)) {
            $datesInMonth->push($cursor->copy());
            $cursor->addDay();
        }

        // 出勤データを日付ごとに整理（連想配列に変換）
        $attendancesByDate = $attendances->keyBy('attendance_date');

        return view('attendance.list', [
            'datesInMonth' => $datesInMonth,
            'attendancesByDate' => $attendancesByDate,
            'month' => $month,
            'prevMonth' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m'),
            'nextMonth' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m'),
        ]);
    }
    public function detail($id)
    {
        $attendance = Attendance::findOrFail($id);

        $date = Carbon::parse($attendance->attendance_date);
        $formatted = [
            'year' => $date->year,                // 例：2023
            'month_day' => $date->format('n月j日') // 例：6月1日
        ];
        $date->format('y-m-d');
        return view('attendance.detail', compact('attendance', 'formatted', 'date'));
    }
    public function apply(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $date = $request->input('date'); // ← OK
        $date = Carbon::parse($attendance->attendance_date);


        $attendanceRequest = $attendance->request()->updateOrCreate(
            [],
            [
                'requested_clock_in' => $date->format('y-m-d') . ' ' . $request->input('clock_in'),
                'requested_clock_out' => $date->format('y-m-d') . ' ' . $request->input('clock_out'),
                'remarks' => $request->input('remarks'),
                'requested_by' => auth()->id(),
            ]
        );

        $attendanceRequest->breaks()->delete();

        $existingBreaks = $request->input('breaks', []);
        foreach ($existingBreaks as $breakData) {
            if (!empty($breakData['break_start']) || !empty($breakData['break_end'])) {
                $attendanceRequest->breaks()->create([
                    'break_start' => $date->format('y-m-d') . ' ' . $breakData['break_start'] ?? null,
                    'break_end' => $date->format('y-m-d') . ' ' . $breakData['break_end'] ?? null,
                ]);
            }
        }
        return redirect()->route('attendance.detail', ['id' => $id]);
    }
}
