<?php

namespace App\Models;

use App\Helpers\AcademicYearContext;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'campus_id', 'class_id', 'name', 'is_active', 'class_teacher_id',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function campus()       { return $this->belongsTo(Campus::class); }
    public function schoolClass()  { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function classTeacher() { return $this->belongsTo(Teacher::class, 'class_teacher_id'); }
    public function attendanceSessions() { return $this->hasMany(AttendanceSession::class); }

    // ── Students via enrollment (year-aware) ──────────────────────────────────
    // Returns students enrolled in this section for the current academic year
    public function enrolledStudents(int $academicYearId = null)
    {
        $yearId = $academicYearId ?? AcademicYearContext::id();

        return Student::whereHas('enrollments', fn($q) => $q
            ->where('section_id', $this->id)
            ->where('academic_year_id', $yearId)
            ->where('status', 'active')
        )
        ->with(['enrollments' => fn($q) => $q
            ->where('academic_year_id', $yearId)
        ])
        ->orderBy('full_name')
        ->get()
        ->each(fn($s) => $s->enrollment = $s->enrollments->first());
    }

    // ── Keep old relationship name but point to enrollments ───────────────────
    // This prevents "students() not found" errors in views that use $section->students
    // Returns a collection (not a relation) for the current year
    public function getStudentsAttribute()
    {
        return $this->enrolledStudents();
    }

    // Student count for current year
    public function getStudentsCountAttribute(): int
    {
        $yearId = AcademicYearContext::id();
        return StudentEnrollment::where('section_id', $this->id)
            ->where('academic_year_id', $yearId)
            ->where('status', 'active')
            ->count();
    }
}