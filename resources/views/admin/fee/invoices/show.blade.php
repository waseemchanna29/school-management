@extends('layouts.app')
@section('title', 'Invoice')
@section('page-title', 'Invoice')

@section('content')

<div class="page-header no-print">
    <div>
        <div class="page-header-title">{{ $invoice->invoice_number }}</div>
        <div class="page-header-sub">{{ $invoice->billing_period_label }}</div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            <i class="fas fa-print"></i> Print Slip
        </button>
        <a href="{{ route('admin.fee.invoices.index') }}" class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

{{-- ============================================================ --}}
{{-- POS SLIP — Side by side: Student Copy | School Copy         --}}
{{-- ============================================================ --}}
<div class="pos-wrapper">

    @for($copy = 1; $copy <= 2; $copy++)
    @if($copy === 2)
    <div class="pos-divider"></div>
    @endif

    <div class="pos-copy">
        <div class="pos-copy-label">
            {{ $copy === 1 ? '✂ Student Copy' : '✂ School Copy' }}
        </div>

        {{-- School Header --}}
        @if($setting?->logo)
            <img src="{{ $setting->logo_url }}" alt="Logo" class="pos-logo">
        @endif
        <div class="pos-school-name">{{ $campus->name }}</div>
        @if($setting?->tagline)
            <div class="pos-school-sub">{{ $setting->tagline }}</div>
        @endif
        @if($setting?->address)
            <div class="pos-school-sub">{{ $setting->address }}</div>
        @endif
        @if($setting?->phone)
            <div class="pos-school-sub">Ph: {{ $setting->phone }}</div>
        @endif

        <hr class="pos-hr">

        {{-- Invoice Meta --}}
        <div class="pos-row">
            <span class="pos-row-label">Invoice #</span>
            <span class="pos-row-value">{{ $invoice->invoice_number }}</span>
        </div>
        <div class="pos-row">
            <span class="pos-row-label">Period</span>
            <span class="pos-row-value">{{ $invoice->billing_period_label }}</span>
        </div>
        <div class="pos-row">
            <span class="pos-row-label">Due Date</span>
            <span class="pos-row-value">{{ $invoice->due_date->format('d-M-Y') }}</span>
        </div>

        <hr class="pos-hr">

        {{-- Student Info --}}
        <div class="pos-row">
            <span class="pos-row-label">Student</span>
            <span class="pos-row-value">{{ $invoice->student->full_name }}</span>
        </div>
        <div class="pos-row">
            <span class="pos-row-label">Roll No.</span>
            <span class="pos-row-value">{{ $invoice->student->roll_number }}</span>
        </div>
        <div class="pos-row">
            <span class="pos-row-label">Class</span>
            <span class="pos-row-value">
                {{ $invoice->student->schoolClass->name ?? '—' }}
                @if($invoice->student->section)
                    – {{ $invoice->student->section->name }}
                @endif
            </span>
        </div>
        <div class="pos-row">
            <span class="pos-row-label">Father</span>
            <span class="pos-row-value">{{ $invoice->student->father_name }}</span>
        </div>

        <hr class="pos-hr">

        {{-- Fee Items --}}
        <div style="font-size:0.7rem; color:#888; margin-bottom:3px; text-transform:uppercase; letter-spacing:1px;">
            Fee Details
        </div>
        <div class="pos-items">
            @foreach($invoice->items as $item)
            <div class="pos-item-row">
                <span>{{ $item->label }}</span>
                <span>PKR {{ number_format($item->amount, 0) }}</span>
            </div>
            @endforeach
        </div>

        <hr class="pos-hr">

        {{-- Subtotal + Adjustments --}}
        <div class="pos-row">
            <span class="pos-row-label">Subtotal</span>
            <span class="pos-row-value">PKR {{ number_format($invoice->subtotal, 0) }}</span>
        </div>
        @if($invoice->outstanding > 0)
        <div class="pos-row" style="color:#b02a37;">
            <span class="pos-row-label">Outstanding</span>
            <span class="pos-row-value">+ PKR {{ number_format($invoice->outstanding, 0) }}</span>
        </div>
        @endif
        @if($invoice->fine > 0)
        <div class="pos-row" style="color:#b02a37;">
            <span class="pos-row-label">Fine / Penalty</span>
            <span class="pos-row-value">+ PKR {{ number_format($invoice->fine, 0) }}</span>
        </div>
        @endif
        @if($invoice->discount > 0)
        <div class="pos-row" style="color:#146c43;">
            <span class="pos-row-label">Discount</span>
            <span class="pos-row-value">- PKR {{ number_format($invoice->discount, 0) }}</span>
        </div>
        @endif

        <div class="pos-total-row">
            <span>Net Payable</span>
            <span>PKR {{ number_format($invoice->net_amount, 0) }}</span>
        </div>

        @if($invoice->paid_amount > 0)
        <div class="pos-row" style="color:#146c43; font-weight:600;">
            <span class="pos-row-label">Amount Paid</span>
            <span class="pos-row-value">PKR {{ number_format($invoice->paid_amount, 0) }}</span>
        </div>
        <div class="pos-row" style="font-weight:700;">
            <span class="pos-row-label">Balance Due</span>
            <span class="pos-row-value"
                  style="color:{{ $invoice->balance > 0 ? '#b02a37' : '#146c43' }};">
                PKR {{ number_format($invoice->balance, 0) }}
            </span>
        </div>
        @endif

        {{-- Status Stamp --}}
        <div style="text-align:center; margin:0.6rem 0;">
            <span class="pos-status-stamp {{ $invoice->status }}">
                {{ strtoupper($invoice->status) }}
            </span>
        </div>

        @if($invoice->remarks)
        <div style="font-size:0.72rem; color:#888; text-align:center;">
            Note: {{ $invoice->remarks }}
        </div>
        @endif

        <div class="pos-footer">
            Printed: {{ now()->format('d-M-Y h:i A') }}<br>
            {{ $setting?->email ?? '' }}
        </div>
    </div>
    @endfor

</div>

{{-- ============================================================ --}}
{{-- ADMIN PANEL (no-print) — Record Payment + Adjustments       --}}
{{-- ============================================================ --}}
<div class="no-print" style="margin-top:2rem;">
    <div class="row">

        {{-- Payment Recording --}}
        @if(!in_array($invoice->status, ['paid', 'waived']))
        <div class="col-6">
            <div class="card" style="border-top:3px solid var(--success);">
                <div class="card-header">
                    <div class="card-header-title" style="color:var(--success);">
                        <i class="fas fa-money-bill-wave"></i> Record Payment
                    </div>
                    <div style="font-size:0.88rem; color:var(--danger); font-weight:700;">
                        Balance: PKR {{ number_format($invoice->balance, 0) }}
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.fee.payments.store', $invoice) }}"
                          method="POST" novalidate>
                        @csrf
                        <div class="row">
                            <div class="mb-form col-6">
                                <label class="form-label">Amount *</label>
                                <input type="number" name="amount" class="form-control"
                                       value="{{ $invoice->balance }}"
                                       min="1" max="{{ $invoice->balance }}" step="0.01">
                            </div>
                            <div class="mb-form col-6">
                                <label class="form-label">Method *</label>
                                <select name="method" class="form-select">
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-form col-6">
                                <label class="form-label">Payment Date *</label>
                                <input type="date" name="payment_date" class="form-control"
                                       value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="mb-form col-6">
                                <label class="form-label">Collected By</label>
                                <input type="text" name="collected_by" class="form-control"
                                       placeholder="Staff name">
                            </div>
                        </div>
                        <div class="mb-form">
                            <label class="form-label">Reference (Cheque/Transfer #)</label>
                            <input type="text" name="reference" class="form-control"
                                   placeholder="Optional">
                        </div>
                        <button type="submit" class="btn-block btn btn-success">
                            <i class="fas fa-check-circle"></i> Record Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Adjustments --}}
        @if(!in_array($invoice->status, ['paid', 'waived']))
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-title">
                        <i class="fas fa-sliders-h"></i> Adjust Invoice
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.fee.invoices.adjust', $invoice) }}"
                          method="POST" novalidate>
                        @csrf @method('PUT')
                        <div class="row">
                            <div class="mb-form col-4">
                                <label class="form-label">Outstanding</label>
                                <input type="number" name="outstanding" class="form-control"
                                       value="{{ $invoice->outstanding }}" min="0" step="0.01">
                            </div>
                            <div class="mb-form col-4">
                                <label class="form-label">Fine</label>
                                <input type="number" name="fine" class="form-control"
                                       value="{{ $invoice->fine }}" min="0" step="0.01">
                            </div>
                            <div class="mb-form col-4">
                                <label class="form-label">Discount</label>
                                <input type="number" name="discount" class="form-control"
                                       value="{{ $invoice->discount }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="mb-form">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control"
                                   value="{{ $invoice->due_date->format('Y-m-d') }}">
                        </div>
                        <div class="mb-form">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control"
                                   value="{{ $invoice->remarks }}"
                                   placeholder="Optional">
                        </div>
                        <div style="display:flex; gap:0.6rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save
                            </button>
                            <form action="{{ route('admin.fee.invoices.waive', $invoice) }}"
                                  method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-outline-secondary btn"
                                        onclick="return confirm('Waive this invoice?')">
                                    <i class="fas fa-hand-holding"></i> Waive
                                </button>
                            </form>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Payment History --}}
        @if($invoice->payments->isNotEmpty())
        <div class="col-12" style="margin-top:1rem;">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-title">
                        <i class="fas fa-history"></i> Payment History
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Receipt #</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Collected By</th>
                                <th>Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $payment)
                            <tr>
                                <td><code style="font-size:0.8rem;">{{ $payment->receipt_number }}</code></td>
                                <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ ucfirst(str_replace('_',' ',$payment->method)) }}
                                    </span>
                                </td>
                                <td>{{ $payment->reference ?? '—' }}</td>
                                <td>{{ $payment->collected_by ?? '—' }}</td>
                                <td style="font-weight:700; color:var(--success);">
                                    PKR {{ number_format($payment->amount, 0) }}
                                </td>
                                <td>
                                    @if($invoice->status !== 'paid')
                                    <form action="{{ route('admin.fee.payments.destroy', $payment) }}"
                                          method="POST" style="display:inline;">
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
            </div>
        </div>
        @endif

        {{-- Paid Message --}}
        @if($invoice->status === 'paid')
        <div class="col-12" style="margin-top:1rem;">
            <div class="card" style="border-top:3px solid var(--success);">
                <div class="card-body" style="text-align:center; padding:2rem;">
                    <i class="fas fa-check-circle"
                       style="font-size:3rem; color:var(--success); display:block; margin-bottom:0.8rem;"></i>
                    <strong style="color:var(--success); font-size:1.1rem;">Fully Paid</strong>
                    <div style="color:var(--text-muted); font-size:0.88rem; margin-top:4px;">
                        PKR {{ number_format($invoice->paid_amount, 0) }} received
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection