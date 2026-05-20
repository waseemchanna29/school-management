<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentRecord extends Model
{
    protected $table    = 'parents';
    protected $fillable = [
        'student_id',
        'father_full_name', 'father_cnic', 'father_phone', 'father_occupation',
        'father_qualification', 'father_income', 'father_is_alive',
        'mother_full_name', 'mother_cnic', 'mother_phone', 'mother_occupation',
        'mother_qualification', 'mother_is_alive',
        'guardian_name', 'guardian_relation', 'guardian_phone', 'guardian_cnic', 'guardian_address',
    ];

    protected $casts = ['father_is_alive' => 'boolean', 'mother_is_alive' => 'boolean'];

    public function student() { return $this->belongsTo(Student::class); }
}
