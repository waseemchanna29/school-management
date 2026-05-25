@extends('layouts.app')
@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Fee Invoices</div>
        <div class="page-header-sub">All generated invoices</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.fee.invoices.bulk') }}" class="btn btn-warning">
            <i class="fas fa-bolt"></i> Bulk Generate
        </a>
        <a href="{{ route('admin.fee.invoices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Single Invoice
        </a>
    </div>
</div>

<form method="GET">
    <div class="filter-bar">
        <div>
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control"
                   value="{{ request('search') }}"
                   placeholder="Invoice #, student name, roll...">
        </div>
        <div>
            <label class="form-label">Month</label>
            <select name="billing_month" class="form-select">
                <option value="">All Months</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('billing_month') == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0,0,0,$m,1)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Year</label>
            <select name="billing_year" class="form-select">
                <option value="">All Years</option>
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ request('billing_year') == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach(['unpaid','partial','paid','waived'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="{{ route('admin.fee.invoices.index') }}" class="btn-outline-secondary btn">
                Clear
            </a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Student</th>
                    <th>Period</th>
                    <th>Net Amount</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td><code style="font-size:0.8rem;">{{ $invoice->invoice_number }}</code></td>
                    <td>
                        <strong>{{ $invoice->student->full_name ?? '—' }}</strong>
                        <div style="font-size:0.76rem; color:var(--text-muted);">
                            {{ $invoice->student->roll_number ?? '' }}
                            &bull;
                            {{ $invoice->student->schoolClass->name ?? '' }}
                        </div>
                    </td>
                    <td>{{ $invoice->billing_period_label }}</td>
                    <td><strong>PKR {{ number_format($invoice->net_amount, 0) }}</strong></td>
                    <td style="color:var(--success);">
                        PKR {{ number_format($invoice->paid_amount, 0) }}
                    </td>
                    <td style="color:{{ $invoice->balance > 0 ? 'var(--danger)' : 'var(--success)' }};
                               font-weight:600;">
                        PKR {{ number_format($invoice->balance, 0) }}
                    </td>
                    <td>
                        {{ $invoice->due_date->format('d M') }}
                        @if($invoice->due_date->isPast() && $invoice->status === 'unpaid')
                            <div style="font-size:0.72rem; color:var(--danger); font-weight:700;">
                                OVERDUE
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $invoice->status_badge_class }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.fee.invoices.show', $invoice) }}"
                           class="btn-outline-primary btn btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9"
                        style="text-align:center; color:var(--text-muted); padding:3rem;">
                        <i class="fas fa-receipt"
                           style="font-size:3rem; display:block; margin-bottom:1rem; color:var(--border);"></i>
                        No invoices found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
    <div style="padding:1rem 1.4rem; border-top:1px solid var(--border);">
        {{ $invoices->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection