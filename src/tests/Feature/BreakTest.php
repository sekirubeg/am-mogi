<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;
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

}
