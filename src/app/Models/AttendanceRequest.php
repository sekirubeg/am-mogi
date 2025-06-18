<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
    public function attendance_request_breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }
    public function breaks()
    {
        return $this->hasMany(AttendanceRequestBreak::class);
    }
}
