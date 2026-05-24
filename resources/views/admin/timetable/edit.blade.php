@extends('layouts.app')
@section('title', 'Edit Timetable')
@section('page-title', 'Edit Timetable')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">{{ $timetable->name }}</div>
        <div class="page-header-sub">
            {{ $timetable->schoolClass->name ?? '' }} — Section {{ $timetable->section->name ?? '' }}
            &bull; {{ $timetable->academic_year }}
            &bull; {{ $timetable->days_label }}
        </div>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('admin.timetable.show', $timetable) }}" class="btn-outline-secondary btn btn-sm">
            <i class="fas fa-eye"></i> View
        </a>
        <a href="{{ route('admin.timetable.index') }}" class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

@if($periods->isEmpty())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        No active time periods found.
        <a href="{{ route('admin.timetable.periods.index') }}">Add periods first.</a>
    </div>
@else

<div class="alert alert-info no-print" style="margin-bottom:1.2rem;">
    <i class="fas fa-info-circle"></i>
    Set each cell to <strong>Lesson</strong> (pick subject + teacher), <strong>Break</strong> (e.g. Lunch), or leave <strong>Free</strong>.
    Teacher conflicts will be flagged after saving.
</div>

<form action="{{ route('admin.timetable.save-grid', $timetable) }}" method="POST" id="timetable-form">
@csrf

<div class="mb-2 card">
    <div class="card-body" style="padding:0;">
        <div class="timetable-grid-wrapper">
            <table class="timetable-grid">
                <thead>
                    <tr>
                        <th class="period-col">
                            <i class="fas fa-clock"></i> Period
                        </th>
                        @foreach($timetable->days as $dayKey)
                            <th>{{ $allDays[$dayKey] ?? $dayKey }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($periods as $period)
                    <tr class="{{ $period->is_break ? 'break-row' : '' }}">
                        <!-- Period Info Column -->
                        <td class="period-info">
                            <span class="period-info-label">{{ $period->label }}</span>
                            <span class="period-info-time">{{ $period->time_range }}</span>
                            <span class="period-info-duration">{{ $period->duration }}</span>
                            @if($period->is_break)
                                <span class="badge badge-pending" style="font-size:0.68rem; margin-top:3px;">Break</span>
                            @endif
                        </td>

                        @foreach($timetable->days as $dayKey)
                        @php
                            $entry       = $grid[$period->id][$dayKey] ?? null;
                            $entryType   = $entry?->type ?? ($period->is_break ? 'break' : 'free');
                            $subjectId   = $entry?->subject_id ?? '';
                            $teacherId   = $entry?->teacher_id ?? '';
                            $customLabel = $entry?->custom_label ?? '';
                            $cellIdx     = "e_{$period->id}_{$dayKey}";
                        @endphp

                        <td class="tt-cell">
                            <div class="tt-cell-edit" id="cell-{{ $cellIdx }}"
                                 data-type="{{ $entryType }}">

                                <!-- Hidden fields -->
                                <input type="hidden" name="entries[{{ $cellIdx }}][period_template_id]"
                                       value="{{ $period->id }}">
                                <input type="hidden" name="entries[{{ $cellIdx }}][day]"
                                       value="{{ $dayKey }}">

                                <!-- Type selector -->
                                <select class="tt-type-select"
                                        name="entries[{{ $cellIdx }}][type]"
                                        onchange="onTypeChange('{{ $cellIdx }}', this.value)"
                                        id="type-{{ $cellIdx }}">
                                    <option value="free"  {{ $entryType === 'free'  ? 'selected' : '' }}>— Free —</option>
                                    <option value="lesson"{{ $entryType === 'lesson'? 'selected' : '' }}>📚 Lesson</option>
                                    <option value="break" {{ $entryType === 'break' ? 'selected' : '' }}>☕ Break</option>
                                </select>

                                <!-- Lesson fields -->
                                <div id="lesson-{{ $cellIdx }}"
                                     style="{{ $entryType !== 'lesson' ? 'display:none;' : '' }}">
                                    <select class="tt-sub-select"
                                            name="entries[{{ $cellIdx }}][subject_id]">
                                        <option value="">Subject...</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}"
                                                    {{ $subjectId == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <select class="tt-teacher-select"
                                            name="entries[{{ $cellIdx }}][teacher_id]">
                                        <option value="">Teacher...</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}"
                                                    {{ $teacherId == $teacher->id ? 'selected' : '' }}>
                                                {{ $teacher->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Break fields -->
                                <div id="break-{{ $cellIdx }}"
                                     style="{{ $entryType !== 'break' ? 'display:none;' : '' }}">
                                    <input type="text" class="tt-custom-label-input"
                                           name="entries[{{ $cellIdx }}][custom_label]"
                                           value="{{ $customLabel }}"
                                           placeholder="Break label...">
                                </div>
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="d-flex align-items-center gap-2 no-print" style="margin-bottom:1rem; flex-wrap:wrap;">
    <button type="button" class="btn-outline-secondary btn btn-sm" onclick="fillAllBreaks()">
        <i class="fas fa-coffee"></i> Mark All Breaks
    </button>
    <button type="button" class="btn-outline-secondary btn btn-sm" onclick="clearAll()">
        <i class="fas fa-eraser"></i> Clear All
    </button>
    <span style="color:var(--text-muted); font-size:0.83rem;">
        <i class="fas fa-info-circle"></i>
        Break rows are highlighted in amber — they default to Break type.
    </span>
</div>

<div style="display:flex; gap:0.8rem;">
    <button type="submit" class="btn btn-primary btn-lg">
        <i class="fas fa-save"></i> Save Timetable
    </button>
    <a href="{{ route('admin.timetable.show', $timetable) }}" class="btn-outline-secondary btn btn-lg">
        Cancel
    </a>
</div>
</form>

@endif

<script>
function onTypeChange(cellId, type) {
    const cell    = document.getElementById('cell-' + cellId);
    const lesson  = document.getElementById('lesson-' + cellId);
    const brk     = document.getElementById('break-' + cellId);

    cell.dataset.type    = type;
    lesson.style.display = type === 'lesson' ? '' : 'none';
    brk.style.display    = type === 'break'  ? '' : 'none';
}

function fillAllBreaks() {
    // Find all break-row cells and set them to "break" type
    document.querySelectorAll('tr.break-row .tt-type-select').forEach(sel => {
        sel.value = 'break';
        const cellId = sel.name.match(/entries\[(.*?)\]\[type\]/)?.[1];
        if (cellId) onTypeChange(cellId, 'break');
    });
}

function clearAll() {
    if (!confirm('Clear all entries in this timetable?')) return;
    document.querySelectorAll('.tt-type-select').forEach(sel => {
        sel.value = 'free';
        const cellId = sel.name.match(/entries\[(.*?)\]\[type\]/)?.[1];
        if (cellId) onTypeChange(cellId, 'free');
    });
}
</script>
@endsection