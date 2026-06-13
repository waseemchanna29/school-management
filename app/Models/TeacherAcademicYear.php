<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAcademicYear extends Model
{
    protected $fillable = ['teacher_id', 'academic_year_id'];

    public function teacher()      { return $this->belongsTo(Teacher::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
}