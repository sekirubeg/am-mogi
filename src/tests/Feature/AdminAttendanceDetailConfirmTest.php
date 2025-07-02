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

    public function testAdminCanConfirmAttendanceDetail()
    {
        // 管理者とユーザーを作成
        $admin = Admin::factory()->create([
            'name' => '管理者太郎',
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        $user = User::factory()->create(['name' => 'ユーザー一郎']);
}
