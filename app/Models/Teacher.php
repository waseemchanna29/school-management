<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'campus_id',
        'employee_code',
        'full_name',
        'father_name',
        'cnic',
        'phone',
        'emergency_phone',
        'gender',
        'date_of_birth',
        'religion',
        'nationality',
        'domicile',
        'address',
        'city',
        'district',
        'province',
        'qualification',
        'specialization',
        'joining_date',
        'employment_type',
        'salary',
        'bank_name',
        'bank_account',
        'photo',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'date_of_birth' => 'date', 'joining_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject');
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->diffInYears(now());
    }

    // Add inside Teacher model
    public function classTeacherOf()
    {
        return $this->hasOne(Section::class, 'class_teacher_id');
    }
    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    // Add these relationships inside Teacher model:

    /**
     * Academic years this teacher is assigned to (via admin assignment).
     */
    public function academicYears()
    {
        return $this->belongsToMany(
            AcademicYear::class,
            'teacher_academic_years',
            'teacher_id',
            'academic_year_id'
        )->orderByDesc('start_date');
    }

    /**
     * Check if teacher is assigned to a specific academic year.
     */
    public function hasYearAccess(int $academicYearId): bool
    {
        return $this->academicYears()
            ->where('academic_year_id', $academicYearId)
            ->exists();
    }
}
