<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeInvoiceItem extends Model
{
    protected $fillable = ['fee_invoice_id', 'fee_label_id', 'label_name', 'amount'];

    public function invoice()  { return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id'); }
    public function feeLabel() { return $this->belongsTo(FeeLabel::class); }
}