<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceIndexConfirmTest extends TestCase
{
    use RefreshDatabase;

    //その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function testAdminCanSeeAllAttendancesForTheDay()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password']);
        $user1 = User::factory()->create(['name' => 'ユーザー一郎']);
        $user2 = User::factory()->create(['name' => 'ユーザー二郎']);

        $date = Carbon::create(2025, 6, 25);

        // 勤怠を作成
        Attendance::factory()->create([
            'user_id' => $user1->id,
            'attendance_date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9, 0),
            'clock_out' => $date->copy()->setTime(18, 0),
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'attendance_date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(10, 0),
            'clock_out' => $date->copy()->setTime(19, 0),
        ]);

        // 管理者ログイン
        $this->actingAs($admin, 'admin');

        // 勤怠一覧画面へアクセス（その日の勤怠一覧を表示する想定）
        $response = $this->get(route('admin.attendance.list', ['date' => $date->toDateString()]));

        // ステータス確認
        $response->assertStatus(200);

        // 各ユーザーの勤怠情報が表示されていることを確認
        $response->assertSee('ユーザー一郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('ユーザー二郎');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    //遷移した際に現在の日付が表示される
    public function testAdminAttendanceIndexShowsCurrentDate()
    {
        // 管理者を作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);
        // 管理者ログイン
        $this->actingAs($admin, 'admin');
        // 現在の日付を取得
        $currentDate = Carbon::now()->format('Y年n月d日');
        // 勤怠一覧画面へアクセス
        $response = $this->get(route('admin.attendance.list'));
        // ステータス確認
        $response->assertStatus(200);
        // 現在の日付が表示されていることを確認
        $response->assertSee($currentDate);
    }

    //「前日」を押下した時に前の日の勤怠情報が表示される
    public function testAdminCanSeePreviousDayAttendances()
    {
        // 管理者を作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);
        // 管理者ログイン
        $this->actingAs($admin, 'admin');
        // 前日の日付を取得
        $previousDate = Carbon::now()->subDay()->format('Y年n月d日');
        // 勤怠一覧画面へアクセス
        $response = $this->get(route('admin.attendance.list', ['date' => Carbon::now()->subDay()->toDateString()]));
        // ステータス確認
        $response->assertStatus(200);
        // 前日の日付が表示されていることを確認
        $response->assertSee($previousDate);
    }
    //「翌日」を押下した時に次の日の勤怠情報が表示される
    public function testAdminCanSeeNextDayAttendances()
    {
        /// 管理者を作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);
        // 管理者ログイン
        $this->actingAs($admin, 'admin');
        // 翌日の日付を取得
        $nextDate = Carbon::now()->addDay()->format('Y年n月d日');
        // 勤怠一覧画面へアクセス
        $response = $this->get(route('admin.attendance.list', ['date' => Carbon::now()->addDay()->toDateString()]));
        // ステータス確認
        $response->assertStatus(200);
        // 翌日の日付が表示されていることを確認
        $response->assertSee($nextDate);
    }
}


