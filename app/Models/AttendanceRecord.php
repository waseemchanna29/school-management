<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'attendance_session_id', 'student_id', 'status', 'remarks',
    ];

    public function session() { return $this->belongsTo(AttendanceSession::class, 'attendance_session_id'); }
    public function student() { return $this->belongsTo(Student::class); }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'present' => 'badge-approved',
            'absent'  => 'badge-rejected',
            'late'    => 'badge-pending',
            'leave'   => 'badge-info',
            default   => 'badge-primary',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'present' => 'var(--success)',
            'absent'  => 'var(--danger)',
            'late'    => 'var(--warning)',
            'leave'   => 'var(--info)',
            default   => 'var(--text-muted)',
        };
    }
}