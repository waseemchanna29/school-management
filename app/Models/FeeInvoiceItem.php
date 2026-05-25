<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeInvoiceItem extends Model
{
    protected $fillable = ['fee_invoice_id', 'label', 'amount', 'sort_order'];

    public function invoice() { return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id'); }
}