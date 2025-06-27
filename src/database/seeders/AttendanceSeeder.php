<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

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
    }
}
