<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;
    public function testAttendanceStatusIsOutsideWorkingHours()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get('/attendance/');
        $response->assertStatus(200);
        $response->assertSeeText('勤務外');
    }
    public function testAttendanceStatusIsWorkingHours()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now()->subHours(1)->format('Y-m-d H:i:s'), // 1時間前に出勤
            'clock_out' => null, // 退勤はまだ
        ]);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('出勤中');
    }
    public function testAttendanceStatusIsBreakingHours()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now()->subHours(1)->format('Y-m-d H:i:s'), // 1時間前に出勤
            'clock_out' => null, // 退勤はまだ
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id, // 勤怠IDに紐付け
            'break_start' => Carbon::now()->subMinutes(30)->format('Y-m-d H:i:s'), // 30分前に休憩開始
            'break_end' => null, // 休憩はまだ終わっていない状態
        ]);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩中');
    }
    public function testAttendanceStatusIsNotWorkingHours()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now()->subHours(1)->format('Y-m-d H:i:s'), // 1時間前に出勤
            'clock_out' => Carbon::now()->subMinutes(30)->format('Y-m-d H:i:s'), // 30分前に退勤
        ]);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('退勤済');
    }
}
