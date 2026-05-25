<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeSchedulerItem extends Model
{
    protected $fillable = ['fee_scheduler_id', 'label', 'amount', 'sort_order'];

    public function scheduler() { return $this->belongsTo(FeeScheduler::class, 'fee_scheduler_id'); }
}