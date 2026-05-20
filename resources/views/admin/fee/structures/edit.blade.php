@extends('layouts.app')
@section('title', 'Edit Fee Structure')
@section('page-title', 'Edit Fee Structure')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Edit Fee Structure</div>
        <div class="page-header-sub">{{ $structure->schoolClass->name ?? '' }} — {{ $structure->academic_year }}</div>
    </div>
    <a href="{{ route('admin.fee.structures.show', $structure) }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('admin.fee.structures.update', $structure) }}" method="POST" novalidate>
@csrf @method('PUT')

<div class="mb-2 card">
    <div class="card-body">
        <div class="mb-form">
            <label class="form-label">Notes</label>
            <input type="text" name="notes" class="form-control" value="{{ old('notes', $structure->notes) }}">
        </div>
    </div>
</div>

<div class="mb-2 card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-list"></i> Fee Items</div>
    </div>
    <div class="card-body">
        <div id="fee-items-container">
            @foreach($structure->items as $i => $item)
            <div class="repeater-item" id="item-{{ $item->id }}">
                <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                <div class="repeater-item-header">
                    <span class="repeater-item-label">{{ $item->feeLabel->name ?? 'Fee Item' }}</span>
                    <div class="d-flex align-items-center gap-2">
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:0.83rem; color:var(--text-muted);">
                            <input type="checkbox" name="items[{{ $i }}][is_active]" value="1"
                                   {{ old("items.$i.is_active", $item->is_active) ? 'checked' : '' }}
                                   style="accent-color:var(--success);">
                            Active
                        </label>
                        <button type="button" class="btn-remove-repeater"
                                onclick="document.getElementById('item-{{ $item->id }}').remove(); recalcTotal()">
                            <i class="fas fa-trash-alt"></i> Remove
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-form col-6">
                        <label class="form-label">Fee Label *</label>
                        <select name="items[{{ $i }}][fee_label_id]" class="form-select">
                            @foreach($labels as $label)
                                <option value="{{ $label->id }}" {{ old("items.$i.fee_label_id", $item->fee_label_id) == $label->id ? 'selected' : '' }}>
                                    {{ $label->name }} ({{ $label->frequency_label }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form col-6">
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

        <div style="margin-top:1.2rem; padding:1rem 1.2rem; background:var(--light-bg); border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:space-between;">
            <span style="font-weight:600; color:var(--primary);">Estimated Total:</span>
            <span id="total-display" style="font-size:1.3rem; font-weight:700; color:var(--primary);">PKR 0.00</span>
        </div>
    </div>
</div>

<div style="display:flex; gap:0.8rem;">
    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Changes</button>
    <a href="{{ route('admin.fee.structures.show', $structure) }}" class="btn-outline-secondary btn btn-lg">Cancel</a>
</div>
</form>

<script>
const labelsJson = @json($labels->map(fn($l) => ['id' => $l->id, 'name' => $l->name . ' (' . $l->frequency_label . ')']));
let itemCount = {{ $structure->items->count() }};

document.getElementById('add-item').addEventListener('click', function () {
    const idx = itemCount++;
    const opts = labelsJson.map(l => `<option value="${l.id}">${l.name}</option>`).join('');
    const html = `
    <div class="repeater-item" id="new-item-${idx}">
        <div class="repeater-item-header">
            <span class="repeater-item-label">New Fee Item</span>
            <button type="button" class="btn-remove-repeater" onclick="document.getElementById('new-item-${idx}').remove(); recalcTotal()">
                <i class="fas fa-trash-alt"></i> Remove
            </button>
        </div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Fee Label *</label>
                <select name="items[new_${idx}][fee_label_id]" class="form-select">${opts}</select>
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Amount (PKR) *</label>
                <input type="number" name="items[new_${idx}][amount]" class="form-control fee-amount-input"
                       placeholder="0.00" min="0" step="0.01" oninput="recalcTotal()">
            </div>
        </div>
    </div>`;
    document.getElementById('fee-items-container').insertAdjacentHTML('beforeend', html);
});

function recalcTotal() {
    const inputs = document.querySelectorAll('.fee-amount-input');
    let total = 0;
    inputs.forEach(i => total += parseFloat(i.value) || 0);
    document.getElementById('total-display').textContent = 'PKR ' + total.toLocaleString('en-PK', {minimumFractionDigits: 2});
}

// Init total
recalcTotal();
</script>
@endsection