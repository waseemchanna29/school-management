@extends('layouts.app')
@section('title', 'Create Fee Structure')
@section('page-title', 'Create Fee Structure')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Create Fee Structure</div>
        <div class="page-header-sub">
            Name your structure, set its type, pick a class, then add fee labels with amounts.
        </div>
    </div>
    <a href="{{ route('admin.fee.structures.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

@if($labels->isEmpty())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        No fee labels found.
        <a href="{{ route('admin.fee.labels.index') }}" style="font-weight:700;">Create fee labels first</a>
        before building a structure.
    </div>
@endif

<form action="{{ route('admin.fee.structures.store') }}" method="POST" novalidate>
@csrf

<!-- Structure Info -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-file-invoice-dollar"></i> Structure Details</div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Structure Name *</label>
                <input type="text" name="name"
                       class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       value="{{ old('name') }}"
                       placeholder="e.g. Monthly Fee, New Admission Fee, Annual Charges">
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                <small style="color:var(--text-muted); font-size:0.79rem; margin-top:3px; display:block;">
                    This name appears on the student's fee profile and invoice.
                </small>
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Structure Type *</label>
                <select name="type" id="type_select"
                        class="form-select {{ $errors->has('type') ? 'is-invalid' : '' }}">
                    <option value="">-- Select Type --</option>
                    <option value="monthly"  {{ old('type') === 'monthly'  ? 'selected' : '' }}>
                        Monthly — billed each month via bulk generation
                    </option>
                    <option value="yearly"   {{ old('type') === 'yearly'   ? 'selected' : '' }}>
                        Yearly / Annual — billed once per year
                    </option>
                    <option value="one_time" {{ old('type') === 'one_time' ? 'selected' : '' }}>
                        One-Time — e.g. Admission, Security Deposit
                    </option>
                </select>
                @error('type')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Class *</label>
                <select name="class_id" class="form-select {{ $errors->has('class_id') ? 'is-invalid' : '' }}">
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                @error('class_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Academic Year *</label>
                <select name="academic_year" class="form-select {{ $errors->has('academic_year') ? 'is-invalid' : '' }}">
                    <option value="">-- Select Year --</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ old('academic_year') === $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
                @error('academic_year')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}"
                       placeholder="Optional notes">
            </div>
        </div>
    </div>
</div>

<!-- Fee Items -->
<div class="mb-2 card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-list"></i> Fee Items</div>
        <span style="font-size:0.83rem; color:var(--text-muted);">
            Add the fee labels that belong to this structure with their amounts.
        </span>
    </div>
    <div class="card-body">

        @if($errors->has('items'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> {{ $errors->first('items') }}
            </div>
        @endif

        <div id="fee-items-container">
            @if(old('items'))
                @foreach(old('items') as $i => $item)
                <div class="repeater-item" id="item-row-{{ $i }}">
                    <div class="repeater-item-header">
                        <span class="repeater-item-label">Item #{{ $i + 1 }}</span>
                        @if($i > 0)
                        <button type="button" class="btn-remove-repeater"
                                onclick="removeItem('item-row-{{ $i }}')">
                            <i class="fas fa-trash-alt"></i> Remove
                        </button>
                        @endif
                    </div>
                    <div class="row">
                        <div class="mb-form col-8">
                            <label class="form-label">Fee Label *</label>
                            <select name="items[{{ $i }}][fee_label_id]" class="form-select">
                                @foreach($labels as $label)
                                    <option value="{{ $label->id }}"
                                            {{ $item['fee_label_id'] == $label->id ? 'selected' : '' }}>
                                        {{ $label->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-form col-4">
                            <label class="form-label">Amount (PKR) *</label>
                            <input type="number" name="items[{{ $i }}][amount]"
                                   class="form-control fee-amount-input"
                                   value="{{ $item['amount'] }}"
                                   min="0" step="0.01" oninput="recalcTotal()">
                        </div>
                    </div>
                </div>
                @endforeach
            @else
            <!-- Default first row -->
            <div class="repeater-item" id="item-row-0">
                <div class="repeater-item-header">
                    <span class="repeater-item-label">Item #1</span>
                </div>
                <div class="row">
                    <div class="mb-form col-8">
                        <label class="form-label">Fee Label *</label>
                        <select name="items[0][fee_label_id]" class="form-select">
                            @foreach($labels as $label)
                                <option value="{{ $label->id }}">{{ $label->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Amount (PKR) *</label>
                        <input type="number" name="items[0][amount]"
                               class="form-control fee-amount-input"
                               placeholder="0" min="0" step="0.01" oninput="recalcTotal()">
                    </div>
                </div>
            </div>
            @endif
        </div>

        <button type="button" class="btn-add-repeater" id="add-item">
            <i class="fas fa-plus-circle"></i> Add Another Fee Item
        </button>

        <!-- Running Total -->
        <div style="margin-top:1.3rem; padding:1rem 1.3rem; background:var(--light-bg);
                    border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:space-between;">
            <span style="font-weight:600; color:var(--primary);">Structure Total:</span>
            <span id="total-display" style="font-size:1.3rem; font-weight:700; color:var(--primary);">
                PKR 0.00
            </span>
        </div>
    </div>
</div>

<div style="display:flex; gap:0.8rem;">
    <button type="submit" class="btn btn-primary btn-lg">
        <i class="fas fa-save"></i> Create Structure
    </button>
    <a href="{{ route('admin.fee.structures.index') }}" class="btn-outline-secondary btn btn-lg">
        Cancel
    </a>
</div>
</form>

<script>
const labelsJson = @json($labels->map(fn($l) => ['id' => $l->id, 'name' => $l->name]));
let itemCount = {{ old('items') ? count(old('items')) : 1 }};

document.getElementById('add-item').addEventListener('click', function () {
    const idx  = itemCount++;
    const opts = labelsJson.map(l => `<option value="${l.id}">${l.name}</option>`).join('');
    const html = `
    <div class="repeater-item" id="item-row-${idx}">
        <div class="repeater-item-header">
            <span class="repeater-item-label">Item #${idx + 1}</span>
            <button type="button" class="btn-remove-repeater" onclick="removeItem('item-row-${idx}')">
                <i class="fas fa-trash-alt"></i> Remove
            </button>
        </div>
        <div class="row">
            <div class="mb-form col-8">
                <label class="form-label">Fee Label *</label>
                <select name="items[${idx}][fee_label_id]" class="form-select">${opts}</select>
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Amount (PKR) *</label>
                <input type="number" name="items[${idx}][amount]"
                       class="form-control fee-amount-input"
                       placeholder="0" min="0" step="0.01" oninput="recalcTotal()">
            </div>
        </div>
    </div>`;
    document.getElementById('fee-items-container').insertAdjacentHTML('beforeend', html);
});

function removeItem(id) {
    document.getElementById(id).remove();
    recalcTotal();
}

function recalcTotal() {
    let total = 0;
    document.querySelectorAll('.fee-amount-input').forEach(i => total += parseFloat(i.value) || 0);
    document.getElementById('total-display').textContent =
        'PKR ' + total.toLocaleString('en-PK', { minimumFractionDigits: 2 });
}
</script>
@endsection