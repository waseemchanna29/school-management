@extends('layouts.app')
@section('title', 'Student Fee')
@section('page-title', 'Student Fee')

@section('content')
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="profile-avatar-placeholder" style="width:52px; height:52px; font-size:1.3rem;">
            {{ strtoupper(substr($student->full_name, 0, 1)) }}
        </div>
        <div>
            <div class="page-header-title">{{ $student->full_name }}</div>
            <div class="page-header-sub">
                <code>{{ $student->roll_number }}</code> &bull;
                {{ $student->schoolClass->name ?? '' }}
                @if($student->section) — Section {{ $student->section->name }} @endif
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- Year Switcher -->
        <form method="GET" style="display:flex; gap:0.5rem; align-items:center;">
            <select name="academic_year" class="form-select" style="min-width:130px;" onchange="this.form.submit()">
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ $academicYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('admin.students.show', $student) }}" class="btn-outline-secondary btn btn-sm">
            <i class="fas fa-user"></i> Profile
        </a>
    </div>
</div>

<!-- Summary -->
@php
    $activeFees   = $studentFees->where('is_active', true);
    $monthlyTotal = $activeFees->filter(fn($f) => $f->feeLabel?->frequency === 'monthly')->sum('amount');
    $yearlyTotal  = $activeFees->filter(fn($f) => $f->feeLabel?->frequency === 'yearly')->sum('amount');
    $oneTimeTotal = $activeFees->filter(fn($f) => $f->feeLabel?->frequency === 'one_time')->sum('amount');
@endphp
<div class="fee-summary-bar">
    <div class="fee-summary-card total">
        <span class="fee-summary-amount">PKR {{ number_format($monthlyTotal, 2) }}</span>
        <span class="fee-summary-label">Monthly Fees</span>
    </div>
    <div class="fee-summary-card paid">
        <span class="fee-summary-amount">PKR {{ number_format($yearlyTotal, 2) }}</span>
        <span class="fee-summary-label">Annual Fees</span>
    </div>
    <div class="fee-summary-card discount">
        <span class="fee-summary-amount">PKR {{ number_format($oneTimeTotal, 2) }}</span>
        <span class="fee-summary-label">One-Time Fees</span>
    </div>
    <div class="fee-summary-card balance">
        <span class="fee-summary-amount">{{ $activeFees->count() }}</span>
        <span class="fee-summary-label">Active Fee Lines</span>
    </div>
</div>

<div class="row">
    <div class="col-8">
        <!-- Student Fee Lines -->
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-list"></i> Fee Lines — {{ $academicYear }}
                </div>
            </div>
            <div class="card-body" style="padding:0;">
                @forelse($studentFees as $fee)
                <div class="fee-item-row" style="padding:0.9rem 1.4rem; {{ !$fee->is_active ? 'opacity:0.5;' : '' }}">
                    <div style="flex:1;">
                        <div class="fee-item-label">
                            {{ $fee->feeLabel->name ?? '—' }}
                            @if($fee->fee_structure_item_id === null || $fee->note)
                                <span class="fee-item-custom">(custom)</span>
                            @endif
                        </div>
                        <div class="fee-item-freq">
                            @if($fee->feeLabel)
                                <span class="badge {{ $fee->feeLabel->frequency_badge_class }}" style="font-size:0.72rem;">
                                    {{ $fee->feeLabel->frequency_label }}
                                </span>
                            @endif
                            @if($fee->note)
                                &bull; <em style="font-size:0.78rem; color:var(--text-muted);">{{ $fee->note }}</em>
                            @endif
                        </div>
                    </div>
                    <div class="fee-item-amount">PKR {{ number_format($fee->amount, 2) }}</div>
                    <div style="display:flex; gap:0.4rem;">
                        <button class="btn-outline-primary btn btn-sm"
                                onclick="openEditFee({{ $fee->id }}, {{ $fee->amount }}, '{{ $fee->is_active ? '1' : '0' }}', '{{ addslashes($fee->note ?? '') }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('admin.fee.student.destroy-fee', $fee) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-outline-danger btn btn-sm"
                                    onclick="return confirm('Remove this fee from student?')">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div style="padding:2.5rem; text-align:center; color:var(--text-muted);">
                    <i class="fas fa-file-invoice" style="font-size:2.5rem; display:block; margin-bottom:0.8rem;"></i>
                    No fees assigned for {{ $academicYear }}.
                    Assign a structure or add fees manually.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Invoices for this student -->
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-receipt"></i> Invoices</div>
                <a href="{{ route('admin.fee.invoices.create', ['student_id' => $student->id]) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Generate Invoice
                </a>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr><th>Invoice</th><th>Period</th><th>Net Amount</th><th>Paid</th><th>Balance</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @php
                            $invoices = \App\Models\FeeInvoice::where('student_id', $student->id)
                                ->where('academic_year', $academicYear)->latest()->get();
                        @endphp
                        @forelse($invoices as $inv)
                        <tr>
                            <td><code style="font-size:0.82rem;">{{ $inv->invoice_number }}</code></td>
                            <td>{{ $inv->period_label }}</td>
                            <td>PKR {{ number_format($inv->net_amount, 2) }}</td>
                            <td style="color:var(--success);">PKR {{ number_format($inv->paid_amount, 2) }}</td>
                            <td style="color:var(--danger);">PKR {{ number_format($inv->balance, 2) }}</td>
                            <td>
                                <span class="badge {{ $inv->status_badge_class }}">{{ ucfirst($inv->status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.fee.invoices.show', $inv) }}" class="btn-outline-primary btn btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" style="text-align:center; color:var(--text-muted); padding:1.5rem;">No invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-4">
        <!-- Assign Structure -->
        @if($structures->isNotEmpty())
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-file-import"></i> Assign Structure</div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.fee.student.assign', $student) }}" method="POST">
                    @csrf
                    <div class="mb-form">
                        <label class="form-label">Fee Structure</label>
                        <select name="fee_structure_id" class="form-select">
                            @foreach($structures as $structure)
                                <option value="{{ $structure->id }}">
                                    {{ $structure->academic_year }} — {{ $structure->items->count() }} items
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year" class="form-select">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $year === $academicYear ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-block btn btn-primary"
                            onclick="return confirm('This will assign/overwrite fees from the selected structure to this student. Individual edits will be preserved if labels match.')">
                        <i class="fas fa-file-import"></i> Assign Structure
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Add Custom Fee -->
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-plus-circle"></i> Add Custom Fee</div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.fee.student.add-fee', $student) }}" method="POST">
                    @csrf
                    <div class="mb-form">
                        <label class="form-label">Fee Label *</label>
                        <select name="fee_label_id" class="form-select">
                            @foreach($labels as $label)
                                <option value="{{ $label->id }}">{{ $label->name }} ({{ $label->frequency_label }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Amount (PKR) *</label>
                        <input type="number" name="amount" class="form-control" placeholder="0.00" min="0" step="0.01">
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year" class="form-select">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $year === $academicYear ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Note (reason)</label>
                        <input type="text" name="note" class="form-control" placeholder="e.g. Scholarship discount">
                    </div>
                    <button type="submit" class="btn-block btn btn-success">
                        <i class="fas fa-plus"></i> Add Fee
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Fee Modal -->
<div id="edit-fee-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius); padding:1.8rem; width:100%; max-width:420px; box-shadow:var(--shadow-md);">
        <h3 style="font-family:var(--font-display); color:var(--primary); margin-bottom:1.2rem;">Edit Student Fee</h3>
        <div class="alert alert-info" style="margin-bottom:1rem;">
            <i class="fas fa-info-circle"></i>
            This change only affects <strong>{{ $student->full_name }}</strong>.
        </div>
        <form id="edit-fee-form" method="POST" novalidate>
            @csrf @method('PUT')
            <div class="mb-form">
                <label class="form-label">Amount (PKR) *</label>
                <input type="number" name="amount" id="edit-fee-amount" class="form-control" min="0" step="0.01">
            </div>
            <div class="mb-form" style="display:flex; align-items:center; gap:10px; padding:0.7rem; background:var(--light-bg); border-radius:var(--radius-sm);">
                <input type="checkbox" name="is_active" id="edit-fee-active" value="1"
                       style="width:17px; height:17px; accent-color:var(--primary); cursor:pointer;">
                <label for="edit-fee-active" style="cursor:pointer; margin:0; font-weight:600;">Fee is Active</label>
            </div>
            <div class="mb-form">
                <label class="form-label">Note / Reason</label>
                <input type="text" name="note" id="edit-fee-note" class="form-control" placeholder="e.g. scholarship, special case">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                <button type="button" class="btn-outline-secondary btn"
                        onclick="document.getElementById('edit-fee-modal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditFee(id, amount, isActive, note) {
    document.getElementById('edit-fee-form').action = '/admin/fee/student-fees/' + id;
    document.getElementById('edit-fee-amount').value = amount;
    document.getElementById('edit-fee-active').checked = isActive === '1';
    document.getElementById('edit-fee-note').value = note;
    document.getElementById('edit-fee-modal').style.display = 'flex';
}
</script>
@endsection