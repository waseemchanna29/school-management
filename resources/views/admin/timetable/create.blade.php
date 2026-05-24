@extends('layouts.app')
@section('title', 'New Timetable')
@section('page-title', 'New Timetable')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Create New Timetable</div>
        <div class="page-header-sub">Step 1: Set up the timetable details. Step 2: Fill the schedule grid.</div>
    </div>
    <a href="{{ route('admin.timetable.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

@if(!$hasPeriods)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        No time periods defined yet.
        <a href="{{ route('admin.timetable.periods.index') }}" style="font-weight:700;">
            Set up your campus time periods first.
        </a>
    </div>
@endif

<div style="max-width:680px;">
    <div class="card">
        <div class="card-body">
            <div class="form-section-title"><i class="fas fa-calendar-alt"></i> Timetable Details</div>

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
                                onchange="filterSections()">
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
                        <select name="section_id"
                                class="form-select {{ $errors->has('section_id') ? 'is-invalid' : '' }}"
                                id="section_select">
                            <option value="">-- Select Section --</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}"
                                        data-class="{{ $section->class_id }}"
                                        {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                    {{ $section->schoolClass->name ?? '' }} – {{ $section->name }}
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
                    <label class="form-label">School Days *</label>
                    <div style="display:flex; gap:0.6rem; flex-wrap:wrap; margin-top:0.3rem;">
                        @foreach($allDays as $key => $label)
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;
                                      padding:0.45rem 0.9rem; background:var(--light-bg);
                                      border:1.5px solid var(--border); border-radius:var(--radius-sm);
                                      font-size:0.88rem; font-weight:600; transition:all var(--transition);"
                               id="day-label-{{ $key }}">
                            <input type="checkbox" name="days[]" value="{{ $key }}"
                                   {{ in_array($key, old('days', ['Mon','Tue','Wed','Thu','Fri'])) ? 'checked' : '' }}
                                   style="accent-color:var(--primary); width:15px; height:15px;"
                                   onchange="updateDayStyle('{{ $key }}', this.checked)">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                    @error('days')<span class="invalid-feedback" style="display:block;">{{ $message }}</span>@enderror
                </div>

                <div class="mb-form">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control"
                           value="{{ old('notes') }}" placeholder="Optional notes">
                </div>

                <div style="display:flex; gap:0.8rem; padding-top:1rem; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary btn-lg" {{ !$hasPeriods ? 'disabled' : '' }}>
                        <i class="fa-arrow-right fas"></i> Create & Fill Schedule
                    </button>
                    <a href="{{ route('admin.timetable.index') }}" class="btn-outline-secondary btn btn-lg">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterSections() {
    const classId = document.getElementById('class_select').value;
    const sel     = document.getElementById('section_select');
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!classId || opt.dataset.class === classId) ? '' : 'none';
    });
    sel.value = '';
}

function updateDayStyle(day, checked) {
    const lbl = document.getElementById('day-label-' + day);
    lbl.style.background     = checked ? 'rgba(37,99,168,0.08)' : '';
    lbl.style.borderColor    = checked ? 'var(--primary)' : '';
    lbl.style.color          = checked ? 'var(--primary)' : '';
}

// Init day styles on load
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[name="days[]"]').forEach(cb => {
        updateDayStyle(cb.value, cb.checked);
    });
});
</script>
@endsection