<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeItem extends Model
{
    protected $fillable = [
        'grade_scale_id', 'grade', 'min_marks', 'max_marks',
        'gpa', 'description', 'color', 'sort_order',
    ];

    public function scale() { return $this->belongsTo(GradeScale::class, 'grade_scale_id'); }

    public function getColorStyleAttribute(): string
    {
        $color = $this->color ?? '#6c7a8d';
        return "background:rgba(" . implode(',', $this->hexToRgb($color)) . ",0.13); color:{$color}; border:1px solid rgba(" . implode(',', $this->hexToRgb($color)) . ",0.35);";
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}