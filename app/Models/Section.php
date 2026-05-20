<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['campus_id', 'class_id', 'name', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
