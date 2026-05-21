<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeLabel extends Model
{
    protected $fillable = ['campus_id', 'name', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function campus()         { return $this->belongsTo(Campus::class); }
    public function structureItems() { return $this->hasMany(FeeStructureItem::class); }
    public function studentFees()    { return $this->hasMany(StudentFee::class); }
}