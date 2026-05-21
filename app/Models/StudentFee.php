<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    protected $fillable = [
        'student_id', 'campus_id', 'fee_label_id',
        'fee_structure_id', 'fee_structure_item_id',
        'academic_year', 'amount', 'is_active', 'note',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function student()          { return $this->belongsTo(Student::class); }
    public function campus()           { return $this->belongsTo(Campus::class); }
    public function feeLabel()         { return $this->belongsTo(FeeLabel::class); }
    public function feeStructure()     { return $this->belongsTo(FeeStructure::class); }
    public function feeStructureItem() { return $this->belongsTo(FeeStructureItem::class); }
}