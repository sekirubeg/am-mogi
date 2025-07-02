<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    //休憩ボタンが正しく機能する
    public function testBreakIn()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now()->subHours(8)->format('Y-m-d H:i:s'), // 8時間前に出勤
            'clock_out' => null, // 1分前に退勤
        ]);
        $response = $this->get('/attendance'); // 勤怠打刻画面のURL
        $response->assertStatus(200);
        $response->assertSee('<button type="submit" class="btn" style="background-color: #FFFFFF; margin-left:3vw; ">休 憩 入</button>', false);

        $this->actingAs($user)->post(route('attendance.break.start')); // ユーザーとして出勤処理を実行
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩中');
    }


    //休憩は一日に何回でもできる
    public function testManyTimesBreakIn()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now()->subHours(8)->format('Y-m-d H:i:s'), // 8時間前に出勤
            'clock_out' => null, // 1分前に退勤
        ]);
        $response = $this->get('/attendance'); // 勤怠打刻画面のURL
        $response->assertStatus(200);

        $this->actingAs($user)->post(route('attendance.break.start')); // ユーザーとして出勤処理を実行
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩中');

        $response = $this->actingAs($user)->post(route('attendance.break.end'));
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<button type="submit" class="btn" style="background-color: #FFFFFF; margin-left:3vw; ">休 憩 入</button>', false);
    }

    //休憩戻ボタンが正しく機能する
    public function testBreakOut()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now()->subHours(8)->format('Y-m-d H:i:s'), // 8時間前に出勤
            'clock_out' => null, // 1分前に退勤
        ]);
        $response = $this->get('/attendance'); // 勤怠打刻画面のURL
        $response->assertStatus(200);
        $response->assertSee('<button type="submit" class="btn" style="background-color: #FFFFFF; margin-left:3vw; ">休 憩 入</button>', false);

        $this->actingAs($user)->post(route('attendance.break.start')); // ユーザーとして出勤処理を実行
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩中');
        $response->assertSee('<button type="submit" class="btn" style="background-color: #FFFFFF;">休 憩 戻</button>', false);

        $response = $this->actingAs($user)->post(route('attendance.break.end'));
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('出勤中');
    }

    //休憩戻は一日に何回でもできる
    public function testManyTimesBreakOut()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now()->subHours(8)->format('Y-m-d H:i:s'), // 8時間前に出勤
            'clock_out' => null, // 1分前に退勤
        ]);
        $response = $this->get('/attendance'); // 勤怠打刻画面のURL
        $response->assertStatus(200);

        $this->actingAs($user)->post(route('attendance.break.start')); // ユーザーとして出勤処理を実行
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩中');
        $response->assertSee('<button type="submit" class="btn" style="background-color: #FFFFFF;">休 憩 戻</button>', false);

        $response = $this->actingAs($user)->post(route('attendance.break.end'));
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('出勤中');

        $response = $this->actingAs($user)->post(route('attendance.break.start')); // ユーザーとして出勤処理を実行
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩中');
        $response->assertSee('<button type="submit" class="btn" style="background-color: #FFFFFF;">休 憩 戻</button>', false);
    }


    //休憩時刻が勤怠一覧画面で確認できる
    public function testBreakTimeVisibleInAttendanceList()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $now = Carbon::create(2024, 7, 1, 9, 0, 0); // 2024/7/1 9:00:00
        Carbon::setTestNow($now);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now()->subHours(8)->format('Y-m-d H:i:s'), // 8時間前に出勤
            'clock_out' => null, // 1分前に退勤
        ]);

        Carbon::setTestNow($now->copy()->addMinutes(30)); // 9:30
        $this->post(route('attendance.break.start'));


        // 10:30 にして休憩終了
        Carbon::setTestNow($now->copy()->addMinutes(90)); // 10:30
        $this->post(route('attendance.break.end'));


        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);
        $breakTime = AttendanceBreak::where('attendance_id', $attendance->id)->latest()->first();

        $this->assertNotNull($breakTime->break_start);
        $this->assertNotNull($breakTime->break_end);

        $start = Carbon::parse($breakTime->break_start);
        $end = Carbon::parse($breakTime->break_end);

        $diff = $start->diffInMinutes($end);
        $this->assertEquals(60, $diff);

        $response->assertSee('01:00');
    }
}
