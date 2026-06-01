<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamTypeWeight extends Model
{
    protected $fillable = [
        'campus_id', 'exam_type', 'label',
        'weight', 'is_active', 'sort_order',
    ];

    protected $casts = ['is_active' => 'boolean', 'weight' => 'decimal:2'];

    public function campus() { return $this->belongsTo(Campus::class); }

    public function isGlobal(): bool { return is_null($this->campus_id); }
}