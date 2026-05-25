@extends('layouts.app')
@section('title', 'Edit Scheduler')
@section('page-title', 'Edit Scheduler')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Edit: {{ $scheduler->name }}</div>
        <div class="page-header-sub">
            Changes here affect the template only — existing student fee lines are not changed
        </div>
    </div>
    <a href="{{ route('admin.fee.schedulers.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('admin.fee.schedulers.update', $scheduler) }}" method="POST" novalidate>
@csrf @method('PUT')

<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-tag"></i> Scheduler Details</div>
        <div class="row">
            <div class="mb-form col-8">
                <label class="form-label">Scheduler Name *</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $scheduler->name) }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control"
                       value="{{ old('description', $scheduler->description) }}">
            </div>
        </div>
    </div>
</div>

<div class="mb-2 card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-list"></i> Fee Items</div>
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Editing items here <strong>does not</strong> update fee lines already assigned to students.
            To update a student's lines, go to their fee profile.
        </div>

        <div id="items-container">
            @foreach($scheduler->items as $i => $item)
            <div class="repeater-item" id="item-{{ $i }}">
                <div class="repeater-item-header">
                    <span class="repeater-item-label">{{ $item->label }}</span>
                    <button type="button" class="btn-remove-repeater"
                            onclick="removeItem('item-{{ $i }}')">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </div>
                <div class="row">
                    <div class="mb-form col-8">
                        <label class="form-label">Label *</label>
                        <input type="text" name="items[{{ $i }}][label]" class="form-control"
                               value="{{ old("items.$i.label", $item->label) }}">
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Amount (PKR) *</label>
                        <input type="number" name="items[{{ $i }}][amount]"
                               class="form-control amount-input"
                               value="{{ old("items.$i.amount", $item->amount) }}"
                               min="0" step="0.01" oninput="recalc()">
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <button type="button" class="btn-add-repeater" onclick="addItem()">
            <i class="fas fa-plus-circle"></i> Add Item
        </button>

        <div style="margin-top:1.2rem; padding:0.9rem 1.2rem; background:var(--light-bg);
                    border-radius:var(--radius-sm); display:flex; justify-content:space-between;">
            <span style="font-weight:600; color:var(--primary);">Total:</span>
            <span id="total-display" style="font-size:1.3rem; font-weight:700; color:var(--primary);">
                PKR {{ number_format($scheduler->total, 0) }}
            </span>
        </div>
    </div>
</div>

<div style="display:flex; gap:0.8rem;">
    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save</button>
    <a href="{{ route('admin.fee.schedulers.index') }}" class="btn-outline-secondary btn btn-lg">Cancel</a>
</div>
</form>

<script>
let count = {{ $scheduler->items->count() }};

function addItem() {
    const idx  = count++;
    const html = `
    <div class="repeater-item" id="item-${idx}">
        <div class="repeater-item-header">
            <span class="repeater-item-label">New Item</span>
            <button type="button" class="btn-remove-repeater" onclick="removeItem('item-${idx}')">
                <i class="fas fa-trash-alt"></i> Remove
            </button>
        </div>
        <div class="row">
            <div class="mb-form col-8">
                <label class="form-label">Label *</label>
                <input type="text" name="items[${idx}][label]" class="form-control">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Amount (PKR) *</label>
                <input type="number" name="items[${idx}][amount]"
                       class="form-control amount-input"
                       placeholder="0" min="0" oninput="recalc()">
            </div>
        </div>
    </div>`;
    document.getElementById('items-container').insertAdjacentHTML('beforeend', html);
}

function removeItem(id) {
    document.getElementById(id).remove();
    recalc();
}

function recalc() {
    let total = 0;
    document.querySelectorAll('.amount-input').forEach(i => total += parseFloat(i.value) || 0);
    document.getElementById('total-display').textContent =
        'PKR ' + total.toLocaleString('en-PK', { minimumFractionDigits: 0 });
}
</script>
@endsection