@extends('layouts.app')
@section('title', 'New Scheduler')
@section('page-title', 'New Fee Scheduler')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">New Fee Scheduler</div>
        <div class="page-header-sub">Define a fee template with labels and amounts</div>
    </div>
    <a href="{{ route('admin.fee.schedulers.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('admin.fee.schedulers.store') }}" method="POST" novalidate>
@csrf

<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-tag"></i> Scheduler Details</div>
        <div class="row">
            <div class="mb-form col-8">
                <label class="form-label">Scheduler Name *</label>
                <input type="text" name="name"
                       class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       value="{{ old('name') }}"
                       placeholder="e.g. Class 9 Monthly Fee, KG Annual Fee">
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Description (optional)</label>
                <input type="text" name="description" class="form-control"
                       value="{{ old('description') }}" placeholder="Short note">
            </div>
        </div>
    </div>
</div>

<div class="mb-2 card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-list"></i> Fee Items</div>
        <span style="font-size:0.82rem; color:var(--text-muted);">
            Each item = one fee line on the student's invoice
        </span>
    </div>
    <div class="card-body">

        @error('items')
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> {{ $message }}
            </div>
        @enderror

        <div id="items-container">
            @if(old('items'))
                @foreach(old('items') as $i => $item)
                <div class="repeater-item" id="item-{{ $i }}">
                    <div class="repeater-item-header">
                        <span class="repeater-item-label">Item #{{ $i + 1 }}</span>
                        @if($i > 0)
                        <button type="button" class="btn-remove-repeater"
                                onclick="removeItem('item-{{ $i }}')">
                            <i class="fas fa-trash-alt"></i> Remove
                        </button>
                        @endif
                    </div>
                    <div class="row">
                        <div class="mb-form col-8">
                            <label class="form-label">Label *</label>
                            <input type="text" name="items[{{ $i }}][label]"
                                   class="form-control"
                                   value="{{ $item['label'] }}"
                                   placeholder="e.g. Tuition Fee, Computer Lab">
                        </div>
                        <div class="mb-form col-4">
                            <label class="form-label">Amount (PKR) *</label>
                            <input type="number" name="items[{{ $i }}][amount]"
                                   class="form-control amount-input"
                                   value="{{ $item['amount'] }}"
                                   min="0" step="0.01" oninput="recalc()">
                        </div>
                    </div>
                </div>
                @endforeach
            @else
            <div class="repeater-item" id="item-0">
                <div class="repeater-item-header">
                    <span class="repeater-item-label">Item #1</span>
                </div>
                <div class="row">
                    <div class="mb-form col-8">
                        <label class="form-label">Label *</label>
                        <input type="text" name="items[0][label]" class="form-control"
                               placeholder="e.g. Tuition Fee">
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Amount (PKR) *</label>
                        <input type="number" name="items[0][amount]"
                               class="form-control amount-input"
                               placeholder="0" min="0" step="0.01" oninput="recalc()">
                    </div>
                </div>
            </div>
            @endif
        </div>

        <button type="button" class="btn-add-repeater" onclick="addItem()">
            <i class="fas fa-plus-circle"></i> Add Fee Item
        </button>

        <!-- Running total -->
        <div style="margin-top:1.2rem; padding:0.9rem 1.2rem; background:var(--light-bg);
                    border-radius:var(--radius-sm); display:flex; justify-content:space-between;
                    align-items:center;">
            <span style="font-weight:600; color:var(--primary);">Total per Invoice:</span>
            <span id="total-display" style="font-size:1.3rem; font-weight:700; color:var(--primary);">
                PKR 0
            </span>
        </div>
    </div>
</div>

<div style="display:flex; gap:0.8rem;">
    <button type="submit" class="btn btn-primary btn-lg">
        <i class="fas fa-save"></i> Create Scheduler
    </button>
    <a href="{{ route('admin.fee.schedulers.index') }}" class="btn-outline-secondary btn btn-lg">
        Cancel
    </a>
</div>
</form>

<script>
let count = {{ old('items') ? count(old('items')) : 1 }};

function addItem() {
    const idx  = count++;
    const html = `
    <div class="repeater-item" id="item-${idx}">
        <div class="repeater-item-header">
            <span class="repeater-item-label">Item #${idx + 1}</span>
            <button type="button" class="btn-remove-repeater" onclick="removeItem('item-${idx}')">
                <i class="fas fa-trash-alt"></i> Remove
            </button>
        </div>
        <div class="row">
            <div class="mb-form col-8">
                <label class="form-label">Label *</label>
                <input type="text" name="items[${idx}][label]" class="form-control"
                       placeholder="e.g. Library Fee">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Amount (PKR) *</label>
                <input type="number" name="items[${idx}][amount]"
                       class="form-control amount-input"
                       placeholder="0" min="0" step="0.01" oninput="recalc()">
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