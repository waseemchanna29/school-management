<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentScheduler extends Model
{
    protected $fillable = [
        'student_id',
        'campus_id',
        'academic_year_id',      // ← NEW
        'fee_scheduler_id',
        'assigned_date',
    ];

    protected $casts = ['assigned_date' => 'date'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
    public function feeScheduler()
    {
        return $this->belongsTo(FeeScheduler::class);
    }

    public function items()
    {
        return $this->hasMany(StudentSchedulerItem::class, 'student_id', 'student_id')
            ->where('fee_scheduler_id', $this->fee_scheduler_id);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
