<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    public function testAttendanceDetailPageName()
    {
        // 1. 勤怠情報が登録されたユーザーを作成し、ログインする
        // 2. 勤怠詳細ページにアクセスし、正しいデータが表示されることを確認する
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
    $userName = $user->name;
    $response->assertSee($userName);
    }
    public function testAttendanceDetailPageDate()
    {
        // 1. 勤怠情報が登録されたユーザーを作成し、ログインする
        // 2. 勤怠詳細ページにアクセスし、正しいデータが表示されることを確認する
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendanceDate = '2025-07-25'; // テスト用の日付
        // テスト用の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => $attendanceDate,
            'clock_in' => '2025-07-25 09:00:00',
            'clock_out' => '2025-07-25 18:00:00',
        ]);


        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        // Carbon を使って日付を 'Y年n月j日' 形式にフォーマットします
        $formattedYear = Carbon::parse($attendanceDate)->format('Y年');
        $formattedMonthDay = Carbon::parse($attendanceDate)->format('n月j日');

        $response->assertSee($formattedYear);
        $response->assertSee($formattedMonthDay);
    }
    public function testAttendanceDetailPageMatchClockInAndClockOut()
    {
        // 1. 勤怠情報が登録されたユーザーを作成し、ログインする
        // 2. 勤怠詳細ページにアクセスし、正しいデータが表示されることを確認する
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

        $clockInTime = Carbon::parse($attendance->clock_in)->format('H:i');
        $clockOutTime = Carbon::parse($attendance->clock_out)->format('H:i');
        $expectedClockInInput = '<input type="text" value="' . $clockInTime . '" name="clock_in" class="start">';
        $expectedClockOutInput = '<input type="text" value="' . $clockOutTime . '" name="clock_out" class="end">';

        $html = $response->getContent();

        $this->assertStringContainsString($expectedClockInInput, $html);
        $this->assertStringContainsString($expectedClockOutInput, $html);
    }
    public function testAttendanceDetailPageMatchBreaks()
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

        // 休憩時間を複数追加
        $breaks = [
            ['break_start' => '2025-07-25 12:00:00', 'break_end' => '2025-07-25 12:30:00'],
            ['break_start' => '2025-07-25 15:00:00', 'break_end' => '2025-07-25 15:15:00'],
        ];

        foreach ($breaks as $break) {
            $attendance->attendance_breaks()->create($break);
        }

        // 3. 勤怠詳細ページにアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $html = $response->getContent();

        // 全ての休憩時間が表示されていることを確認
        foreach ($attendance->attendance_breaks as $index => $break) {
            $breakInTime = Carbon::parse($break->break_start)->format('H:i');
            $breakOutTime = Carbon::parse($break->break_end)->format('H:i');

            // インデックスは1から開始する仮定（blade側がそうなら）
            $inputIndex = $index + 1;

            $expectedBreakInInput = '<input type="text" value="' . $breakInTime . '" name="breaks[' . $inputIndex . '][break_start]" class="start">';
            $expectedBreakOutInput = '<input type="text" value="' . $breakOutTime . '" name="breaks[' . $inputIndex . '][break_end]" class="end">';

            $this->assertStringContainsString($expectedBreakInInput, $html);
            $this->assertStringContainsString($expectedBreakOutInput, $html);
        }
    }
}
