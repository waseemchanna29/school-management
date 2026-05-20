<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeStructureItem extends Model
{
    protected $fillable = ['fee_structure_id', 'fee_label_id', 'amount', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function feeStructure() { return $this->belongsTo(FeeStructure::class); }
    public function feeLabel()     { return $this->belongsTo(FeeLabel::class); }
    public function studentFees()  { return $this->hasMany(StudentFee::class); }
}