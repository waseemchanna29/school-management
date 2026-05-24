<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodTemplate extends Model
{
    protected $fillable = [
        'campus_id', 'label', 'start_time', 'end_time',
        'is_break', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_break'  => 'boolean',
        'is_active' => 'boolean',
    ];

    public function campus()  { return $this->belongsTo(Campus::class); }
    public function entries() { return $this->hasMany(TimetableEntry::class); }

    public function getDurationAttribute(): string
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end   = \Carbon\Carbon::parse($this->end_time);
        $mins  = $start->diffInMinutes($end);
        return $mins . ' min';
    }

    public function getTimeRangeAttribute(): string
    {
        return \Carbon\Carbon::parse($this->start_time)->format('h:i A')
             . ' – '
             . \Carbon\Carbon::parse($this->end_time)->format('h:i A');
    }
}