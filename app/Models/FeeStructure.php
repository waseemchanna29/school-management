<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    protected $fillable = [
        'campus_id', 'name', 'type', 'class_id',
        'academic_year', 'is_active', 'notes',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function campus()      { return $this->belongsTo(Campus::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function items()       { return $this->hasMany(FeeStructureItem::class); }

    public function getTotalAttribute(): float
    {
        return $this->items->where('is_active', true)->sum('amount');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'monthly'  => 'Monthly',
            'yearly'   => 'Yearly / Annual',
            'one_time' => 'One-Time',
            default    => ucfirst($this->type),
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->type) {
            'monthly'  => 'badge-primary',
            'yearly'   => 'badge-info',
            'one_time' => 'badge-pending',
            default    => 'badge-primary',
        };
    }
}