<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'campus_id',
        'class_id',
        'section_id',
        'roll_number',
        'status',
        'enrolled_at',
        'notes',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
    ];

    // ── Relationships ────────────────────────────────────────────────────────
    public function student()      { return $this->belongsTo(Student::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function campus()       { return $this->belongsTo(Campus::class); }
    public function schoolClass()  { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section()      { return $this->belongsTo(Section::class); }

    // ── Status Helpers ────────────────────────────────────────────────────────
    public function isActive(): bool      { return $this->status === 'active'; }
    public function isPassed(): bool      { return $this->status === 'passed'; }
    public function isDetained(): bool    { return $this->status === 'detained'; }
    public function isLeft(): bool        { return $this->status === 'left'; }
    public function isTransferred(): bool { return $this->status === 'transferred'; }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'active'      => 'badge-approved',
            'passed'      => 'badge-info',
            'detained'    => 'badge-pending',
            'left'        => 'badge-rejected',
            'transferred' => 'badge-primary',
            default       => 'badge-primary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active'      => 'Active',
            'passed'      => 'Passed',
            'detained'    => 'Detained',
            'left'        => 'Left School',
            'transferred' => 'Transferred',
            default       => ucfirst($this->status),
        };
    }
}