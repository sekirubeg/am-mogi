<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AdminUserInformationConfirmTest extends TestCase
{
    use RefreshDatabase;

    //管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function testAdminConfirmUserInformation()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        // 一般ユーザー複数作成
        $users = User::factory()->count(3)->create();
        $this->actingAs($admin, 'admin');
        $response = $this->get(route('admin.staff.list'));

        // ステータス確認
        $response->assertStatus(200);

        // 各ユーザーの氏名・メールアドレスが表示されていることを確認
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    //ユーザーの勤怠情報が正しく表示される
    public function testAdminCanSeeUserInformation()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        $user = User::factory()->create([
            'name' => 'ユーザー一郎',
            'email' => 'user@gmail.com',
            'password' => 'password',
        ]);
        // 勤怠情報を3件作成
        $attendances = collect();

        for ($i = 0; $i < 3; $i++) {
            $date = now()->addDays($i)->format('Y-m-d');
            $attendances->push(
                Attendance::factory()->create([
                    'user_id' => $user->id,
                    'attendance_date' => $date,
                    'clock_in' => $date . ' 09:00:00',
                    'clock_out' => $date . ' 18:00:00',
                ])
            );
        }
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 勤怠一覧ページにアクセス（ルート名は適宜変更）
        $response = $this->get(route('admin.staff.attendance', ['id' => $user->id]));

        // レスポンスOKであること
        $response->assertStatus(200);

        // 勤怠情報が表示されているか確認
        foreach ($attendances as $attendance) {
            $response->assertSee(Carbon::parse($attendance->attendance_date)->format('m/d'));
            $response->assertSee(substr($attendance->clock_in, 11, 5));  // HH:MM 表示のみを取り出す11文字目から5文字だけ
            $response->assertSee(substr($attendance->clock_out, 11, 5));
        }
    }

    //「前月」を押下した時に表示月の前月の情報が表示される
    public function testAdminCanSeePreviousMonthAttendances()
    {
        // 管理者を作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);
        $user = User::factory()->create([
            'name' => 'ユーザー一郎',
            'email' => 'user@gmail.com',
            'password' => 'password',
        ]);

        // 勤怠情報を3件作成
        $attendances = collect();
        for ($i = 0; $i < 3; $i++) {
            $date = now()->subMonth()->addDays($i)->format('Y-m-d');
            $attendances->push(
                Attendance::factory()->create([
                    'user_id' => $user->id,
                    'attendance_date' => $date,
                    'clock_in' => $date . ' 09:00:00',
                    'clock_out' => $date . ' 18:00:00',
                ])
            );
        }
        // 管理者ログイン
        $this->actingAs($admin, 'admin');
        // 前月の日付を取得
        $previousMonth = Carbon::now()->subMonth()->format('Y-m');
        // 勤怠一覧画面へアクセス
        $response = $this->get(route('admin.staff.attendance', ['id' => $user->id, 'month' => $previousMonth]));
        $response->assertStatus(200);
        $response->assertSee($previousMonth); // 表示年月が前月であることを確認
        foreach ($attendances as $attendance) {
            $response->assertSee(Carbon::parse($attendance->attendance_date)->format('m/d'));
            $response->assertSee(substr($attendance->clock_in, 11, 5));  // HH:MM 表示のみを取り出す11文字目から5文字だけ
            $response->assertSee(substr($attendance->clock_out, 11, 5));
        }
    }

    //「翌月」を押下した時に表示月の翌月の情報が表示される
    public function testAdminCanSeeNextMonthAttendances()
    {
        // 管理者を作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);
        $user = User::factory()->create([
            'name' => 'ユーザー一郎',
            'email' => 'user@gmail.com',
            'password' => 'password',
        ]);

        // 勤怠情報を3件作成
        $attendances = collect();
        for ($i = 0; $i < 3; $i++) {
            $date = now()->addMonth()->addDays($i)->format('Y-m-d');
            $attendances->push(
                Attendance::factory()->create([
                    'user_id' => $user->id,
                    'attendance_date' => $date,
                    'clock_in' => $date . ' 09:00:00',
                    'clock_out' => $date . ' 18:00:00',
                ])
            );
        }
        // 管理者ログイン
        $this->actingAs($admin, 'admin');
        // 翌月の日付を取得
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');
        // 勤怠一覧画面へアクセス
        $response = $this->get(route('admin.staff.attendance', ['id' => $user->id, 'month' => $nextMonth]));
        $response->assertStatus(200);
        $response->assertSee($nextMonth); // 表示年月が前月であることを確認
        foreach ($attendances as $attendance) {
            $response->assertSee(Carbon::parse($attendance->attendance_date)->format('m/d'));
            $response->assertSee(substr($attendance->clock_in, 11, 5));  // HH:MM 表示のみを取り出す11文字目から5文字だけ
            $response->assertSee(substr($attendance->clock_out, 11, 5));
        }
    }

    //「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function testAdminCanSeeAttendanceDetail()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        $user = User::factory()->create([
            'name' => 'ユーザー一郎',
            'email' => 'user@gmail.com',
            'password' => 'password',
        ]);
        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2025-07-01',
            'clock_in' => '2025-07-01 09:00:00',
            'clock_out' => '2025-07-01 18:00:00',
        ]);
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');
        // 勤怠一覧ページにアクセス
        $response = $this->get(route('admin.staff.attendance', ['id' => $user->id]));
        $response->assertStatus(200);
        // 勤怠詳細ページへアクセス
        $response = $this->get(route('admin.attendance.detail', ['id' => $attendance->id]));
        // ステータス確認
        $response->assertStatus(200);
        // 勤怠情報の内容が表示されていることを確認
        $response->assertSee('ユーザー一郎');
        $response->assertSee('2025年');
        $response->assertSee('7月1日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
