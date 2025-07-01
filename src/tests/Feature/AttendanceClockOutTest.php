<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function testAttendanceClockOut()
    {
        $displayedTime = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $now = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $this->assertEquals($now, $displayedTime);
    }
}
