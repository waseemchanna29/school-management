<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeLabel extends Model
{
    protected $fillable = ['campus_id', 'name', 'frequency', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function campus()          { return $this->belongsTo(Campus::class); }
    public function structureItems()  { return $this->hasMany(FeeStructureItem::class); }
    public function studentFees()     { return $this->hasMany(StudentFee::class); }

    public function getFrequencyLabelAttribute(): string
    {
        return match($this->frequency) {
            'monthly'  => 'Monthly',
            'yearly'   => 'Yearly',
            'one_time' => 'One-Time',
            default    => ucfirst($this->frequency),
        };
    }

    public function getFrequencyBadgeClassAttribute(): string
    {
        return match($this->frequency) {
            'monthly'  => 'badge-primary',
            'yearly'   => 'badge-info',
            'one_time' => 'badge-pending',
            default    => 'badge-primary',
        };
    }
}