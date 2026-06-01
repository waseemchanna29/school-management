<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeScale extends Model
{
    protected $fillable = ['campus_id', 'name', 'is_default', 'is_active'];
    protected $casts    = ['is_default' => 'boolean', 'is_active' => 'boolean'];

    public function campus() { return $this->belongsTo(Campus::class); }
    public function items()  { return $this->hasMany(GradeItem::class)->orderBy('sort_order'); }

    public function isGlobal(): bool { return is_null($this->campus_id); }

    /** Get the grade item for a given percentage */
    public function getGrade(float $percentage): ?GradeItem
    {
        return $this->items
            ->where('min_marks', '<=', $percentage)
            ->where('max_marks', '>=', $percentage)
            ->first();
    }
}