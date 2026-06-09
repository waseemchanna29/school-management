<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $fillable = [
        'campus_id',
        'class_id',
        'section_id',
        'teacher_id',
        'date',
        'academic_year_id',              // ← string removed, id added
        'status',
        'locked',
        'remarks',
        'submitted_at',
    ];

    protected $casts = [
        'date'         => 'date',
        'submitted_at' => 'datetime',
        'locked'       => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
    public function records()
    {
        return $this->hasMany(AttendanceRecord::class);
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }  // ← NEW

    // ── Status Helpers ────────────────────────────────────────────────────────
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }
    public function isLocked(): bool
    {
        return $this->locked;
    }
    public function isEditable(): bool
    {
        return !$this->locked && !$this->isSubmitted();
    }

    // ── Computed Counts ───────────────────────────────────────────────────────
    public function getPresentCountAttribute(): int
    {
        return $this->records->where('status', 'present')->count();
    }

    public function getAbsentCountAttribute(): int
    {
        return $this->records->where('status', 'absent')->count();
    }

    public function getLateCountAttribute(): int
    {
        return $this->records->where('status', 'late')->count();
    }

    public function getLeaveCountAttribute(): int
    {
        return $this->records->where('status', 'leave')->count();
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'submitted' => 'badge-approved',
            default     => 'badge-pending',
        };
    }
}
