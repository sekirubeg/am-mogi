<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequestBreak extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function attendance_request()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }
}
