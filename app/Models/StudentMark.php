<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMark extends Model
{
    protected $fillable = [
        'student_id', 'subject_id', 'teacher_id', 'campus_id',
        'academic_year', 'term', 'exam_type',
        'marks_obtained', 'total_marks', 'remarks', 'exam_date',
    ];

    protected $casts = ['exam_date' => 'date'];

    public function student() { return $this->belongsTo(Student::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function campus()  { return $this->belongsTo(Campus::class); }

    public function getPercentageAttribute(): float
    {
        if ($this->total_marks <= 0) return 0;
        return round(($this->marks_obtained / $this->total_marks) * 100, 2);
    }
}