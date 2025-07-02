<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Admin; // Adminモデルを使用しているため追加
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function testAttendanceClockOut()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now()->subHours(8)->format('Y-m-d H:i:s'), // 8時間前に出勤
            'clock_out' => null,
        ]);
        $response = $this->get('/attendance'); // 勤怠打刻画面のURL
        $response->assertStatus(200);
        $response->assertSee('<button type="submit" class="btn btn-dark">退 勤</button>', false);
        // 退勤処理を実行
        $response = $this->actingAs($user)->post(route('attendance.end'));
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    public function testConfirmedClockOut()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance'); // 勤怠打刻画面のURL
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post(route('attendance.start'));

        // 4. 一般ユーザーが退勤処理を行う
        // 退勤時刻を正確に記録しておく
        $clockOutMoment = Carbon::now(); // 現在時刻を退勤時刻とする
        Carbon::setTestNow($clockOutMoment); // テスト中の現在時刻を退勤時刻に固定

        $response = $this->get('/attendance'); // 勤怠打刻画面のURL
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post(route('attendance.end'));
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'attendance_date' => $clockOutMoment->format('Y-m-d'),
            'clock_out' => $clockOutMoment->format('Y-m-d H:i:s'), // 退勤時刻が設定されている
        ]);

        $admin = Admin::factory()->create(['name' => 'テスト管理者', 'email' => 'admin@example.com', 'password' => 'password']);
        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $expectedDisplayedDate = $clockOutMoment->format('Y年m月d日');
        $expectedDisplayedTime = $clockOutMoment->format('H:i');
        $response->assertSeeText($user->name); // ユーザー名が表示されているか
        $response->assertSeeText($expectedDisplayedDate); // 退勤日付が表示されているか
        $response->assertSeeText($expectedDisplayedTime); // 退勤時刻が表示されているか

    }
}
