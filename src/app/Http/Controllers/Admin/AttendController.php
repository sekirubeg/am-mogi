<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;
use App\Models\AttendanceRequest as AttendanceRequestModel;
use App\Models\AttendanceRequestBreak;
use App\Models\AttendanceBreak;


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

    public function staff(){
        $staff = User::get();
        return view('admin.staff.index', compact('staff'));
    }

    public function show(Request $request, $id){
        $user = User::where('id', $id)->first();
        $month = $request->query('month', now()->format('Y-m'));

        $attendances = Attendance::where('user_id', $id)
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

        return view('admin.staff.attendance', [
            'user' => $user,
            'datesInMonth' => $datesInMonth,
            'attendancesByDate' => $attendancesByDate,
            'month' => $month,
            'prevMonth' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m'),
            'nextMonth' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m'),
        ]);
    }
    public function list(Request $request){
        $status = $request->query('review_status'); // approved or pending

        $query = AttendanceRequestModel::query();

        if ($status == 1) {
            $query->where('review_status', 1); // 承認済み
        } else {
            $query->where('review_status', 0); // 承認待ち
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return view('admin.requests.index', compact('requests', 'status'));
    }

    public function approve($id){
        $attendance = Attendance::findOrFail($id);
        $attendanceRequest = $attendance->request; // リレーションがある前提

        $attendanceRequestBreaks = collect();
        if ($attendanceRequest) {
            $attendanceRequestBreaks = AttendanceRequestBreak::where('attendance_request_id', $attendanceRequest->id)->get();
        }

        $date = Carbon::parse($attendance->attendance_date);
        $formatted = [
            'year' => $date->year,
            'month_day' => $date->format('n月j日')
        ];
        return view('admin.requests.show', compact('attendance', 'formatted', 'date', 'attendanceRequest', 'attendanceRequestBreaks'));
    }
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $date = Carbon::parse($attendance->attendance_date);
        // 勤怠情報の更新
        $attendance->clock_in = $date->format('Y-m-d') . ' ' . $request->clock_in;
        $attendance->clock_out = $date->format('Y-m-d') . ' ' . $request->clock_out;
        $attendance->remarks = $request->remarks;
        $attendance->save();

        $attendanceRequest = AttendanceRequestModel::where('attendance_id', $id)->first();

        $attendanceRequest->admin_id = auth()->id();
        $attendanceRequest->review_status = 1;
        $attendanceRequest->reviewed_at = now();
        $attendanceRequest->save();

        // 既存の休憩を削除して再登録（必要に応じて）
        AttendanceBreak::where('attendance_id', $attendance->id)->delete();

        if ($request->has('breaks')) {
            foreach ($request->breaks as $break) {
                AttendanceBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $date->format('Y-m-d') . ' ' .  $break['break_start'],
                    'break_end' => $date->format('Y-m-d') . ' ' . $break['break_end'],
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => '承認完了'
        ]);
    }

    public function exportCsv(Request $request){
        $user = User::findOrFail($request->user_id);
        $requestmonth = $request->month; // '2025-06'
        $month = Carbon::createFromFormat('Y-m', $requestmonth);
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();
        // その月の全出勤データを取得（リレーション付きで）
        $attendances = Attendance::with('attendance_breaks')
            ->where('user_id', $user->id)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy('attendance_date');
        $datesInMonth = collect();
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $datesInMonth->push($date->copy());
        }

        $csvHeader = ['日付', '出勤', '退勤', '休憩', '実働'];
        $fileName = "{$user->name}さん_{$month->format('Y年m月')}_勤怠一覧.csv";
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
        ];
        $callback = function () use ($datesInMonth, $attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '実働']);

            foreach ($datesInMonth as $date) {
                $a = $attendances[$date->toDateString()] ?? null;

                if ($a && $a->clock_in && $a->clock_out) {
                    // 総休憩時間（秒数）
                    $totalBreakSeconds = $a->attendance_breaks->sum(function ($break) {
                        return $break->break_end && $break->break_start
                            ? Carbon::parse($break->break_end)->diffInSeconds($break->break_start)
                            : 0;
                    });

                    $workSeconds = Carbon::parse($a->clock_out)->diffInSeconds($a->clock_in) - $totalBreakSeconds;

                    fputcsv($handle, [
                        $date->locale('ja')->isoFormat('YYYY/MM/DD (dd)'),
                        Carbon::parse($a->clock_in)->format('H:i'),
                        Carbon::parse($a->clock_out)->format('H:i'),
                        gmdate('H:i', $totalBreakSeconds),
                        gmdate('H:i', $workSeconds),
                    ]);
                } else {
                    // 勤怠なしの場合
                    fputcsv($handle, [
                        $date->locale('ja')->isoFormat('YYYY/MM/DD (dd)'),
                        '',
                        '',
                        '',
                        '',
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
