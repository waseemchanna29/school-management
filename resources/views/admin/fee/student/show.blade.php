@extends('layouts.app')
@section('title', 'Student Fee Profile')
@section('page-title', 'Student Fee Profile')

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
        <form method="GET">
            <select name="academic_year" class="form-select" style="min-width:140px;" onchange="this.form.submit()">
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

@php
    $activeFees   = $studentFees->where('is_active', true);
    $totalMonthly = $activeFees->filter(fn($f) => $f->feeStructure?->type === 'monthly')->sum('amount');
    $totalYearly  = $activeFees->filter(fn($f) => $f->feeStructure?->type === 'yearly')->sum('amount');
    $totalOneTime = $activeFees->filter(fn($f) => $f->feeStructure?->type === 'one_time')->sum('amount');
    $totalCustom  = $activeFees->whereNull('fee_structure_id')->sum('amount');
@endphp

<!-- Summary -->
<div class="fee-summary-bar">
    <div class="fee-summary-card total">
        <span class="fee-summary-amount">PKR {{ number_format($totalMonthly, 2) }}</span>
        <span class="fee-summary-label">Monthly Total</span>
    </div>
    <div class="fee-summary-card paid">
        <span class="fee-summary-amount">PKR {{ number_format($totalYearly, 2) }}</span>
        <span class="fee-summary-label">Annual Total</span>
    </div>
    <div class="fee-summary-card discount">
        <span class="fee-summary-amount">PKR {{ number_format($totalOneTime, 2) }}</span>
        <span class="fee-summary-label">One-Time Total</span>
    </div>
    <div class="fee-summary-card balance">
        <span class="fee-summary-amount">{{ $activeFees->count() }}</span>
        <span class="fee-summary-label">Active Fee Lines</span>
    </div>
</div>

<div class="row">
    <!-- Left: Fee Lines grouped by structure -->
    <div class="col-8">
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-list"></i> Fee Lines — {{ $academicYear }}
                </div>
            </div>

            @if($studentFees->isEmpty())
            <div style="padding:3rem; text-align:center; color:var(--text-muted);">
                <i class="fas fa-file-invoice"
                   style="font-size:3rem; display:block; margin-bottom:1rem; color:var(--border);"></i>
                No fee lines assigned for {{ $academicYear }}.
                <br>
                <span style="font-size:0.88rem;">Assign a fee structure from the right panel.</span>
            </div>
            @else
                @foreach($feesByStructure as $structureName => $fees)
                <div style="border-bottom:2px solid var(--border);">
                    <!-- Structure Group Header -->
                    <div style="padding:0.65rem 1.4rem; background:var(--light-bg);
                                display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:0.6rem;">
                            <i class="fas fa-folder" style="color:var(--accent);"></i>
                            <strong style="font-size:0.9rem; color:var(--primary);">{{ $structureName }}</strong>
                            @php $structureType = $fees->first()->feeStructure?->type; @endphp
                            @if($structureType)
                                <span class="badge {{ $fees->first()->feeStructure->type_badge_class }}"
                                      style="font-size:0.72rem;">
                                    {{ $fees->first()->feeStructure->type_label }}
                                </span>
                            @endif
                        </div>
                        <strong style="color:var(--primary); font-size:0.9rem;">
                            PKR {{ number_format($fees->where('is_active', true)->sum('amount'), 2) }}
                        </strong>
                    </div>

                    <!-- Fee Items in this structure group -->
                    @foreach($fees as $fee)
                    <div style="padding:0.75rem 1.4rem; border-bottom:1px solid var(--border);
                                display:flex; align-items:center; gap:1rem;
                                {{ !$fee->is_active ? 'opacity:0.5;' : '' }}">
                        <div style="flex:1;">
                            <div style="font-weight:600; font-size:0.9rem;">
                                {{ $fee->feeLabel->name ?? '—' }}
                                @if(!$fee->fee_structure_id)
                                    <span style="font-size:0.75rem; color:var(--accent);
                                                 font-style:italic; font-weight:400;">(custom)</span>
                                @endif
                            </div>
                            @if($fee->note)
                                <div style="font-size:0.77rem; color:var(--text-muted); margin-top:1px;">
                                    <i class="fas fa-info-circle"></i> {{ $fee->note }}
                                </div>
                            @endif
                        </div>
                        <div style="font-size:1rem; font-weight:700; color:var(--primary); min-width:110px; text-align:right;">
                            PKR {{ number_format($fee->amount, 2) }}
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn-outline-primary btn btn-sm"
                                    onclick="openEditFee({{ $fee->id }}, {{ $fee->amount }}, {{ $fee->is_active ? 'true' : 'false' }}, '{{ addslashes($fee->note ?? '') }}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.fee.student.destroy-fee', $fee) }}"
                                  method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-outline-danger btn btn-sm"
                                        onclick="return confirm('Remove this fee line from {{ addslashes($student->full_name) }}?')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            @endif
        </div>

        <!-- Invoices -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-receipt"></i> Invoices — {{ $academicYear }}</div>
                <a href="{{ route('admin.fee.invoices.create', ['student_id' => $student->id]) }}"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Generate Invoice
                </a>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Period</th>
                            <th>Type</th>
                            <th>Net Amount</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                        <tr>
                            <td><code style="font-size:0.81rem;">{{ $inv->invoice_number }}</code></td>
                            <td>{{ $inv->period_label }}</td>
                            <td>
                                <span class="badge badge-info" style="font-size:0.75rem;">
                                    {{ ucfirst($inv->period_type) }}
                                </span>
                            </td>
                            <td>PKR {{ number_format($inv->net_amount, 2) }}</td>
                            <td style="color:{{ $inv->balance > 0 ? 'var(--danger)' : 'var(--success)' }}; font-weight:600;">
                                PKR {{ number_format($inv->balance, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $inv->status_badge_class }}">
                                    {{ ucfirst($inv->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.fee.invoices.show', $inv) }}"
                                   class="btn-outline-primary btn btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center; color:var(--text-muted); padding:1.5rem;">
                                No invoices for {{ $academicYear }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Assign + Add Custom -->
    <div class="col-4">

        <!-- Assign Structure -->
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-file-import"></i> Assign Fee Structure
                </div>
            </div>
            <div class="card-body">
                @if($availableStructures->isEmpty())
                    <div class="alert alert-warning" style="margin:0;">
                        <i class="fas fa-exclamation-triangle"></i>
                        No active structures for
                        <strong>{{ $student->schoolClass->name ?? 'this class' }}</strong>.
                        <a href="{{ route('admin.fee.structures.create') }}">Create one.</a>
                    </div>
                @else
                    <form action="{{ route('admin.fee.student.assign', $student) }}" method="POST">
                        @csrf
                        <div class="mb-form">
                            <label class="form-label">Select Structure *</label>
                            <select name="fee_structure_id" class="form-select" id="struct_select"
                                    onchange="showStructurePreview()">
                                <option value="">-- Pick a Structure --</option>
                                @foreach($availableStructures as $structure)
                                    <option value="{{ $structure->id }}"
                                            data-type="{{ $structure->type_label }}"
                                            data-total="{{ number_format($structure->total, 2) }}"
                                            data-items="{{ $structure->items->map(fn($i) => $i->feeLabel->name . ': PKR ' . number_format($i->amount,2))->join(' | ') }}">
                                        {{ $structure->name }}
                                        ({{ $structure->type_label }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Preview box -->
                        <div id="struct-preview"
                             style="display:none; background:var(--light-bg); border:1.5px solid var(--border);
                                    border-radius:var(--radius-sm); padding:0.8rem 1rem; margin-bottom:1rem;
                                    font-size:0.82rem; color:var(--text-dark); line-height:1.7;">
                        </div>

                        <div class="mb-form">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year" class="form-select">
                                @foreach($years as $year)
                                    <option value="{{ $year }}"
                                            {{ $year === $academicYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn-block btn btn-primary"
                                onclick="return confirm('Assign this structure to {{ addslashes($student->full_name) }}? Existing lines from the same structure will be updated.')">
                            <i class="fas fa-file-import"></i> Assign Structure
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Add Custom Fee Line -->
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-plus-circle"></i> Add Custom Fee Line
                </div>
            </div>
            <div class="card-body">
                <p style="font-size:0.83rem; color:var(--text-muted); margin-bottom:1rem;">
                    Add a one-off fee line directly to this student — not linked to any structure.
                </p>
                <form action="{{ route('admin.fee.student.add-fee', $student) }}" method="POST">
                    @csrf
                    <div class="mb-form">
                        <label class="form-label">Fee Label *</label>
                        <select name="fee_label_id" class="form-select">
                            <option value="">-- Select Label --</option>
                            @foreach($labels as $label)
                                <option value="{{ $label->id }}">{{ $label->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Amount (PKR) *</label>
                        <input type="number" name="amount" class="form-control"
                               placeholder="0.00" min="0" step="0.01">
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year" class="form-select">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $year === $academicYear ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Note / Reason</label>
                        <input type="text" name="note" class="form-control"
                               placeholder="e.g. late fee, scholarship waiver...">
                    </div>
                    <button type="submit" class="btn-block btn btn-success">
                        <i class="fas fa-plus"></i> Add Custom Fee
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Fee Modal -->
<div id="edit-fee-modal"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
            z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius); padding:1.8rem;
                width:100%; max-width:420px; box-shadow:var(--shadow-md);">
        <h3 style="font-family:var(--font-display); color:var(--primary); margin-bottom:0.8rem;">
            Edit Fee Line
        </h3>
        <div class="alert alert-info" style="margin-bottom:1rem;">
            <i class="fas fa-info-circle"></i>
            This change only affects <strong>{{ $student->full_name }}</strong>.
        </div>
        <form id="edit-fee-form" method="POST" novalidate>
            @csrf @method('PUT')
            <div class="mb-form">
                <label class="form-label">Amount (PKR) *</label>
                <input type="number" name="amount" id="edit-fee-amount"
                       class="form-control" min="0" step="0.01">
            </div>
            <div class="mb-form"
                 style="display:flex; align-items:center; gap:10px; padding:0.7rem;
                        background:var(--light-bg); border-radius:var(--radius-sm);">
                <input type="checkbox" name="is_active" id="edit-fee-active" value="1"
                       style="width:17px; height:17px; accent-color:var(--primary); cursor:pointer;">
                <label for="edit-fee-active" style="cursor:pointer; margin:0; font-weight:600;">
                    Fee Line is Active
                </label>
            </div>
            <div class="mb-form">
                <label class="form-label">Note / Reason</label>
                <input type="text" name="note" id="edit-fee-note"
                       class="form-control" placeholder="e.g. scholarship, special concession">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save
                </button>
                <button type="button" class="btn-outline-secondary btn"
                        onclick="document.getElementById('edit-fee-modal').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditFee(id, amount, isActive, note) {
    document.getElementById('edit-fee-form').action = '/admin/fee/student-fees/' + id;
    document.getElementById('edit-fee-amount').value  = amount;
    document.getElementById('edit-fee-active').checked = isActive;
    document.getElementById('edit-fee-note').value   = note;
    document.getElementById('edit-fee-modal').style.display = 'flex';
}

function showStructurePreview() {
    const sel     = document.getElementById('struct_select');
    const opt     = sel.options[sel.selectedIndex];
    const preview = document.getElementById('struct-preview');

    if (!sel.value) {
        preview.style.display = 'none';
        return;
    }

    const items = opt.dataset.items.split(' | ').map(i => `<li>${i}</li>`).join('');
    preview.innerHTML = `
        <strong style="color:var(--primary);">${opt.text}</strong><br>
        <span style="color:var(--text-muted);">Type: ${opt.dataset.type}</span><br>
        <ul style="margin:0.4rem 0 0 1rem; padding:0;">${items}</ul>
        <div style="margin-top:0.5rem; font-weight:700; color:var(--primary);">
            Total: PKR ${opt.dataset.total}
        </div>`;
    preview.style.display = 'block';
}
</script>
@endsection