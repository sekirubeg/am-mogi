<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Admin;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function testAttendanceIndex()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 事前条件：対象ユーザーの今日の勤怠レコードが存在しないことを確認
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $user->id,
            'attendance_date' => Carbon::today()->format('Y-m-d'),
        ]);
        $response = $this->get('/attendance'); // 勤怠打刻画面のURL
        $response->assertStatus(200);
        $response->assertSee('<button type="submit" class="btn btn-dark">出勤</button>', false);
        // 3. 一般ユーザーが出勤処理を行う
        // 出勤時刻を正確に記録しておく
        $clockInMoment = Carbon::now();

        // Carbon::setTestNow() を使うと、テスト中の現在時刻を固定できるため、
        // より安定したテストが可能になります。
        Carbon::setTestNow($clockInMoment);


        $this->actingAs($user)->post(route('attendance.start')); // ユーザーとして出勤処理を実行
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'attendance_date' => $clockInMoment->format('Y-m-d'),
            'clock_in' => $clockInMoment->format('Y-m-d H:i:s'),
            'clock_out' => null,
        ]);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('出勤中');
    }

    public function testAttendanceFinishedWork()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now()->subHours(8)->format('Y-m-d H:i:s'), // 8時間前に出勤
            'clock_out' => Carbon::now()->subMinutes(1)->format('Y-m-d H:i:s'), // 1分前に退勤
        ]);
        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertDontSee('<button type="submit" class="btn btn-dark">出勤</button>', false);
    }


    public function testClockInTimeIsVisibleInAdminPanel()
    {
        // CSRFトークンの検証を無効にする（フォーム送信のため）
        $this->withoutMiddleware(VerifyCsrfToken::class);

        // 1. テスト用の一般ユーザーを作成（出勤するユーザー）
        $user = User::factory()->create();


        // 3. 一般ユーザーが出勤処理を行う
        // 出勤時刻を正確に記録しておく
        $clockInMoment = Carbon::now();

        // Carbon::setTestNow() を使うと、テスト中の現在時刻を固定できるため、
        // より安定したテストが可能になります。
        Carbon::setTestNow($clockInMoment);


        $this->actingAs($user)->post(route('attendance.start')); // ユーザーとして出勤処理を実行
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'attendance_date' => $clockInMoment->format('Y-m-d'),
            'clock_in' => $clockInMoment->format('Y-m-d H:i:s'),
            'clock_out' => null,
        ]);


        $admin = Admin::factory()->create(['email' => 'admin@example.com','name' => 'Admin User', 'password' => 'password']); // 管理者ユーザーを作成
        $this->actingAs($admin, 'admin'); // 'admin'ガードでログインする場合

        // 4. 管理画面から出勤の日付を確認する
        // 管理者として勤怠一覧画面にアクセス
        $response = $this->get('/admin/attendance/list'); // あなたの管理画面の勤怠一覧URLに合わせる

        // ページが正常に表示されたことを確認
        $response->assertStatus(200);

        // 5. 管理画面に出勤の「日付」と「時刻」が正確に記録されていることを確認
        // UIに表示される日付のフォーマットに合わせる (例: Y年m月d日, Y/m/d, Y-m-d など)
        $expectedDisplayedDate = $clockInMoment->format('Y年m月d日');
        // UIに表示される時刻のフォーマットに合わせる (例: H時i分s秒, H:i:s, H:i など)
        $expectedDisplayedTime = $clockInMoment->format('H:i');

        // レスポンスのHTMLコンテンツに期待する日時文字列が含まれているかを確認
        // assertSeeText はHTMLタグを無視してテキストコンテンツをチェック
        $response->assertSeeText($user->name); // ユーザー名が表示されているか
        $response->assertSeeText($expectedDisplayedTime); // 出勤時刻が表示されているか
        $response->assertSeeText($expectedDisplayedDate); // 出勤日付が表示されているか

    }
}
