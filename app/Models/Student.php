<?php

namespace App\Models;

use App\Helpers\AcademicYearContext;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'campus_id',
        'full_name',
        'father_name',
        'mother_name',
        'cnic',
        'gender',
        'date_of_birth',
        'blood_group',
        'phone',
        'address',
        'city',
        'district',
        'province',
        'admission_date',
        'previous_school',
        'photo',
        'status',
        'gr_number',
        
    ];

    protected $casts = [
        'date_of_birth'  => 'date',
        'admission_date' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
    public function parentRecord()
    {
        return $this->hasOne(ParentRecord::class);
    }

    // All enrollments across all years
    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    // Current year enrollment (uses AcademicYearContext)
    public function currentEnrollment()
    {
        return $this->hasOne(StudentEnrollment::class)
            ->where('academic_year_id', AcademicYearContext::id());
    }

    // Latest enrollment (most recent year)
    public function latestEnrollment()
    {
        return $this->hasOne(StudentEnrollment::class)
            ->orderByDesc('academic_year_id');
    }

    // Enrollment for a specific year
    public function enrollmentForYear(int $academicYearId): ?StudentEnrollment
    {
        return $this->enrollments()
            ->where('academic_year_id', $academicYearId)
            ->first();
    }

    // Attendance
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    // Fee
    public function schedulerAssignment()
    {
        return $this->hasOne(StudentScheduler::class);
    }

    public function schedulerItems()
    {
        return $this->hasMany(StudentSchedulerItem::class);
    }

    public function feeInvoices()
    {
        return $this->hasMany(FeeInvoice::class);
    }

    // Marks
    public function marks()
    {
        return $this->hasMany(StudentMark::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    // Quick access to current year class via enrollment
    public function getClassNameAttribute(): string
    {
        return $this->currentEnrollment?->schoolClass?->name ?? '—';
    }

    public function getSectionNameAttribute(): string
    {
        return $this->currentEnrollment?->section?->name ?? '—';
    }

    public function getRollNumberAttribute(): string
    {
        return $this->currentEnrollment?->roll_number ?? '—';
    }

    public function getEnrollmentStatusAttribute(): string
    {
        return $this->currentEnrollment?->status ?? 'not enrolled';
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : null;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    // Scope to students enrolled in the current academic year in a campus
    public function scopeEnrolledInYear($query, int $academicYearId, int $campusId)
    {
        return $query->whereHas('enrollments', function ($q) use ($academicYearId, $campusId) {
            $q->where('academic_year_id', $academicYearId)
                ->where('campus_id', $campusId);
        });
    }
    // Scope to students enrolled in a specific section in a year
    public function scopeInSection($query, int $sectionId, int $academicYearId)
    {
        return $query->whereHas('enrollments', function ($q) use ($sectionId, $academicYearId) {
            $q->where('section_id', $sectionId)
                ->where('academic_year_id', $academicYearId);
        });
    }

    // Scope to students enrolled in a specific class in a year
    public function scopeInClass($query, int $classId, int $academicYearId)
    {
        return $query->whereHas('enrollments', function ($q) use ($classId, $academicYearId) {
            $q->where('class_id', $classId)
                ->where('academic_year_id', $academicYearId);
        });
    }
}
