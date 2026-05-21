@extends('layouts.app')
@section('title', 'Fee Structure')
@section('page-title', 'Fee Structure')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">{{ $structure->name }}</div>
        <div class="page-header-sub">
            <span class="badge {{ $structure->type_badge_class }}">{{ $structure->type_label }}</span>
            &bull; {{ $structure->schoolClass->name ?? '—' }}
            &bull; {{ $structure->academic_year }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.fee.structures.edit', $structure) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <form action="{{ route('admin.fee.structures.revise', $structure) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm"
                    onclick="return confirm('Create next-year revision of this structure?')">
                <i class="fas fa-copy"></i> Revise for Next Year
            </button>
        </form>
        <a href="{{ route('admin.fee.structures.index') }}" class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

<!-- Totals -->
<div class="fee-summary-bar" style="grid-template-columns: repeat(3, 1fr);">
    <div class="fee-summary-card total">
        <span class="fee-summary-amount">PKR {{ number_format($structure->total, 2) }}</span>
        <span class="fee-summary-label">Structure Total</span>
    </div>
    <div class="fee-summary-card paid">
        <span class="fee-summary-amount">{{ $structure->items->where('is_active', true)->count() }}</span>
        <span class="fee-summary-label">Active Items</span>
    </div>
    <div class="fee-summary-card discount">
        <span class="fee-summary-amount">
            <span class="badge {{ $structure->type_badge_class }}" style="font-size:0.9rem; padding:0.4rem 0.9rem;">
                {{ $structure->type_label }}
            </span>
        </span>
        <span class="fee-summary-label">Billing Type</span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-list"></i> Fee Items</div>
        @if($structure->notes)
            <span style="font-size:0.83rem; color:var(--text-muted);">{{ $structure->notes }}</span>
        @endif
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Fee Label</th><th>Amount</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($structure->items as $i => $item)
                <tr style="{{ !$item->is_active ? 'opacity:0.5;' : '' }}">
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $item->feeLabel->name ?? '—' }}</strong></td>
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
                <tr>
                    <td colspan="4" style="text-align:center; color:var(--text-muted); padding:1.5rem;">
                        No items. <a href="{{ route('admin.fee.structures.edit', $structure) }}">Add items.</a>
                    </td>
                </tr>
                @endforelse
                @if($structure->items->isNotEmpty())
                <tr style="background:var(--primary);">
                    <td colspan="2" style="text-align:right; font-weight:700; color:var(--white); padding:0.8rem 1rem;">
                        Total
                    </td>
                    <td style="font-weight:700; color:var(--accent-light); padding:0.8rem 1rem; font-size:1.05rem;">
                        PKR {{ number_format($structure->total, 2) }}
                    </td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection