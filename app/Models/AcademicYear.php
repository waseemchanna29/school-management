<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'campus_id', 'name', 'start_date', 'end_date',
        'is_current', 'is_locked', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_current' => 'boolean',
        'is_locked'  => 'boolean',
    ];

    public function campus() { return $this->belongsTo(Campus::class); }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_locked)  return 'Locked';
        if ($this->is_current) return 'Current';
        if ($this->end_date->isPast()) return 'Past';
        return 'Upcoming';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->is_locked)  return 'badge-rejected';
        if ($this->is_current) return 'badge-approved';
        if ($this->end_date->isPast()) return 'badge-pending';
        return 'badge-info';
    }

    public function getDurationAttribute(): string
    {
        return $this->start_date->format('M Y')
             . ' – '
             . $this->end_date->format('M Y');
    }
}