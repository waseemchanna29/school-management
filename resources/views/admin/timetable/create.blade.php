@extends('layouts.app')
@section('title', 'New Timetable')
@section('page-title', 'New Timetable')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Create Timetable</div>
        <div class="page-header-sub">
            Step 1 of 3: Basic details &rarr; Step 2: Define periods &rarr; Step 3: Fill schedule
        </div>
    </div>
    <a href="{{ route('admin.timetable.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div style="max-width:700px;">
    <div class="card">
        <div class="card-body">
            <div class="form-section-title">
                <i class="fas fa-calendar-alt"></i> Timetable Details
            </div>

            <form action="{{ route('admin.timetable.store') }}" method="POST" novalidate>
                @csrf

                <div class="mb-form">
                    <label class="form-label">Timetable Name *</label>
                    <input type="text" name="name"
                           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name') }}"
                           placeholder="e.g. Class 9-A Morning Schedule 2024-25">
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="row">
                    <div class="mb-form col-6">
                        <label class="form-label">Class *</label>
                        <select name="class_id" id="class_select"
                                class="form-select {{ $errors->has('class_id') ? 'is-invalid' : '' }}"
                                onchange="filterSections(this.value)">
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}"
                                        {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Section *</label>
                        <select name="section_id" id="section_select"
                                class="form-select {{ $errors->has('section_id') ? 'is-invalid' : '' }}">
                            <option value="">-- Select Section --</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}"
                                        data-class="{{ $section->class_id }}"
                                        {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('section_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="mb-form">
                    <label class="form-label">Academic Year *</label>
                    <select name="academic_year" class="form-select">
                        @foreach($years as $year)
                            <option value="{{ $year }}"
                                    {{ old('academic_year') === $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-form">
                    <label class="form-label">Active School Days *</label>
                    <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:0.4rem;">
                        @php
                            $defaultDays = old('days', ['Mon','Tue','Wed','Thu','Fri']);
                        @endphp
                        @foreach(\App\Models\Timetable::DAY_LABELS as $key => $label)
                        <label class="day-checkbox-label"
                               id="day-lbl-{{ $key }}"
                               style="display:flex; align-items:center; gap:7px; cursor:pointer;
                                      padding:0.5rem 1rem; border:2px solid var(--border);
                                      border-radius:var(--radius-sm); font-size:0.88rem;
                                      font-weight:600; transition:all var(--transition);
                                      {{ in_array($key, $defaultDays) ? 'background:rgba(37,99,168,0.08); border-color:var(--primary); color:var(--primary);' : '' }}">
                            <input type="checkbox" name="days[]" value="{{ $key }}"
                                   {{ in_array($key, $defaultDays) ? 'checked' : '' }}
                                   style="accent-color:var(--primary); width:15px; height:15px;"
                                   onchange="toggleDayStyle('{{ $key }}', this.checked)">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                    @error('days')
                        <span class="invalid-feedback" style="display:block; margin-top:5px;">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="mb-form">
                    <label class="form-label">Notes (optional)</label>
                    <input type="text" name="notes" class="form-control"
                           value="{{ old('notes') }}"
                           placeholder="e.g. Morning shift, Ramadan schedule...">
                </div>

                <div style="display:flex; gap:0.8rem; padding-top:1rem; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-arrow-right fas"></i> Next: Define Periods
                    </button>
                    <a href="{{ route('admin.timetable.index') }}"
                       class="btn-outline-secondary btn btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterSections(classId) {
    const sel = document.getElementById('section_select');
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!classId || opt.dataset.class === classId) ? '' : 'none';
    });
    sel.value = '';
}

function toggleDayStyle(key, checked) {
    const lbl = document.getElementById('day-lbl-' + key);
    lbl.style.background  = checked ? 'rgba(37,99,168,0.08)' : '';
    lbl.style.borderColor = checked ? 'var(--primary)'        : 'var(--border)';
    lbl.style.color       = checked ? 'var(--primary)'        : '';
}

// Init styles
document.querySelectorAll('[name="days[]"]').forEach(cb => {
    toggleDayStyle(cb.value, cb.checked);
});
</script>
@endsection