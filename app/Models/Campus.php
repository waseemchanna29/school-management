<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    protected $fillable = [
        'name', 'code', 'city', 'district', 'province',
        'address', 'phone', 'email', 'principal_name', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function admins()
    {
        return $this->belongsToMany(User::class, 'admin_campus');
    }

    public function teachers()  { return $this->hasMany(Teacher::class); }
    public function students()  { return $this->hasMany(Student::class); }
    public function classes()   { return $this->hasMany(SchoolClass::class); }
    public function sections()  { return $this->hasMany(Section::class); }
    public function subjects()  { return $this->hasMany(Subject::class); }
}