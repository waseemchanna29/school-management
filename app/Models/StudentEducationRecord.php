<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEducationRecord extends Model
{
     protected $fillable = [
        'student_id', 'level', 'institution_name', 'board_university',
        'passing_year', 'total_marks', 'obtained_marks', 'grade_division', 'certificate_number',
    ];

    public function student() { return $this->belongsTo(Student::class); }
}
