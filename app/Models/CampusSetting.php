<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampusSetting extends Model
{
    protected $fillable = [
        'campus_id', 'logo', 'phone', 'email', 'address', 'tagline',
    ];

    public function campus() { return $this->belongsTo(Campus::class); }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo
            ? asset('storage/' . $this->logo)
            : null;
    }
}