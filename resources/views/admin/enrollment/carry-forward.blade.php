@extends('layouts.app')
@section('title', 'Carry Forward')
@section('page-title', 'Carry Forward Students')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Carry Forward Students</div>
        <div class="page-header-sub">
            Enroll students from
            <strong>{{ $previousYear->name }}</strong>
            into
            <strong>{{ $currentYear->name }}</strong>
            — assign new class and section for each
        </div>
    </div>
    <a href="{{ route('admin.enrollment.index') }}"
       class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

@if($previousEnrollments->isEmpty())
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    No students from <strong>{{ $previousYear->name }}</strong>
    are pending enrollment for <strong>{{ $currentYear->name }}</strong>.
    All active students may already be enrolled this year.
</div>
@else

<div class="alert alert-info no-print" style="margin-bottom:1.4rem;">
    <i class="fas fa-info-circle"></i>
    <strong>{{ $previousEnrollments->count() }}</strong> students from
    {{ $previousYear->name }} need to be enrolled in {{ $currentYear->name }}.
    Assign each student a new class and section, then click
    <strong>Enroll All</strong>.
</div>

<form action="{{ route('admin.enrollment.carry-forward.store') }}"
      method="POST" novalidate>
@csrf

{{-- Select/Deselect All --}}
<div style="display:flex; align-items:center; gap:1rem;
            margin-bottom:1rem; flex-wrap:wrap;">
    <label style="display:flex; align-items:center; gap:8px;
                  cursor:pointer; font-weight:600; font-size:0.88rem;">
        <input type="checkbox" id="select-all"
               style="width:16px; height:16px; accent-color:var(--primary);"
               onchange="toggleAllStudents(this)">
        Select All Students
    </label>
    <button type="button" class="btn-outline-secondary btn btn-sm"
            onclick="setAllSameClass()">
        <i class="fa-layer-group fas"></i>
        Same Class for All
    </button>
    <span style="font-size:0.82rem; color:var(--text-muted);">
        <i class="fas fa-info-circle"></i>
        Uncheck students you don't want to enroll yet
    </span>
</div>

{{-- Group by previous class --}}
@php $grouped = $previousEnrollments->groupBy('class_id'); @endphp

@foreach($grouped as $classId => $classEnrollments)
<div class="mb-2 card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-chalkboard"></i>
            Previous: {{ $classEnrollments->first()->schoolClass->name ?? '—' }}
            <span class="badge badge-info"
                  style="margin-left:6px; font-size:0.72rem;">
                {{ $classEnrollments->count() }} students
            </span>
        </div>

        {{-- Quick assign for the entire group --}}
        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
            <span style="font-size:0.8rem; color:var(--text-muted);">
                Assign all in this group:
            </span>
            <select class="form-select" style="width:130px; font-size:0.8rem;"
                    id="group-class-{{ $classId }}"
                    onchange="applyGroupClass('{{ $classId }}', this.value)">
                <option value="">— Class —</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
            <select class="form-select" style="width:120px; font-size:0.8rem;"
                    id="group-section-{{ $classId }}"
                    onchange="applyGroupSection('{{ $classId }}', this.value)">
                <option value="">— Section —</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}"
                            data-class="{{ $section->class_id }}">
                        {{ $section->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>Student</th>
                    <th>Previous Section</th>
                    <th>Previous Roll</th>
                    <th>New Class *</th>
                    <th>New Section *</th>
                    <th>New Roll No.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classEnrollments as $idx => $enrollment)
                @php
                    $key = $enrollment->student_id;
                @endphp
                <tr id="cf-row-{{ $key }}" data-group="{{ $classId }}">
                    <td style="text-align:center;">
                        <input type="checkbox"
                               class="cf-checkbox group-{{ $classId }}"
                               id="cf-check-{{ $key }}"
                               checked
                               style="width:15px; height:15px;
                                      accent-color:var(--primary);"
                               onchange="toggleStudentRow('{{ $key }}', this.checked)">
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.6rem;">
                            <div class="sidebar-user-avatar"
                                 style="width:30px; height:30px; font-size:0.78rem;">
                                {{ strtoupper(substr($enrollment->student->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <strong style="font-size:0.87rem;">
                                    {{ $enrollment->student->full_name }}
                                </strong>
                                <div style="font-size:0.74rem; color:var(--text-muted);">
                                    {{ $enrollment->student->father_name }}
                                </div>
                            </div>
                        </div>

                        {{-- Hidden student_id --}}
                        <input type="hidden"
                               name="enrollments[{{ $key }}][student_id]"
                               value="{{ $key }}"
                               class="cf-student-input"
                               id="cf-sid-{{ $key }}">
                    </td>
                    <td>
                        <span class="badge badge-info" style="font-size:0.75rem;">
                            {{ $enrollment->section->name ?? '—' }}
                        </span>
                    </td>
                    <td style="color:var(--text-muted);">
                        {{ $enrollment->roll_number ?? '—' }}
                    </td>
                    <td>
                        <select name="enrollments[{{ $key }}][class_id]"
                                class="form-select cf-class-{{ $classId }}"
                                id="cf-class-{{ $key }}"
                                style="font-size:0.82rem; width:120px;"
                                onchange="filterCfSections('{{ $key }}', this.value)">
                            <option value="">— Class —</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="enrollments[{{ $key }}][section_id]"
                                class="form-select cf-section-{{ $classId }}"
                                id="cf-section-{{ $key }}"
                                style="font-size:0.82rem; width:110px;">
                            <option value="">— Section —</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}"
                                        data-class="{{ $section->class_id }}">
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text"
                               name="enrollments[{{ $key }}][roll_number]"
                               class="form-control"
                               style="font-size:0.82rem; width:90px;"
                               placeholder="Roll No."
                               id="cf-roll-{{ $key }}">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach

<div style="display:flex; gap:0.8rem; margin-top:1rem; align-items:center;">
    <button type="submit" class="btn btn-primary btn-lg">
        <i class="fas fa-user-graduate"></i>
        Enroll Selected Students into {{ $currentYear->name }}
    </button>
    <a href="{{ route('admin.enrollment.index') }}"
       class="btn-outline-secondary btn btn-lg">Cancel</a>
</div>
</form>
@endif

<script>
// Toggle all checkboxes
function toggleAllStudents(master) {
    document.querySelectorAll('.cf-checkbox')
        .forEach(cb => {
            cb.checked = master.checked;
            const key = cb.id.replace('cf-check-', '');
            toggleStudentRow(key, master.checked);
        });
}

// Enable/disable row inputs when checkbox toggled
function toggleStudentRow(key, enabled) {
    const row = document.getElementById('cf-row-' + key);
    if (!row) return;

    // Enable/disable all inputs in this row
    row.querySelectorAll('select, input[type="text"]')
        .forEach(el => el.disabled = !enabled);

    // Toggle hidden student_id field
    const sid = document.getElementById('cf-sid-' + key);
    if (sid) sid.disabled = !enabled;

    row.style.opacity = enabled ? '1' : '0.4';
}

// Apply same class to all rows in a group
function applyGroupClass(groupClassId, newClassId) {
    document.querySelectorAll(`.cf-class-${groupClassId}`).forEach(sel => {
        sel.value = newClassId;
        const key = sel.id.replace('cf-class-', '');
        filterCfSections(key, newClassId);
    });
}

// Apply same section to all rows in a group
function applyGroupSection(groupClassId, sectionId) {
    document.querySelectorAll(`.cf-section-${groupClassId}`).forEach(sel => {
        sel.value = sectionId;
    });
}

// Filter sections when class changes for a student row
function filterCfSections(key, classId) {
    const sel = document.getElementById('cf-section-' + key);
    if (!sel) return;
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!classId || opt.dataset.class === classId)
            ? '' : 'none';
    });
    sel.value = '';
}

function setAllSameClass() {
    smsAlert(
        'Use the "Assign all in this group" dropdowns at the top of each class group ' +
        'to quickly assign all students in that group to the same class and section.',
        'info',
        'Quick Assign Tip'
    );
}
</script>
@endsection