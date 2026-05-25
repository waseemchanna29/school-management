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
    <div class="d-flex gap-2">
        <a href="{{ route('admin.fee.invoices.create', ['student_id' => $student->id]) }}"
           class="btn btn-primary btn-sm">
            <i class="fas fa-receipt"></i> Generate Invoice
        </a>
        <a href="{{ route('admin.students.show', $student) }}"
           class="btn-outline-secondary btn btn-sm">
            <i class="fas fa-user"></i> Profile
        </a>
    </div>
</div>

<div class="row">

    <!-- LEFT: Current Fee Lines -->
    <div class="col-8">

        <!-- Current Assignment -->
        @if($assignment)
        <div class="mb-2 card"
             style="border-left:4px solid var(--primary);">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-file-invoice-dollar"></i>
                    Assigned: {{ $assignment->feeScheduler->name ?? '—' }}
                </div>
                <div class="d-flex gap-2">
                    <span style="font-size:0.82rem; color:var(--text-muted);">
                        Since {{ $assignment->assigned_date->format('d M, Y') }}
                    </span>
                    <form action="{{ route('admin.fee.student.unassign', $student) }}"
                          method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-outline-danger btn btn-sm"
                                onclick="return confirm('Remove scheduler from this student?')">
                            <i class="fas fa-unlink"></i> Unassign
                        </button>
                    </form>
                </div>
            </div>

            <!-- Personal fee lines -->
            <div>
                @forelse($items as $item)
                <div class="fee-item-line {{ !$item->is_active ? 'opacity-50' : '' }}"
                     style="{{ !$item->is_active ? 'opacity:0.5;' : '' }}">
                    <div style="flex:1;">
                        <div class="fee-item-line-label">
                            {{ $item->label }}
                            @if(!$item->is_active)
                                <span style="font-size:0.74rem; color:var(--danger);">(inactive)</span>
                            @endif
                        </div>
                        @if($item->note)
                            <div class="fee-item-line-note">{{ $item->note }}</div>
                        @endif
                    </div>
                    <div class="fee-item-line-amount">
                        PKR {{ number_format($item->amount, 0) }}
                    </div>
                    <button class="btn-outline-primary btn btn-sm"
                            onclick="openEditItem(
                                {{ $item->id }},
                                '{{ addslashes($item->label) }}',
                                {{ $item->amount }},
                                {{ $item->is_active ? 'true' : 'false' }},
                                '{{ addslashes($item->note ?? '') }}'
                            )">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('admin.fee.student.remove-item', $item) }}"
                          method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-outline-danger btn btn-sm"
                                onclick="return confirm('Remove this fee line?')">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>
                @empty
                <div style="padding:1.5rem; text-align:center; color:var(--text-muted);">
                    No fee items. Add custom items on the right.
                </div>
                @endforelse

                @if($items->where('is_active', true)->count())
                <div class="fee-item-line"
                     style="background:var(--light-bg); font-weight:700;">
                    <div style="flex:1; color:var(--primary);">Monthly Total</div>
                    <div style="font-size:1rem; font-weight:700; color:var(--primary);">
                        PKR {{ number_format($items->where('is_active', true)->sum('amount'), 0) }}
                    </div>
                    <div style="width:70px;"></div>
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            No fee scheduler assigned to this student. Use the panel on the right to assign one.
        </div>
        @endif

        <!-- Invoice history -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-receipt"></i> Invoice History
                </div>
                <a href="{{ route('admin.fee.invoices.create', ['student_id' => $student->id]) }}"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Invoice
                </a>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Period</th>
                            <th>Net Amount</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                        <tr>
                            <td>
                                <code style="font-size:0.8rem;">{{ $inv->invoice_number }}</code>
                            </td>
                            <td>{{ $inv->billing_period_label }}</td>
                            <td>PKR {{ number_format($inv->net_amount, 0) }}</td>
                            <td style="color:var(--success);">
                                PKR {{ number_format($inv->paid_amount, 0) }}
                            </td>
                            <td style="color:{{ $inv->balance > 0 ? 'var(--danger)' : 'var(--success)' }};
                                       font-weight:600;">
                                PKR {{ number_format($inv->balance, 0) }}
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
                            <td colspan="7"
                                style="text-align:center; color:var(--text-muted); padding:1.5rem;">
                                No invoices yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($invoices->hasPages())
            <div style="padding:0.8rem 1.2rem; border-top:1px solid var(--border);">
                {{ $invoices->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- RIGHT: Assign & Add Custom -->
    <div class="col-4">

        <!-- Assign Scheduler -->
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-link"></i>
                    {{ $assignment ? 'Change Scheduler' : 'Assign Scheduler' }}
                </div>
            </div>
            <div class="card-body">
                @if($schedulers->isEmpty())
                    <div class="alert alert-warning" style="margin:0;">
                        <i class="fas fa-exclamation-triangle"></i>
                        No active schedulers.
                        <a href="{{ route('admin.fee.schedulers.create') }}">Create one.</a>
                    </div>
                @else
                    @if($assignment)
                    <div class="alert alert-info" style="margin-bottom:1rem;">
                        <i class="fas fa-info-circle"></i>
                        Reassigning will replace current fee lines.
                    </div>
                    @endif
                    <form action="{{ route('admin.fee.student.assign', $student) }}" method="POST">
                        @csrf
                        <div class="mb-form">
                            <label class="form-label">Scheduler *</label>
                            <select name="fee_scheduler_id" class="form-select"
                                    id="scheduler-select" onchange="showPreview()">
                                <option value="">-- Select --</option>
                                @foreach($schedulers as $sched)
                                    <option value="{{ $sched->id }}"
                                            data-total="{{ number_format($sched->total, 0) }}"
                                            data-items="{{ $sched->items->map(fn($i) => $i->label.': PKR '.number_format($i->amount,0))->join(' | ') }}"
                                            {{ $assignment?->fee_scheduler_id == $sched->id ? 'selected' : '' }}>
                                        {{ $sched->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Preview -->
                        <div id="sched-preview"
                             style="display:none; font-size:0.8rem; background:var(--light-bg);
                                    border-radius:var(--radius-sm); padding:0.7rem 0.9rem;
                                    margin-bottom:1rem; color:var(--text-dark); line-height:1.8;">
                        </div>

                        <div class="mb-form">
                            <label class="form-label">Assign Date *</label>
                            <input type="date" name="assigned_date" class="form-control"
                                   value="{{ date('Y-m-d') }}">
                        </div>

                        <button type="submit" class="btn-block btn btn-primary"
                                onclick="return confirm('This will replace the current fee lines for this student.')">
                            <i class="fas fa-link"></i>
                            {{ $assignment ? 'Reassign' : 'Assign' }} Scheduler
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Add Custom Item -->
        @if($assignment)
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-plus-circle"></i> Add Custom Fee Line
                </div>
            </div>
            <div class="card-body">
                <p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:0.9rem;">
                    Add a one-off fee line specific to this student only.
                </p>
                <form action="{{ route('admin.fee.student.add-item', $student) }}" method="POST">
                    @csrf
                    <div class="mb-form">
                        <label class="form-label">Label *</label>
                        <input type="text" name="label" class="form-control"
                               placeholder="e.g. Late Fee, Transport, Uniform">
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Amount (PKR) *</label>
                        <input type="number" name="amount" class="form-control"
                               placeholder="0" min="0" step="0.01">
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Note (optional)</label>
                        <input type="text" name="note" class="form-control"
                               placeholder="Reason for this item">
                    </div>
                    <button type="submit" class="btn-block btn btn-success">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Edit Item Modal -->
<div id="edit-item-modal"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
            z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius); padding:1.8rem;
                width:100%; max-width:400px; box-shadow:var(--shadow-md);">
        <h3 style="font-family:var(--font-display); color:var(--primary); margin-bottom:0.7rem;">
            Edit Fee Item
        </h3>
        <div class="alert alert-info" style="margin-bottom:1rem; font-size:0.82rem;">
            <i class="fas fa-info-circle"></i>
            This change only affects <strong>{{ $student->full_name }}</strong>.
        </div>
        <form id="edit-item-form" method="POST" novalidate>
            @csrf @method('PUT')
            <div class="mb-form">
                <label class="form-label">Label *</label>
                <input type="text" name="label" id="ei-label" class="form-control">
            </div>
            <div class="mb-form">
                <label class="form-label">Amount (PKR) *</label>
                <input type="number" name="amount" id="ei-amount"
                       class="form-control" min="0" step="0.01">
            </div>
            <div style="display:flex; align-items:center; gap:10px; padding:0.6rem;
                        background:var(--light-bg); border-radius:var(--radius-sm);
                        margin-bottom:0.9rem;">
                <input type="checkbox" name="is_active" id="ei-active" value="1"
                       style="width:16px; height:16px; accent-color:var(--primary);">
                <label for="ei-active" style="cursor:pointer; margin:0; font-weight:600; font-size:0.88rem;">
                    Item is Active
                </label>
            </div>
            <div class="mb-form">
                <label class="form-label">Note</label>
                <input type="text" name="note" id="ei-note" class="form-control">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save
                </button>
                <button type="button" class="btn-outline-secondary btn"
                        onclick="document.getElementById('edit-item-modal').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditItem(id, label, amount, isActive, note) {
    document.getElementById('edit-item-form').action = '/admin/fee/student-item/' + id;
    document.getElementById('ei-label').value  = label;
    document.getElementById('ei-amount').value = amount;
    document.getElementById('ei-active').checked = isActive;
    document.getElementById('ei-note').value   = note;
    document.getElementById('edit-item-modal').style.display = 'flex';
}

function showPreview() {
    const sel     = document.getElementById('scheduler-select');
    const opt     = sel.options[sel.selectedIndex];
    const preview = document.getElementById('sched-preview');

    if (!sel.value) { preview.style.display = 'none'; return; }

    const items = opt.dataset.items.split(' | ').map(i => `<div>${i}</div>`).join('');
    preview.innerHTML = items +
        `<div style="font-weight:700; color:var(--primary); margin-top:4px; border-top:1px dashed var(--border); padding-top:4px;">
            Total: PKR ${opt.dataset.total}
         </div>`;
    preview.style.display = 'block';
}

// Init preview if scheduler already selected
document.addEventListener('DOMContentLoaded', showPreview);
</script>
@endsection