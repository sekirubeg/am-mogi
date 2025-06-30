<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $attendances = [
            [
                'user_id' => 1,
                'attendance_date' => '2025-06-27',
                'clock_in' => '2025-06-27 09:00:00',
                'clock_out' => '2025-06-27 18:00:00',
            ],
            [
                'user_id' => 1,
                'attendance_date' => '2025-06-28',
                'clock_in' => '2025-06-28 09:15:00',
                'clock_out' => '2025-06-28 18:15:00',
            ],
            [
                'user_id' => 2,
                'attendance_date' => '2025-06-27',
                'clock_in' => '2025-06-27 09:05:00',
                'clock_out' => '2025-06-27 18:05:00',
            ],
            [
                'user_id' => 2,
                'attendance_date' => '2025-06-28',
                'clock_in' => '2025-06-28 09:20:00',
                'clock_out' => '2025-06-28 18:20:00',
            ],
            [
                'user_id' => 3,
                'attendance_date' => '2025-06-27',
                'clock_in' => '2025-06-27 09:10:00',
                'clock_out' => '2025-06-27 18:10:00',
            ],
            [
                'user_id' => 3,
                'attendance_date' => '2025-06-28',
                'clock_in' => '2025-06-28 09:25:00',
                'clock_out' => '2025-06-28 18:25:00',
            ],
        ];
        foreach ($attendances as $attendance) {
            $attendanceModel =  Attendance::create($attendance);
            AttendanceBreak::create([
                'attendance_id' => $attendanceModel->id,
                'break_start' => $attendance['attendance_date'] . ' 12:00:00',
                'break_end' => $attendance['attendance_date'] . ' 13:00:00',
            ]);
        }
        $userIds = [1, 2, 3];
        if (User::whereIn('id', $userIds)->count() !== count($userIds)) {
            // ユーザーが存在しない場合の処理。例えばUserSeederをここで呼び出すなど。
            // 今回はDatabaseSeederでUserSeederが先に呼ばれる前提とします。
            $this->command->warn('ユーザーID 1, 2, 3 が存在しません。UserSeederが先に実行されているか確認してください。');
            return;
        }

        // 2025年5月の開始日と終了日を設定
        $startDate = Carbon::parse('2025-05-01');
        $endDate = Carbon::parse('2025-05-31');

        // 各ユーザーと各日付についてループ
        foreach ($userIds as $userId) {
            $currentDate = $startDate->copy(); // 各ユーザーごとに開始日をリセット

            while ($currentDate->lte($endDate)) {
                $attendanceDate = $currentDate->format('Y-m-d');
                $clockIn = $currentDate->format('Y-m-d') . ' 09:00:00'; // 固定の出勤時間
                $clockOut = $currentDate->format('Y-m-d') . ' 18:00:00'; // 固定の退勤時間

                // 勤怠レコードを作成
                $attendanceModel = Attendance::create([
                    'user_id' => $userId,
                    'attendance_date' => $attendanceDate,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                ]);

                // 休憩レコードを作成 (12:00-13:00)
                AttendanceBreak::create([
                    'attendance_id' => $attendanceModel->id,
                    'break_start' => $attendanceDate . ' 12:00:00',
                    'break_end' => $attendanceDate . ' 13:00:00',
                ]);

                $currentDate->addDay(); // 次の日へ進む
            }
        }
    }
}
