<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    protected $fillable = [
        'receipt_number', 'fee_invoice_id', 'student_id', 'campus_id',
        'amount', 'method', 'payment_date', 'reference', 'collected_by', 'remarks',
    ];

    protected $casts = ['payment_date' => 'date'];

    public function invoice()  { return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id'); }
    public function student()  { return $this->belongsTo(Student::class); }
    public function campus()   { return $this->belongsTo(Campus::class); }
}