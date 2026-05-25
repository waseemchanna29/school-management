<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeScheduler extends Model
{
    protected $fillable = ['campus_id', 'name', 'description', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function campus()          { return $this->belongsTo(Campus::class); }
    public function items()           { return $this->hasMany(FeeSchedulerItem::class)->orderBy('sort_order'); }
    public function studentSchedulers(){ return $this->hasMany(StudentScheduler::class); }

    public function getTotalAttribute(): float
    {
        return $this->items->sum('amount');
    }
}