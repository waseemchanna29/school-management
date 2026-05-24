<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableEntry extends Model
{
    protected $fillable = [
        'timetable_id', 'period_template_id', 'day',
        'type', 'subject_id', 'teacher_id', 'custom_label',
    ];

    public function timetable()      { return $this->belongsTo(Timetable::class); }
    public function periodTemplate() { return $this->belongsTo(PeriodTemplate::class); }
    public function subject()        { return $this->belongsTo(Subject::class); }
    public function teacher()        { return $this->belongsTo(Teacher::class); }
}