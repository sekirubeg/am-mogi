<?php

namespace Tests\Feature;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Tests\TestCase;

class AdminReviseConfirmTest extends TestCase
{
    use RefreshDatabase;

    // 管理者が勤怠修正申請の確認ができる
    public function testAdminCanConfirmAttendanceRevision()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);

        $this->actingAs($admin, 'admin');
        // 承認待ちの修正申請を持つユーザーを2人作成
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // 勤怠データを作成
        $users = [
            [
                'user' => $user1,
                'date' => '2025-07-01',
                'clock_in' => '2025-07-01 09:00:00',
                'clock_out' => '2025-07-01 18:00:00',
                'remarks' => '遅刻のため修正',
            ],
            [
                'user' => $user2,
                'date' => '2025-07-02',
                'clock_in' => '2025-07-01 10:00:00',
                'clock_out' => '2025-07-01 19:00:00',
                'remarks' => '早退のため修正',
            ],
        ];

        foreach ($users as $data) {
            $attendance = Attendance::factory()->create([
                'user_id' => $data['user']->id,
                'attendance_date' => $data['date'],
                'clock_in' => $data['clock_in'],
                'clock_out' => $data['clock_out'],
                'remarks' => $data['remarks'],
            ]);

            AttendanceRequest::factory()->create([
                'requested_by' => $data['user']->id,
                'requested_at' => $data['date'],
                'attendance_id' => $attendance->id,
                'requested_clock_in' => $data['clock_in'],
                'requested_clock_out' => $data['clock_out'],
                'review_status' => 0,
            ]);
        }
        // 勤怠修正申請一覧ページへアクセス
        $response = $this->get(route('admin.attendance.application.list'));
        // ステータス確認
        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee('2025/07/01');
        $response->assertSee($user2->name);
        $response->assertSee('2025/07/01');

    }

    //承認済みの修正申請が全て表示されている
    public function testAdminCanSeeApprovedAttendanceRevision()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);

        $this->actingAs($admin, 'admin');
        // 承認待ちの修正申請を持つユーザーを2人作成
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // 勤怠データを作成
        $users = [
            [
                'user' => $user1,
                'date' => '2025-07-01',
                'clock_in' => '2025-07-01 09:00:00',
                'clock_out' => '2025-07-01 18:00:00',
            ],
            [
                'user' => $user2,
                'date' => '2025-07-02',
                'clock_in' => '2025-07-01 10:00:00',
                'clock_out' => '2025-07-01 19:00:00',
            ],
        ];

        foreach ($users as $data) {
            $attendance = Attendance::factory()->create([
                'user_id' => $data['user']->id,
                'attendance_date' => $data['date'],
                'clock_in' => $data['clock_in'],
                'clock_out' => $data['clock_out'],
            ]);

            AttendanceRequest::factory()->create([
                'requested_by' => $data['user']->id,
                'requested_at' => $data['date'],
                'attendance_id' => $attendance->id,
                'requested_clock_in' => $data['clock_in'],
                'requested_clock_out' => $data['clock_out'],
                'review_status' => 1,
            ]);
        }
        // 勤怠修正申請一覧ページへアクセス
        $response = $this->get(route('admin.attendance.application.list', ['review_status' => 1]));
        // ステータス確認
        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee('2025/07/01');
        $response->assertSee($user2->name);
        $response->assertSee('2025/07/01');
    }

    //修正申請の詳細内容が正しく表示されている
    public function testAdminCanSeeAttendanceRevisionDetails()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        $user1 = User::factory()->create();
        $this->actingAs($admin, 'admin');
        // 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user1->id,
            'attendance_date' => '2025-07-01',
            'clock_in' => '2025-07-01 09:00:00',
            'clock_out' => '2025-07-01 18:00:00',
        ]);
        $attendance_request = AttendanceRequest::factory()->create([
            'requested_by' => $user1->id,
            'requested_at' => '2025-07-01',
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2025-07-01 10:00:00',
            'requested_clock_out' => '2025-07-01 19:00:00',
            'remarks' => '遅刻のため修正',
            'review_status' => 0,
        ]);
        // 勤怠修正詳細ページへアクセス
        $response = $this->get(route('admin.attendance.show', ['id' => $attendance_request->id]));
        // ステータス確認
        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee('2025年');
        $response->assertSee('7月1日');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('遅刻のため修正');
    }


    //修正申請の承認処理が正しく行われる
    public function testAdminCanApproveAttendanceRevision()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        $user1 = User::factory()->create();
        $this->actingAs($admin, 'admin');
        // 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user1->id,
            'attendance_date' => '2025-07-01',
            'clock_in' => '2025-07-01 09:00:00',
            'clock_out' => '2025-07-01 18:00:00',
        ]);
        $attendanceRequest = AttendanceRequest::factory()->create([
            'requested_by' => $user1->id,
            'requested_at' => '2025-07-01',
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2025-07-01 10:00:00',
            'requested_clock_out' => '2025-07-01 19:00:00',
            'remarks' => '遅刻のため修正',
            'review_status' => 0,
        ]);

        // 勤怠修正申請の承認処理を実行
        // コントローラの処理の観点から、clock_in, clock_out, remarksを送信
        $response = $this->post(route('admin.attendance.approve', ['id' => $attendance->id]), [
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'remarks' => '遅刻のため修正',
        ]);
        $response->assertStatus(200);

        // 修正申請が承認されたことを確認
        $this->assertDatabaseHas('attendance_requests', [
            'id' => $attendanceRequest->id,
            'review_status' => 1,
        ]);

        // 勤怠データが更新されていることを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '2025-07-01 10:00:00',
            'clock_out' => '2025-07-01 19:00:00',
        ]);
    }
}
