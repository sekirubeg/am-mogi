<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function attendance_requests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }
    public function attendance_breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }
    public function request()
    {
        return $this->hasOne(AttendanceRequest::class);
    }
}
