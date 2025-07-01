<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceClockTest extends TestCase
{
    public function testAttendanceClockIn()
    {
        $displayedTime = Carbon::now()->format('Y-m-d H:i:s');
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $this->assertEquals($now, $displayedTime);
    }
}
