<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeInvoice extends Model
{
    protected $fillable = [
        'invoice_number', 'student_id', 'campus_id', 'academic_year',
        'period_label', 'period_type', 'month', 'year',
        'total_amount', 'discount', 'fine', 'net_amount',
        'paid_amount', 'balance', 'status', 'due_date', 'remarks',
    ];

    protected $casts = ['due_date' => 'date'];

    public function student()  { return $this->belongsTo(Student::class); }
    public function campus()   { return $this->belongsTo(Campus::class); }
    public function items()    { return $this->hasMany(FeeInvoiceItem::class); }
    public function payments() { return $this->hasMany(FeePayment::class); }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'paid'    => 'badge-approved',
            'partial' => 'badge-pending',
            'waived'  => 'badge-info',
            default   => 'badge-rejected',
        };
    }

    public function recalculate(): void
    {
        $this->paid_amount = $this->payments()->sum('amount');
        $this->net_amount  = $this->total_amount - $this->discount + $this->fine;
        $this->balance     = max(0, $this->net_amount - $this->paid_amount);
        $this->status      = $this->balance <= 0
            ? 'paid'
            : ($this->paid_amount > 0 ? 'partial' : 'unpaid');
        $this->save();
    }
}