<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['campus_id', 'name', 'code', 'class_id', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subject');
    }
}
