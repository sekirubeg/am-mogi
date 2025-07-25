<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceBreak extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
