<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceIndexTest extends TestCase
{
    use RefreshDatabase; // 各テストメソッドの前にデータベースをリフレッシュ

    public function testAllAttendanceRecordsAreDisplayedForLoggedInUser()
    {
        // 1. 勤怠情報が複数登録されたユーザーを作成し、ログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        // テスト用の複数勤怠データを作成（ファクトリを使用）
        // 過去の特定の日付のデータを生成するため、setTestNowで時間を一時的に固定し、
        // 複数日にわたる勤怠を作成します。

        // 1日目: 2025-07-25 の勤怠
        Carbon::setTestNow(Carbon::parse('2025-07-25 09:00:00'));
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-07-25',
            'clock_in' => '2025-07-25 09:00:00',
            'clock_out' => '2025-07-25 18:00:00',
        ]);

        // 2日目: 2025-07-26 の勤怠
        Carbon::setTestNow(Carbon::parse('2025-07-26 09:00:00'));
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-07-26',
            'clock_in' => '2025-07-26 09:00:00',
            'clock_out' => '2025-07-26 18:00:00',
        ]);

        // 3日目: 2025-07-27 の勤怠
        Carbon::setTestNow(Carbon::parse('2027-07-27 09:00:00'));
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-07-27',
            'clock_in' => '2025-07-27 09:00:00',
            'clock_out' => '2025-07-27 18:00:00',
        ]);


        Carbon::setTestNow(null);
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSeeText('07/25');
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');

        $response->assertSeeText('07/26');
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');

        $response->assertSeeText('07/27'); // 3日目の日付
        $response->assertSeeText('09:00'); // 3日目の出勤時刻
        $response->assertSeeText('18:00'); // 3日目の退勤時刻
    }

    public function testAttendanceListMonth()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Carbon::setTestNow(Carbon::parse('2025-07-01 10:00:00'));
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $currentMonth = Carbon::now()->format('Y年m月');
        $response->assertSeeText($currentMonth);

        Carbon::setTestNow(null);
    }
    public function testAttendanceListPreMonth()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Carbon::setTestNow(Carbon::parse('2025-07-01 10:00:00'));
        $response = $this->get(route('attendance.list', ['month' => '2025-06']));
        $response->assertStatus(200);
        $previousMonth = Carbon::parse('2025-06-01')->format('Y年m月');
        $response->assertSeeText($previousMonth);

        Carbon::setTestNow(null);
    }
    public function testAttendanceListNextMonth()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Carbon::setTestNow(Carbon::parse('2025-07-01 10:00:00'));
        $response = $this->get(route('attendance.list', ['month' => '2025-08']));
        $response->assertStatus(200);
        $nextMonth = Carbon::parse('2025-08-01')->format('Y年m月');
        $response->assertSeeText($nextMonth);

        Carbon::setTestNow(null);
    }
    public function testAttendanceDetailConfirm()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // テスト用の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-07-25',
            'clock_in' => '2025-07-25 09:00:00',
            'clock_out' => '2025-07-25 18:00:00',
        ]);


        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $month = Carbon::parse($attendance->attendance_date)->format('n月d日');
        $response->assertSeeText($month);
        $response->assertSee('<input type="text" value="09:00" name="clock_in" class="start">', false);
        $response->assertSee('<input type="text" value="18:00" name="clock_out" class="end">', false);
    }
}
