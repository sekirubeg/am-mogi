<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AdminAttendanceDetailConfirmTest extends TestCase
{
    use RefreshDatabase;


    //勤怠詳細画面に表示されるデータが選択したものになっている
    public function testAdminCanConfirmAttendanceDetail()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        $this->actingAs($admin, 'admin');
        $user = User::factory()->create(['name' => 'テストユーザー']);
        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-07-01',
            'clock_in' => '2025-07-01 09:00:00',
            'clock_out' => '2025-07-01 18:00:00',
        ]);

        // 勤怠詳細ページへアクセス
        $response = $this->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        // ステータス確認
        $response->assertStatus(200);

        // 勤怠情報の内容が表示されていることを確認
        $response->assertSee('テストユーザー');
        $response->assertSee('2025年');
        $response->assertSee('7月1日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    //出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testAdminCanConfirmAttendanceDetailWithBreaks()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        $this->actingAs($admin, 'admin');
        $user = User::factory()->create(['name' => 'テストユーザー']);
        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-07-01',
            'clock_in' => '2025-07-01 09:00:00',
            'clock_out' => '2025-07-01 18:00:00',
        ]);
        // 休憩データ作成
        $attendance->attendance_breaks()->create([
            'break_start' => '2025-07-01 12:00:00',
            'break_end' => '2025-07-01 12:30:00',
        ]);

        // 無効な入力（出勤 > 退勤）を送信
        $response = $this->from(route('admin.attendance.detail', ['id' => $attendance->id]))
            ->post(route('admin.attendance.application', ['id' => $attendance->id]), [
                'clock_in' => '18:00',
                'clock_out' => '17:00',
                'remarks' => 'テストの備考',
            ]);

        // エラーがあって元のページにリダイレクトされる
        $response->assertRedirect(route('admin.attendance.detail', ['id' => $attendance->id]));

        // リダイレクト後のページを再取得してエラーメッセージを検証
        $followUp = $this->get(route('admin.attendance.detail', ['id' => $attendance->id]));
        $followUp->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    //一旦リセット
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }
    //休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testAdminCanConfirmAttendanceDetailWithInvalidBreaks()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        $this->actingAs($admin, 'admin');
        $user = User::factory()->create(['name' => 'テストユーザー']);
        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-07-01',
            'clock_in' => '2025-07-01 09:00:00',
            'clock_out' => '2025-07-01 18:00:00',
        ]);
        // 休憩データ作成
        $attendance->attendance_breaks()->create([
            'break_start' => '2025-07-01 12:00:00',
            'break_end' => '2025-07-01 12:30:00',
        ]);
        // 無効な入力（休憩開始時間が退勤時間より後）を送信
        $response = $this->from(route('admin.attendance.detail', ['id' => $attendance->id]))
            ->post(route('admin.attendance.application', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
            'breaks' => [
                1 => [
                    'break_start' => '20:00',
                    'break_end' => '21:00',
                ],
            ],
                'remarks' => 'テストの備考',
            ]);
        // エラーがあって元のページにリダイレクトされる
        $response->assertRedirect(route('admin.attendance.detail', ['id' => $attendance->id]));
        // リダイレクト後のページを再取得してエラーメッセージを検証
        $followUp = $this->get(route('admin.attendance.detail', ['id' => $attendance->id]));
        $followUp->assertSee('休憩時間が不適切な値です');
    }

    //休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testAdminCanConfirmAttendanceDetailWithInvalidBreakEnd()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        $this->actingAs($admin, 'admin');
        $user = User::factory()->create(['name' => 'テストユーザー']);
        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-07-01',
            'clock_in' => '2025-07-01 09:00:00',
            'clock_out' => '2025-07-01 18:00:00',
        ]);
        // 休憩データ作成
        $attendance->attendance_breaks()->create([
            'break_start' => '2025-07-01 12:00:00',
            'break_end' => '2025-07-01 12:30:00',
        ]);
        // 無効な入力（休憩開始時間が退勤時間より後）を送信
        $response = $this->from(route('admin.attendance.detail', ['id' => $attendance->id]))
            ->post(route('admin.attendance.application', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    1 => [
                        'break_start' => '17:00',
                        'break_end' => '21:00',
                    ],
                ],
                'remarks' => 'テストの備考',
            ]);
        // エラーがあって元のページにリダイレクトされる
        $response->assertRedirect(route('admin.attendance.detail', ['id' => $attendance->id]));
        // リダイレクト後のページを再取得してエラーメッセージを検証
        $followUp = $this->get(route('admin.attendance.detail', ['id' => $attendance->id]));
        $followUp->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    //備考欄が未入力の場合のエラーメッセージが表示される
    public function testAdminCanConfirmAttendanceDetailWithInvalidRemarks()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        $this->actingAs($admin, 'admin');
        $user = User::factory()->create(['name' => 'テストユーザー']);
        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-07-01',
            'clock_in' => '2025-07-01 09:00:00',
            'clock_out' => '2025-07-01 18:00:00',
        ]);
        // 休憩データ作成
        $attendance->attendance_breaks()->create([
            'break_start' => '2025-07-01 12:00:00',
            'break_end' => '2025-07-01 12:30:00',
        ]);
        // 無効な入力（休憩開始時間が退勤時間より後）を送信
        $response = $this->from(route('admin.attendance.detail', ['id' => $attendance->id]))
            ->post(route('admin.attendance.application', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    1 => [
                        'break_start' => '17:00',
                        'break_end' => '21:00',
                    ],
                ],
            ]);
        // エラーがあって元のページにリダイレクトされる
        $response->assertRedirect(route('admin.attendance.detail', ['id' => $attendance->id]));
        // リダイレクト後のページを再取得してエラーメッセージを検証
        $followUp = $this->get(route('admin.attendance.detail', ['id' => $attendance->id]));
        $followUp->assertSee('備考を記入してください');
    }
}
