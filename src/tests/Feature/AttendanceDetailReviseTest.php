<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Admin;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceDetailReviseTest extends TestCase
{
    use RefreshDatabase;

    public function testAttendanceErrors(){
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-06-25',
            'clock_in' => '2025-06-25 09:00:00',
            'clock_out' => '2025-06-25 18:00:00',
        ]);

        // 無効な入力（出勤 > 退勤）を送信
        $response = $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.application', ['id' => $attendance->id]), [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'remarks' => 'テストの備考',
            ]);

        // エラーがあって元のページにリダイレクトされる
        $response->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));

        // リダイレクト後のページを再取得してエラーメッセージを検証
        $followUp = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $followUp->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }


    public function testBreakStartErrors()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-06-25',
            'clock_in' => '2025-06-25 09:00:00',
            'clock_out' => '2025-06-25 18:00:00',
        ]);

        // 休憩時間を複数追加
        $breaks = [
            ['break_start' => '2025-06-25 12:00:00', 'break_end' => '2025-06-25 12:30:00'],
            ['break_start' => '2025-06-25 15:00:00', 'break_end' => '2025-06-25 15:15:00'],
        ];

        foreach ($breaks as $break) {
            $attendance->attendance_breaks()->create($break);
        }

        // 無効な入力（出勤 > 退勤）を送信
        $response = $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.application', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    1 => [
                        'break_start' => '19:00',
                        'break_end' => '18:00',
                    ],
                ],
                'remark' => 'テストの備考',
            ]);

        // エラーがあって元のページにリダイレクトされる
        $response->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));

        // リダイレクト後のページを再取得してエラーメッセージを検証
        $followUp = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $followUp->assertSee('休憩時間が不適切な値です');
    }

    //一旦リセット
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }


    public function testBreakEndErrors()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-06-25',
            'clock_in' => '2025-06-25 09:00:00',
            'clock_out' => '2025-06-25 18:00:00',
        ]);
        // 休憩時間を複数追加
        $breaks = [
            ['break_start' => '2025-06-25 12:00:00', 'break_end' => '2025-06-25 12:30:00'],
            ['break_start' => '2025-06-25 15:00:00', 'break_end' => '2025-06-25 15:15:00'],
        ];

        foreach ($breaks as $break) {
            $attendance->attendance_breaks()->create($break);
        }

        // 無効な入力（出勤 > 退勤）を送信
        $response = $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.application', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    1 => [
                        'break_start' => '19:00',
                        'break_end' => '21:00',
                    ],
                ],
                'remark' => 'テストの備考',
            ]);

        // エラーがあって元のページにリダイレクトされる
        $response->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));

        // リダイレクト後のページを再取得してエラーメッセージを検証
        $followUp = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $followUp->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }


    public function testRemarkErrors()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-06-25',
            'clock_in' => '2025-06-25 09:00:00',
            'clock_out' => '2025-06-25 18:00:00',
        ]);
        // 休憩時間を複数追加
        $breaks = [
            ['break_start' => '2025-06-25 12:00:00', 'break_end' => '2025-06-25 12:30:00'],
            ['break_start' => '2025-06-25 15:00:00', 'break_end' => '2025-06-25 15:15:00'],
        ];

        foreach ($breaks as $break) {
            $attendance->attendance_breaks()->create($break);
        }

        // 無効な入力（出勤 > 退勤）を送信
        $response = $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.application', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    1 => [
                        'break_start' => '11:00',
                        'break_end' => '12:00',
                    ],
                ],
            ]);

        // エラーがあって元のページにリダイレクトされる
        $response->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));

        // リダイレクト後のページを再取得してエラーメッセージを検証
        $followUp = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $followUp->assertSee('備考を記入してください');
    }


    //修正申請処理が実行される
    public function testAttendanceDetailRevise()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-06-25',
            'clock_in' => '2025-06-25 09:00:00',
            'clock_out' => '2025-06-25 18:00:00',
        ]);

        $response = $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.application', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'remarks' => 'テストの備考',
            ]);

        $response->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'remarks' => 'テストの備考',
        ]);
        // ④ 管理者でログインして、申請一覧画面に表示されるかチェック
        $admin = Admin::factory()->create(['name' => 'テスト管理者', 'email' => 'admin@example.com', 'password' => 'password']);
        $this->actingAs($admin, 'admin');

        // 申請一覧画面にて
        $response = $this->get(route('admin.attendance.application.list')); // 管理者の申請一覧ルート
        $response->assertStatus(200);
        $name = $attendance->user->name; // 申請者の名前を取得
        $date = \Carbon\Carbon::parse($attendance->attendance_date)->format('Y/m/d');
        $response->assertSee($name); // 申請者の名前が表示され
        $response->assertSee($date); // 申請日が表示されるはず
        $response->assertSee('テストの備考'); // 備考が表示されるはず

        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id)->firstOrFail();

        //承認画面にて
        $response = $this->get(route('admin.attendance.show', ['id' => $attendanceRequest->id])); // 管理者の申請一覧ルート
        $response->assertStatus(200);
        $name = $attendance->user->name; // 申請者の名前を取得
        $year = \Carbon\Carbon::parse($attendance->attendance_date)->format('y年');
        $date = \Carbon\Carbon::parse($attendance->attendance_date)->format('n月d日');
        $response->assertSee($year); // 年が表示されるはず
        $response->assertSee($name); // 申請者の名前が表示され
        $response->assertSee($date); // 申請日が表示されるはず
        $response->assertSee('テストの備考'); // 備考が表示されるはず
    }


    //「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function testAttendanceDetailRevise2()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-06-25',
            'clock_in' => '2025-06-25 09:00:00',
            'clock_out' => '2025-06-25 18:00:00',
        ]);

        $response = $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.application', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'remarks' => 'テストの備考2',
            ]);

        $response->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'remarks' => 'テストの備考2',
        ]);

        $response = $this->get(route('attendance.application.list'));
        $response->assertStatus(200);
        $name = $attendance->user->name; // 申請者の名前を取得
        $date = \Carbon\Carbon::parse($attendance->attendance_date)->format('y/m/d');
        $response->assertSee($name); // 申請者の名前が表示され
        $response->assertSee($date); // 申請日が表示されるはず
        $response->assertSee('テストの備考2'); // 備考が表示されるはず
    }

    //承認済み」に管理者が承認した修正申請が全て表示されている
    public function testAttendanceDetailRevise3()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-06-25',
            'clock_in' => '2025-06-25 09:00:00',
            'clock_out' => '2025-06-25 18:00:00',
        ]);

        $response = $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.application', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'remarks' => 'テストの備考3',
            ]);

        $response->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));

        // 管理者が承認したということ
        AttendanceRequest::where('attendance_id', $attendance->id)->update(['review_status' => 1]);

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'remarks' => 'テストの備考3',
            'review_status' => 1, // 承認済み
        ]);

        $response = $this->get('/attendance/application/list?review_status=1'); // 承認済みの申請一覧ルート
        $response->assertStatus(200);
        $name = $attendance->user->name; // 申請者の名前を取得
        $date = \Carbon\Carbon::parse($attendance->attendance_date)->format('y/m/d');
        $response->assertSee($name); // 申請者の名前が表示され
        $response->assertSee($date); // 申請日が表示されるはず
        $response->assertSee('テストの備考3'); // 備考が表示されるはず
    }

    //各申請の「詳細」を押下すると申請詳細画面に遷移する
    public function testAttendanceDetailRevise4()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-06-25',
            'clock_in' => '2025-06-25 09:00:00',
            'clock_out' => '2025-06-25 18:00:00',
        ]);

        $response = $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.application', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'remarks' => 'テストの備考4',
            ]);

        $response = $this->get(route('attendance.application.list'));
        $response->assertStatus(200);
        // 「詳細」リンクを押したのと同じURLにアクセス
        $response = $this->get(route('attendance.detail', $attendance->id));
        $response->assertStatus(200);
        // 念の為確認
        $response->assertSee('*承認待ちのため修正はできません。');

    }
}

