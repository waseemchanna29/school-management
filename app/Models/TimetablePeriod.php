<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TimetablePeriod extends Model
{
    protected $fillable = [
        'timetable_id', 'label', 'start_time',
        'end_time', 'is_break', 'sort_order',
    ];

    protected $casts = ['is_break' => 'boolean'];

    public function timetable() { return $this->belongsTo(Timetable::class); }
    public function entries()   { return $this->hasMany(TimetableEntry::class); }

    public function getTimeRangeAttribute(): string
    {
        return Carbon::parse($this->start_time)->format('h:i A')
             . ' – '
             . Carbon::parse($this->end_time)->format('h:i A');
    }

    public function getDurationAttribute(): string
    {
        $mins = Carbon::parse($this->start_time)->diffInMinutes(Carbon::parse($this->end_time));
        return $mins . ' min';
    }
}