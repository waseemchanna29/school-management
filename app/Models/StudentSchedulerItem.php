<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSchedulerItem extends Model
{
    protected $fillable = [
        'student_id', 'campus_id', 'fee_scheduler_id',
        'label', 'amount', 'is_active', 'sort_order', 'note',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function student()   { return $this->belongsTo(Student::class); }
    public function scheduler() { return $this->belongsTo(FeeScheduler::class, 'fee_scheduler_id'); }
}