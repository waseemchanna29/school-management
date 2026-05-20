@extends('layouts.app')
@section('title', 'Fee Invoices')
@section('page-title', 'Fee Invoices')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Fee Invoices</div>
        <div class="page-header-sub">All generated invoices across students</div>
    </div>
    <a href="{{ route('admin.fee.invoices.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> Generate Invoice
    </a>
</div>

<form method="GET">
    <div class="filter-bar">
        <div>
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control"
                   placeholder="Invoice # or student..." value="{{ request('search') }}">
        </div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach(['unpaid','partial','paid','waived'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Type</label>
            <select name="period_type" class="form-select">
                <option value="">All Types</option>
                <option value="monthly"  {{ request('period_type') === 'monthly'  ? 'selected' : '' }}>Monthly</option>
                <option value="yearly"   {{ request('period_type') === 'yearly'   ? 'selected' : '' }}>Yearly</option>
                <option value="one_time" {{ request('period_type') === 'one_time' ? 'selected' : '' }}>One-Time</option>
            </select>
        </div>
        <div>
            <label class="form-label">Academic Year</label>
            <select name="academic_year" class="form-select">
                <option value="">All Years</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ request('academic_year') === $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.fee.invoices.index') }}" class="btn-outline-secondary btn">Clear</a>
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
                    <th>Due Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td><code style="font-size:0.82rem;">{{ $invoice->invoice_number }}</code></td>
                    <td>
                        <strong>{{ $invoice->student->full_name ?? '—' }}</strong>
                        <div style="font-size:0.77rem; color:var(--text-muted);">{{ $invoice->student->roll_number ?? '' }}</div>
                    </td>
                    <td>
                        {{ $invoice->period_label }}
                        <div style="font-size:0.77rem; color:var(--text-muted);">{{ $invoice->academic_year }}</div>
                    </td>
                    <td><strong>PKR {{ number_format($invoice->net_amount, 2) }}</strong></td>
                    <td style="color:var(--success);">PKR {{ number_format($invoice->paid_amount, 2) }}</td>
                    <td style="color:{{ $invoice->balance > 0 ? 'var(--danger)' : 'var(--success)' }}; font-weight:600;">
                        PKR {{ number_format($invoice->balance, 2) }}
                    </td>
                    <td>
                        {{ $invoice->due_date->format('d M, Y') }}
                        @if($invoice->due_date->isPast() && $invoice->status === 'unpaid')
                            <div style="font-size:0.74rem; color:var(--danger); font-weight:600;">OVERDUE</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $invoice->status_badge_class }}">{{ ucfirst($invoice->status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.fee.invoices.show', $invoice) }}" class="btn-outline-primary btn btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center; color:var(--text-muted); padding:2.5rem;">No invoices found.</td></tr>
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