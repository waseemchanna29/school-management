<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    protected $fillable = [
        'campus_id', 'class_id', 'section_id',
        'academic_year', 'name', 'days', 'is_active', 'notes',
    ];

    protected $casts = [
        'days'      => 'array',
        'is_active' => 'boolean',
    ];

    // Day labels map
    public const DAY_LABELS = [
        'Mon' => 'Monday',
        'Tue' => 'Tuesday',
        'Wed' => 'Wednesday',
        'Thu' => 'Thursday',
        'Fri' => 'Friday',
        'Sat' => 'Saturday',
    ];

    public function campus()      { return $this->belongsTo(Campus::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section()     { return $this->belongsTo(Section::class); }
    public function periods()     { return $this->hasMany(TimetablePeriod::class)->orderBy('sort_order')->orderBy('start_time'); }
    public function entries()     { return $this->hasMany(TimetableEntry::class); }

    public function getDaysLabelAttribute(): string
    {
        return collect($this->days)
            ->map(fn($d) => self::DAY_LABELS[$d] ?? $d)
            ->join(', ');
    }

    // Active days in order
    public function orderedDays(): array
    {
        $order = array_keys(self::DAY_LABELS);
        return collect($this->days ?? [])
            ->sortBy(fn($d) => array_search($d, $order))
            ->values()
            ->toArray();
    }

    // Build grid: period_id → day → entry
    public function buildGrid(): array
    {
        $grid = [];
        foreach ($this->periods as $period) {
            $grid[$period->id] = [];
            foreach ($this->orderedDays() as $day) {
                $grid[$period->id][$day] = $this->entries
                    ->where('timetable_period_id', $period->id)
                    ->where('day', $day)
                    ->first();
            }
        }
        return $grid;
    }
}