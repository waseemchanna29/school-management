<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'campus_id',
        'roll_number',
        'gr_number',
        'full_name',
        'father_name',
        'mother_name',
        'cnic',
        'phone',
        'gender',
        'date_of_birth',
        'religion',
        'nationality',
        'blood_group',
        'address',
        'city',
        'district',
        'province',
        'class_id',
        'section_id',
        'admission_date',
        'previous_school',
        'status',
        'photo',
    ];

    protected $casts = ['date_of_birth' => 'date', 'admission_date' => 'date', 'is_active' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
    public function parentRecord()
    {
        return $this->hasOne(ParentRecord::class);
    }
    public function educationRecords()
    {
        return $this->hasMany(StudentEducationRecord::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active'      => 'badge-approved',
            'transferred' => 'badge-pending',
            'expelled'    => 'badge-rejected',
            default       => 'badge-rejected',
        };
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->diffInYears(now());
    }
}
