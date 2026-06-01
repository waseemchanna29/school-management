@extends('layouts.app')
@section('title', 'Edit Grade Scale')
@section('page-title', 'Edit Grade Scale')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Edit: {{ $gradeScale->name }}</div>
        <div class="page-header-sub">
            {{ $gradeScale->isGlobal() ? 'Global Scale' : 'Campus Scale' }}
        </div>
    </div>
    <a href="{{ route('super.grading.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('super.grading.update', $gradeScale) }}" method="POST" novalidate>
@csrf @method('PUT')

<div class="mb-2 card">
    <div class="card-body">
        <div class="row">
            <div class="mb-form col-8">
                <label class="form-label">Scale Name *</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $gradeScale->name) }}">
            </div>
            @if($gradeScale->isGlobal())
            <div class="mb-form col-4">
                <div style="display:flex; align-items:center; gap:10px; padding:0.8rem;
                            background:var(--light-bg); border-radius:var(--radius-sm); margin-top:1.7rem;">
                    <input type="checkbox" name="is_default" id="is_default" value="1"
                           {{ $gradeScale->is_default ? 'checked' : '' }}
                           style="width:17px; height:17px; accent-color:var(--primary);">
                    <label for="is_default" style="cursor:pointer; margin:0; font-weight:600; font-size:0.88rem;">
                        Set as Global Default
                    </label>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="mb-2 card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-list"></i> Grade Items</div>
    </div>
    <div class="card-body">
        <div id="grades-container">
            @foreach($gradeScale->items as $i => $item)
            <div class="repeater-item" id="grade-row-{{ $i }}">
                <div class="repeater-item-header">
                    <span class="repeater-item-label" style="display:flex; align-items:center; gap:0.5rem;">
                        <span class="grade-badge" style="{{ $item->color_style }}">{{ $item->grade }}</span>
                        {{ $item->description }}
                    </span>
                    <button type="button" class="btn-remove-repeater"
                            onclick="removeGrade('grade-row-{{ $i }}')">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </div>
                <div class="row">
                    <div class="mb-form col-3">
                        <label class="form-label">Grade *</label>
                        <input type="text" name="items[{{ $i }}][grade]"
                               class="form-control" style="font-weight:700; text-align:center;"
                               value="{{ old("items.$i.grade", $item->grade) }}">
                    </div>
                    <div class="mb-form col-2">
                        <label class="form-label">Min %</label>
                        <input type="number" name="items[{{ $i }}][min_marks]"
                               class="form-control" min="0" max="100"
                               value="{{ old("items.$i.min_marks", $item->min_marks) }}">
                    </div>
                    <div class="mb-form col-2">
                        <label class="form-label">Max %</label>
                        <input type="number" name="items[{{ $i }}][max_marks]"
                               class="form-control" min="0" max="100"
                               value="{{ old("items.$i.max_marks", $item->max_marks) }}">
                    </div>
                    <div class="mb-form col-2">
                        <label class="form-label">GPA</label>
                        <input type="number" name="items[{{ $i }}][gpa]"
                               class="form-control" min="0" max="4" step="0.1"
                               value="{{ old("items.$i.gpa", $item->gpa) }}">
                    </div>
                    <div class="mb-form col-2">
                        <label class="form-label">Color</label>
                        <input type="color" name="items[{{ $i }}][color]"
                               class="form-control" style="height:40px; padding:3px;"
                               value="{{ old("items.$i.color", $item->color ?? '#6c7a8d') }}">
                    </div>
                    <div class="mb-form col-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="items[{{ $i }}][description]"
                               class="form-control"
                               value="{{ old("items.$i.description", $item->description) }}">
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
        <i class="fas fa-save"></i> Save Changes
    </button>
    <a href="{{ route('super.grading.index') }}" class="btn-outline-secondary btn btn-lg">Cancel</a>
</div>
</form>

<script>
let gradeCount = {{ $gradeScale->items->count() }};
function addGradeRow() {
    const idx = gradeCount++;
    document.getElementById('grades-container').insertAdjacentHTML('beforeend', `
    <div class="repeater-item" id="grade-row-${idx}">
        <div class="repeater-item-header">
            <span class="repeater-item-label">New Grade</span>
            <button type="button" class="btn-remove-repeater" onclick="removeGrade('grade-row-${idx}')">
                <i class="fas fa-trash-alt"></i> Remove
            </button>
        </div>
        <div class="row">
            <div class="mb-form col-3">
                <label class="form-label">Grade *</label>
                <input type="text" name="items[${idx}][grade]" class="form-control" style="font-weight:700; text-align:center;">
            </div>
            <div class="mb-form col-2">
                <label class="form-label">Min %</label>
                <input type="number" name="items[${idx}][min_marks]" class="form-control" min="0" max="100">
            </div>
            <div class="mb-form col-2">
                <label class="form-label">Max %</label>
                <input type="number" name="items[${idx}][max_marks]" class="form-control" min="0" max="100">
            </div>
            <div class="mb-form col-2">
                <label class="form-label">GPA</label>
                <input type="number" name="items[${idx}][gpa]" class="form-control" min="0" max="4" step="0.1">
            </div>
            <div class="mb-form col-2">
                <label class="form-label">Color</label>
                <input type="color" name="items[${idx}][color]" class="form-control" style="height:40px; padding:3px;" value="#6c7a8d">
            </div>
            <div class="mb-form col-12">
                <label class="form-label">Description</label>
                <input type="text" name="items[${idx}][description]" class="form-control">
            </div>
        </div>
    </div>`);
}
function removeGrade(id) { document.getElementById(id).remove(); }
</script>
@endsection