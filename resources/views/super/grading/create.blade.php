@extends('layouts.app')
@section('title', 'New Grade Scale')
@section('page-title', 'New Grade Scale')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Create Grade Scale</div>
        <div class="page-header-sub">Define grade ranges, GPA values and colors</div>
    </div>
    <a href="{{ route('super.grading.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('super.grading.store') }}" method="POST" novalidate>
@csrf

<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-star-half-alt"></i> Scale Details</div>
        <div class="row">
            <div class="mb-form col-8">
                <label class="form-label">Scale Name *</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name') }}"
                       placeholder="e.g. Standard Pakistani Grading Scale">
            </div>
            <div class="mb-form col-4">
                <div style="display:flex; align-items:center; gap:10px; padding:0.8rem;
                            background:var(--light-bg); border-radius:var(--radius-sm); margin-top:1.7rem;">
                    <input type="checkbox" name="is_default" id="is_default" value="1"
                           {{ old('is_default') ? 'checked' : '' }}
                           style="width:17px; height:17px; accent-color:var(--primary);">
                    <label for="is_default" style="cursor:pointer; margin:0; font-weight:600; font-size:0.88rem;">
                        Set as Global Default
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-2 card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-list"></i> Grade Items</div>
        <span style="font-size:0.82rem; color:var(--text-muted);">
            Ranges must cover 0–100% without gaps or overlaps
        </span>
    </div>
    <div class="card-body">
        <div id="grades-container">
            @php
                $defaults = [
                    ['grade'=>'A+','min'=>90,'max'=>100,'gpa'=>4.0,'desc'=>'Excellent','color'=>'#198754'],
                    ['grade'=>'A', 'min'=>80,'max'=>89, 'gpa'=>3.7,'desc'=>'Very Good','color'=>'#0dcaf0'],
                    ['grade'=>'B+','min'=>75,'max'=>79, 'gpa'=>3.3,'desc'=>'Good','color'=>'#2563a8'],
                    ['grade'=>'B', 'min'=>65,'max'=>74, 'gpa'=>3.0,'desc'=>'Above Average','color'=>'#6f42c1'],
                    ['grade'=>'C', 'min'=>55,'max'=>64, 'gpa'=>2.0,'desc'=>'Average','color'=>'#e8a020'],
                    ['grade'=>'D', 'min'=>45,'max'=>54, 'gpa'=>1.0,'desc'=>'Below Average','color'=>'#fd7e14'],
                    ['grade'=>'F', 'min'=>0, 'max'=>44, 'gpa'=>0.0,'desc'=>'Fail','color'=>'#dc3545'],
                ];
                $items = old('items', $defaults);
            @endphp

            @foreach($items as $i => $item)
            <div class="repeater-item" id="grade-row-{{ $i }}">
                <div class="repeater-item-header">
                    <span class="repeater-item-label">Grade Entry #{{ $i + 1 }}</span>
                    @if($i > 0)
                    <button type="button" class="btn-remove-repeater"
                            onclick="removeGrade('grade-row-{{ $i }}')">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                    @endif
                </div>
                <div class="row">
                    <div class="mb-form col-3">
                        <label class="form-label">Grade Label *</label>
                        <input type="text" name="items[{{ $i }}][grade]"
                               class="form-control" style="font-weight:700; text-align:center;"
                               value="{{ $item['grade'] ?? '' }}" placeholder="A+">
                    </div>
                    <div class="mb-form col-2">
                        <label class="form-label">Min % *</label>
                        <input type="number" name="items[{{ $i }}][min_marks]"
                               class="form-control" min="0" max="100"
                               value="{{ $item['min'] ?? $item['min_marks'] ?? '' }}">
                    </div>
                    <div class="mb-form col-2">
                        <label class="form-label">Max % *</label>
                        <input type="number" name="items[{{ $i }}][max_marks]"
                               class="form-control" min="0" max="100"
                               value="{{ $item['max'] ?? $item['max_marks'] ?? '' }}">
                    </div>
                    <div class="mb-form col-2">
                        <label class="form-label">GPA *</label>
                        <input type="number" name="items[{{ $i }}][gpa]"
                               class="form-control" min="0" max="4" step="0.1"
                               value="{{ $item['gpa'] ?? '' }}">
                    </div>
                    <div class="mb-form col-2">
                        <label class="form-label">Color</label>
                        <input type="color" name="items[{{ $i }}][color]"
                               class="form-control" style="height:40px; padding:3px;"
                               value="{{ $item['color'] ?? '#6c7a8d' }}">
                    </div>
                    <div class="mb-form col-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="items[{{ $i }}][description]"
                               class="form-control"
                               value="{{ $item['desc'] ?? $item['description'] ?? '' }}"
                               placeholder="e.g. Excellent, Very Good, Fail...">
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <button type="button" class="btn-add-repeater" onclick="addGradeRow()">
            <i class="fas fa-plus-circle"></i> Add Grade
        </button>
    </div>
</div>

<div style="display:flex; gap:0.8rem;">
    <button type="submit" class="btn btn-primary btn-lg">
        <i class="fas fa-save"></i> Create Scale
    </button>
    <a href="{{ route('super.grading.index') }}" class="btn-outline-secondary btn btn-lg">
        Cancel
    </a>
</div>
</form>

<script>
let gradeCount = {{ count($items) }};

function addGradeRow() {
    const idx  = gradeCount++;
    const html = `
    <div class="repeater-item" id="grade-row-${idx}">
        <div class="repeater-item-header">
            <span class="repeater-item-label">Grade Entry #${idx + 1}</span>
            <button type="button" class="btn-remove-repeater" onclick="removeGrade('grade-row-${idx}')">
                <i class="fas fa-trash-alt"></i> Remove
            </button>
        </div>
        <div class="row">
            <div class="mb-form col-3">
                <label class="form-label">Grade Label *</label>
                <input type="text" name="items[${idx}][grade]" class="form-control"
                       style="font-weight:700; text-align:center;" placeholder="e.g. B+">
            </div>
            <div class="mb-form col-2">
                <label class="form-label">Min % *</label>
                <input type="number" name="items[${idx}][min_marks]" class="form-control" min="0" max="100">
            </div>
            <div class="mb-form col-2">
                <label class="form-label">Max % *</label>
                <input type="number" name="items[${idx}][max_marks]" class="form-control" min="0" max="100">
            </div>
            <div class="mb-form col-2">
                <label class="form-label">GPA *</label>
                <input type="number" name="items[${idx}][gpa]" class="form-control" min="0" max="4" step="0.1">
            </div>
            <div class="mb-form col-2">
                <label class="form-label">Color</label>
                <input type="color" name="items[${idx}][color]" class="form-control"
                       style="height:40px; padding:3px;" value="#6c7a8d">
            </div>
            <div class="mb-form col-12">
                <label class="form-label">Description</label>
                <input type="text" name="items[${idx}][description]" class="form-control"
                       placeholder="e.g. Excellent">
            </div>
        </div>
    </div>`;
    document.getElementById('grades-container').insertAdjacentHTML('beforeend', html);
}

function removeGrade(id) {
    document.getElementById(id).remove();
}
</script>
@endsection