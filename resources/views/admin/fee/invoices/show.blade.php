@extends('layouts.app')
@section('title', 'Invoice')
@section('page-title', 'Invoice')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Invoice {{ $invoice->invoice_number }}</div>
        <div class="page-header-sub">
            {{ $invoice->period_label }} &bull; {{ $invoice->academic_year }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-outline-primary btn btn-sm">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('admin.fee.invoices.index') }}" class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

<!-- Summary -->
<div class="fee-summary-bar">
    <div class="fee-summary-card total">
        <span class="fee-summary-amount">PKR {{ number_format($invoice->total_amount, 2) }}</span>
        <span class="fee-summary-label">Total Amount</span>
    </div>
    <div class="fee-summary-card discount">
        <span class="fee-summary-amount">PKR {{ number_format($invoice->discount, 2) }}</span>
        <span class="fee-summary-label">Discount</span>
    </div>
    <div class="fee-summary-card total" style="border-top-color:var(--warning);">
        <span class="fee-summary-amount">PKR {{ number_format($invoice->fine, 2) }}</span>
        <span class="fee-summary-label">Fine / Penalty</span>
    </div>
    <div class="fee-summary-card paid">
        <span class="fee-summary-amount">PKR {{ number_format($invoice->paid_amount, 2) }}</span>
        <span class="fee-summary-label">Total Paid</span>
    </div>
    <div class="fee-summary-card balance">
        <span class="fee-summary-amount">PKR {{ number_format($invoice->balance, 2) }}</span>
        <span class="fee-summary-label">Balance Due</span>
    </div>
</div>

<div class="row">
    <div class="col-8">

        <!-- Invoice Card -->
        <div class="mb-2 invoice-card">
            <div class="invoice-card-header">
                <div>
                    <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                    <div style="color:rgba(255,255,255,0.7); font-size:0.88rem; margin-top:4px;">
                        {{ $invoice->period_label }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <span class="fee-status-pill {{ $invoice->status }}">
                        <i class="fas fa-{{ $invoice->status === 'paid' ? 'check-circle' : ($invoice->status === 'partial' ? 'adjust' : 'times-circle') }}"></i>
                        {{ ucfirst($invoice->status) }}
                    </span>
                    <div style="color:rgba(255,255,255,0.6); font-size:0.82rem; margin-top:6px;">
                        Due: {{ $invoice->due_date->format('d M, Y') }}
                    </div>
                </div>
            </div>

            <!-- Student Info -->
            <div style="padding:1.2rem 1.6rem; border-bottom:1px solid var(--border);">
                <div style="font-weight:700; font-size:0.78rem; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted); margin-bottom:0.6rem;">Bill To</div>
                <strong style="font-size:1rem;">{{ $invoice->student->full_name }}</strong>
                <div style="color:var(--text-muted); font-size:0.85rem; margin-top:2px;">
                    Roll: {{ $invoice->student->roll_number }} &bull;
                    {{ $invoice->student->schoolClass->name ?? '' }}
                    @if($invoice->student->section) — {{ $invoice->student->section->name }} @endif
                </div>
                <div style="color:var(--text-muted); font-size:0.85rem;">
                    Father: {{ $invoice->student->father_name }}
                </div>
            </div>

            <!-- Items -->
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr><th>#</th><th>Fee Description</th><th style="text-align:right;">Amount</th></tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->label_name }}</td>
                            <td style="text-align:right; font-weight:600;">PKR {{ number_format($item->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:var(--light-bg);">
                            <td colspan="2" style="text-align:right; font-weight:700; padding:0.8rem 1rem;">Subtotal</td>
                            <td style="text-align:right; font-weight:700; padding:0.8rem 1rem;">PKR {{ number_format($invoice->total_amount, 2) }}</td>
                        </tr>
                        @if($invoice->discount > 0)
                        <tr style="color:var(--success);">
                            <td colspan="2" style="text-align:right; font-weight:600; padding:0.5rem 1rem;">Discount</td>
                            <td style="text-align:right; font-weight:600; padding:0.5rem 1rem;">- PKR {{ number_format($invoice->discount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->fine > 0)
                        <tr style="color:var(--danger);">
                            <td colspan="2" style="text-align:right; font-weight:600; padding:0.5rem 1rem;">Fine / Penalty</td>
                            <td style="text-align:right; font-weight:600; padding:0.5rem 1rem;">+ PKR {{ number_format($invoice->fine, 2) }}</td>
                        </tr>
                        @endif
                        <tr style="background:var(--primary); color:var(--white);">
                            <td colspan="2" style="text-align:right; font-weight:700; padding:0.9rem 1rem; font-size:1rem; color:var(--white);">Net Amount</td>
                            <td style="text-align:right; font-weight:700; padding:0.9rem 1rem; font-size:1.1rem; color:var(--white);">PKR {{ number_format($invoice->net_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($invoice->remarks)
            <div style="padding:1rem 1.6rem; border-top:1px solid var(--border); font-size:0.85rem; color:var(--text-muted);">
                <strong>Remarks:</strong> {{ $invoice->remarks }}
            </div>
            @endif
        </div>

        <!-- Payment History -->
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-history"></i> Payment History</div>
            </div>
            @if($invoice->payments->isEmpty())
                <div style="padding:1.5rem; text-align:center; color:var(--text-muted);">No payments recorded yet.</div>
            @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr><th>Receipt #</th><th>Date</th><th>Method</th><th>Reference</th><th>Amount</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                        <tr>
                            <td><code style="font-size:0.82rem;">{{ $payment->receipt_number }}</code></td>
                            <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                            <td><span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</span></td>
                            <td>{{ $payment->reference ?? '—' }}</td>
                            <td style="font-weight:700; color:var(--success);">PKR {{ number_format($payment->amount, 2) }}</td>
                            <td>
                                @if($invoice->status !== 'paid')
                                <form action="{{ route('admin.fee.payments.destroy', $payment) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-outline-danger btn btn-sm"
                                            onclick="return confirm('Reverse this payment?')">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="col-4">
        <!-- Record Payment -->
        @if(!in_array($invoice->status, ['paid', 'waived']))
        <div class="mb-2 card" style="border-top:3px solid var(--success);">
            <div class="card-header">
                <div class="card-header-title" style="color:var(--success);"><i class="fas fa-money-bill-wave"></i> Record Payment</div>
            </div>
            <div class="card-body">
                <div style="margin-bottom:1rem; padding:0.8rem; background:rgba(25,135,84,0.07); border-radius:var(--radius-sm); text-align:center;">
                    <span style="font-size:0.8rem; color:var(--text-muted);">Balance Due</span>
                    <div style="font-size:1.5rem; font-weight:700; color:var(--danger);">
                        PKR {{ number_format($invoice->balance, 2) }}
                    </div>
                </div>
                <form action="{{ route('admin.fee.payments.store', $invoice) }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-form">
                        <label class="form-label">Amount *</label>
                        <input type="number" name="amount" class="form-control"
                               value="{{ $invoice->balance }}" min="1" max="{{ $invoice->balance }}" step="0.01">
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Payment Method *</label>
                        <select name="method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Payment Date *</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Reference / Cheque #</label>
                        <input type="text" name="reference" class="form-control" placeholder="Optional">
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Collected By</label>
                        <input type="text" name="collected_by" class="form-control" placeholder="Staff name">
                    </div>
                    <button type="submit" class="btn-block btn btn-success">
                        <i class="fas fa-check-circle"></i> Record Payment
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Adjustments -->
        @if(!in_array($invoice->status, ['paid', 'waived']))
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-sliders-h"></i> Adjustments</div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.fee.invoices.adjustments', $invoice) }}" method="POST" novalidate>
                    @csrf @method('PUT')
                    <div class="mb-form">
                        <label class="form-label">Discount (PKR)</label>
                        <input type="number" name="discount" class="form-control"
                               value="{{ $invoice->discount }}" min="0" step="0.01">
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Fine / Penalty (PKR)</label>
                        <input type="number" name="fine" class="form-control"
                               value="{{ $invoice->fine }}" min="0" step="0.01">
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control"
                               value="{{ $invoice->due_date->format('Y-m-d') }}">
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"
                                  placeholder="Optional notes">{{ $invoice->remarks }}</textarea>
                    </div>
                    <button type="submit" class="btn-block btn btn-primary">
                        <i class="fas fa-save"></i> Save Adjustments
                    </button>
                </form>
            </div>
        </div>

        <div class="card" style="border-color:rgba(13,202,240,0.3);">
            <div class="card-body" style="text-align:center;">
                <form action="{{ route('admin.fee.invoices.waive', $invoice) }}" method="POST">
                    @csrf
                    <p style="font-size:0.84rem; color:var(--text-muted); margin-bottom:0.8rem;">
                        Mark this invoice as fully waived (no payment needed).
                    </p>
                    <button type="submit" class="btn-block btn-outline-secondary btn"
                            onclick="return confirm('Waive this invoice? No payment will be required.')">
                        <i class="fas fa-hand-holding"></i> Waive Invoice
                    </button>
                </form>
            </div>
        </div>
        @endif

        @if($invoice->status === 'paid')
        <div class="card" style="border-top:3px solid var(--success);">
            <div class="card-body" style="text-align:center; padding:2rem;">
                <i class="fas fa-check-circle" style="font-size:3rem; color:var(--success); display:block; margin-bottom:0.8rem;"></i>
                <strong style="color:var(--success);">Fully Paid</strong>
                <div style="font-size:0.85rem; color:var(--text-muted); margin-top:4px;">
                    PKR {{ number_format($invoice->paid_amount, 2) }} received
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection