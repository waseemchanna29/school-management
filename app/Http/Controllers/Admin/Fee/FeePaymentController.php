<?php

namespace App\Http\Controllers\Admin\Fee;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Services\FeeService;
use Illuminate\Http\Request;

class FeePaymentController extends Controller
{
    public function __construct(private FeeService $fee) {}

    public function store(Request $request, FeeInvoice $invoice)
    {
        if ($invoice->campus_id !== CampusContext::id()) abort(403);

        if (in_array($invoice->status, ['paid', 'waived'])) {
            return back()->with('error', 'Invoice is already ' . $invoice->status . '.');
        }

        $request->validate([
            'amount'       => ['required', 'numeric', 'min:1', 'max:' . $invoice->balance],
            'method'       => ['required', 'in:cash,bank_transfer,cheque,online'],
            'payment_date' => ['required', 'date'],
            'reference'    => ['nullable', 'string'],
            'collected_by' => ['nullable', 'string'],
            'remarks'      => ['nullable', 'string'],
        ]);

        $this->fee->recordPayment($invoice, $request->all());

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function destroy(FeePayment $payment)
    {
        if ($payment->campus_id !== CampusContext::id()) abort(403);
        $invoice = $payment->invoice;
        $payment->delete();
        $invoice->recalculate();
        return back()->with('success', 'Payment reversed.');
    }
}