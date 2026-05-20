@extends('layouts.app')
@section('title', 'Fee Structure')
@section('page-title', 'Fee Structure')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">{{ $structure->schoolClass->name ?? '' }} — {{ $structure->academic_year }}</div>
        <div class="page-header-sub">Fee structure details and items</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.fee.structures.edit', $structure) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <form action="{{ route('admin.fee.structures.revise', $structure) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm"
                    onclick="return confirm('Create next-year revision copy?')">
                <i class="fas fa-copy"></i> Revise for Next Year
            </button>
        </form>
        <a href="{{ route('admin.fee.structures.index') }}" class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

<!-- Summary -->
<div class="fee-summary-bar" style="grid-template-columns: repeat(4, 1fr);">
    <div class="fee-summary-card total">
        <span class="fee-summary-amount">PKR {{ number_format($structure->items->sum('amount'), 2) }}</span>
        <span class="fee-summary-label">Total (All Items)</span>
    </div>
    <div class="fee-summary-card paid">
        <span class="fee-summary-amount">PKR {{ number_format($structure->total_monthly, 2) }}</span>
        <span class="fee-summary-label">Monthly Recurring</span>
    </div>
    <div class="fee-summary-card discount">
        <span class="fee-summary-amount">PKR {{ number_format($structure->items->where('is_active', true)->filter(fn($i) => $i->feeLabel?->frequency === 'yearly')->sum('amount'), 2) }}</span>
        <span class="fee-summary-label">Annual Amount</span>
    </div>
    <div class="fee-summary-card balance">
        <span class="fee-summary-amount">{{ $structure->items->where('is_active', true)->count() }}</span>
        <span class="fee-summary-label">Active Fee Items</span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-list"></i> Fee Items</div>
        @if($structure->notes)
        <span style="font-size:0.83rem; color:var(--text-muted);">Note: {{ $structure->notes }}</span>
        @endif
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Fee Label</th><th>Frequency</th><th>Amount</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($structure->items as $item)
                <tr style="{{ !$item->is_active ? 'opacity:0.5;' : '' }}">
                    <td><strong>{{ $item->feeLabel->name ?? '—' }}</strong></td>
                    <td>
                        @if($item->feeLabel)
                        <span class="badge {{ $item->feeLabel->frequency_badge_class }}">
                            {{ $item->feeLabel->frequency_label }}
                        </span>
                        @endif
                    </td>
                    <td>
                        <strong style="font-size:1rem; color:var(--primary);">
                            PKR {{ number_format($item->amount, 2) }}
                        </strong>
                    </td>
                    <td>
                        <span class="badge {{ $item->is_active ? 'badge-approved' : 'badge-rejected' }}">
                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; color:var(--text-muted);">No items.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection