@extends('layouts.app')
@section('title', 'Edit Fee Structure')
@section('page-title', 'Edit Fee Structure')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Edit: {{ $structure->name }}</div>
        <div class="page-header-sub">
            <span class="badge {{ $structure->type_badge_class }}">{{ $structure->type_label }}</span>
            &bull; {{ $structure->schoolClass->name ?? '' }} &bull; {{ $structure->academic_year }}
        </div>
    </div>
    <a href="{{ route('admin.fee.structures.show', $structure) }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('admin.fee.structures.update', $structure) }}" method="POST" novalidate>
@csrf @method('PUT')

<!-- Structure Info -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-file-invoice-dollar"></i> Structure Details</div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Structure Name *</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $structure->name) }}"
                       placeholder="e.g. Monthly Fee, Admission Fee">
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Structure Type *</label>
                <select name="type" class="form-select">
                    <option value="monthly"  {{ old('type', $structure->type) === 'monthly'  ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly"   {{ old('type', $structure->type) === 'yearly'   ? 'selected' : '' }}>Yearly / Annual</option>
                    <option value="one_time" {{ old('type', $structure->type) === 'one_time' ? 'selected' : '' }}>One-Time</option>
                </select>
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Class *</label>
                <select name="class_id" class="form-select">
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}"
                                {{ old('class_id', $structure->class_id) == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Academic Year *</label>
                <select name="academic_year" class="form-select">
                    @foreach($years as $year)
                        <option value="{{ $year }}"
                                {{ old('academic_year', $structure->academic_year) === $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="form-control"
                       value="{{ old('notes', $structure->notes) }}">
            </div>
        </div>
    </div>
</div>

<!-- Fee Items -->
<div class="mb-2 card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-list"></i> Fee Items</div>
    </div>
    <div class="card-body">
        <div id="fee-items-container">
            @foreach($structure->items as $i => $item)
            <div class="repeater-item" id="item-row-{{ $item->id }}">
                <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                <div class="repeater-item-header">
                    <span class="repeater-item-label">{{ $item->feeLabel->name ?? 'Item ' . ($i+1) }}</span>
                    <button type="button" class="btn-remove-repeater"
                            onclick="removeItem('item-row-{{ $item->id }}')">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </div>
                <div class="row">
                    <div class="mb-form col-8">
                        <label class="form-label">Fee Label *</label>
                        <select name="items[{{ $i }}][fee_label_id]" class="form-select">
                            @foreach($labels as $label)
                                <option value="{{ $label->id }}"
                                        {{ old("items.$i.fee_label_id", $item->fee_label_id) == $label->id ? 'selected' : '' }}>
                                    {{ $label->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Amount (PKR) *</label>
                        <input type="number" name="items[{{ $i }}][amount]"
                               class="form-control fee-amount-input"
                               value="{{ old("items.$i.amount", $item->amount) }}"
                               min="0" step="0.01" oninput="recalcTotal()">
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <button type="button" class="btn-add-repeater" id="add-item">
            <i class="fas fa-plus-circle"></i> Add Fee Item
        </button>

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
        <i class="fas fa-save"></i> Save Changes
    </button>
    <a href="{{ route('admin.fee.structures.show', $structure) }}" class="btn-outline-secondary btn btn-lg">
        Cancel
    </a>
</div>
</form>

<script>
const labelsJson = @json($labels->map(fn($l) => ['id' => $l->id, 'name' => $l->name]));
let itemCount = {{ $structure->items->count() }};

document.getElementById('add-item').addEventListener('click', function () {
    const idx  = 'new_' + itemCount++;
    const opts = labelsJson.map(l => `<option value="${l.id}">${l.name}</option>`).join('');
    const html = `
    <div class="repeater-item" id="item-row-${idx}">
        <div class="repeater-item-header">
            <span class="repeater-item-label">New Item</span>
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

recalcTotal();
</script>
@endsection