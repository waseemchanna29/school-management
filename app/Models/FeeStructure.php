<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    protected $fillable = ['campus_id', 'class_id', 'academic_year', 'is_active', 'notes'];
    protected $casts    = ['is_active' => 'boolean'];

    public function campus()      { return $this->belongsTo(Campus::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function items()       { return $this->hasMany(FeeStructureItem::class); }

    public function getTotalAttribute(): float
    {
        return $this->items->where('is_active', true)->sum('amount');
    }

    public function getTotalMonthlyAttribute(): float
    {
        return $this->items->where('is_active', true)
            ->filter(fn($i) => $i->feeLabel?->frequency === 'monthly')
            ->sum('amount');
    }
}