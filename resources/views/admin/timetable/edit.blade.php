@extends('layouts.app')
@section('title', 'Edit Timetable')
@section('page-title', 'Edit Timetable')

@section('content')

{{-- Page Header --}}
<div class="page-header no-print">
    <div>
        <div class="page-header-title">{{ $timetable->name }}</div>
        <div class="page-header-sub">
            {{ $timetable->schoolClass->name ?? '' }} —
            Section {{ $timetable->section->name ?? '' }}
            &bull; {{ $timetable->academic_year }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.timetable.show', $timetable) }}"
           class="btn-outline-primary btn btn-sm">
            <i class="fas fa-eye"></i> Preview
        </a>
        <a href="{{ route('admin.timetable.index') }}"
           class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

<div class="row">

    {{-- LEFT: Period Manager --}}
    <div class="col-4">
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-clock"></i> Time Periods
                </div>
                <span style="font-size:0.8rem; color:var(--text-muted);">
                    Shared across all days
                </span>
            </div>

            {{-- Add Period Form --}}
            <div style="padding:1rem 1.2rem; border-bottom:1px solid var(--border);
                        background:var(--light-bg);">
                <form action="{{ route('admin.timetable.periods.store', $timetable) }}"
                      method="POST" novalidate>
                    @csrf
                    <div class="mb-form">
                        <label class="form-label" style="font-size:0.8rem;">Label *</label>
                        <input type="text" name="label"
                               class="form-control {{ $errors->has('label') ? 'is-invalid' : '' }}"
                               value="{{ old('label') }}"
                               placeholder="Period 1, Lunch Break, Jummah...">
                        @error('label')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        <div class="mb-form col-6">
                            <label class="form-label" style="font-size:0.8rem;">Start *</label>
                            <input type="time" name="start_time"
                                   class="form-control {{ $errors->has('start_time') ? 'is-invalid' : '' }}"
                                   value="{{ old('start_time') }}">
                        </div>
                        <div class="mb-form col-6">
                            <label class="form-label" style="font-size:0.8rem;">End *</label>
                            <input type="time" name="end_time"
                                   class="form-control {{ $errors->has('end_time') ? 'is-invalid' : '' }}"
                                   value="{{ old('end_time') }}">
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:0.8rem;">
                        <input type="checkbox" name="is_break" id="is_break_add" value="1"
                               style="width:15px; height:15px; accent-color:var(--accent);">
                        <label for="is_break_add"
                               style="font-size:0.82rem; font-weight:600; cursor:pointer; margin:0;">
                            Break / Prayer / Assembly
                        </label>
                    </div>
                    <button type="submit" class="btn-block btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Period
                    </button>
                </form>
            </div>

            {{-- Periods List --}}
            <div id="periods-list">
                @forelse($periods as $period)
                <div class="period-row {{ $period->is_break ? 'is-break' : 'is-lesson' }}"
                     data-id="{{ $period->id }}">
                    <i class="fas fa-grip-vertical"
                       style="color:var(--border); cursor:grab; font-size:0.85rem;"></i>
                    <div style="flex:1; min-width:0;">
                        <div class="period-row-label">{{ $period->label }}</div>
                        <div class="period-row-time">{{ $period->time_range }}</div>
                        <div class="period-row-dur">{{ $period->duration }}</div>
                    </div>
                    <span class="badge {{ $period->is_break ? 'badge-pending' : 'badge-primary' }}"
                          style="font-size:0.68rem;">
                        {{ $period->is_break ? 'Break' : 'Lesson' }}
                    </span>
                    <button class="btn-outline-primary btn btn-sm"
                            style="padding:0.2rem 0.5rem;"
                            onclick="openEditPeriod(
                                {{ $period->id }},
                                '{{ addslashes($period->label) }}',
                                '{{ substr($period->start_time, 0, 5) }}',
                                '{{ substr($period->end_time, 0, 5) }}',
                                {{ $period->is_break ? 'true' : 'false' }}
                            )">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('admin.timetable.periods.destroy', [$timetable, $period]) }}"
                          method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="btn-outline-danger btn btn-sm"
                                style="padding:0.2rem 0.5rem;"
                                onclick="return confirm('Remove period \'{{ addslashes($period->label) }}\'?')">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
                @empty
                <div style="padding:1.5rem; text-align:center; color:var(--text-muted); font-size:0.85rem;">
                    No periods yet. Add your first period above.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- RIGHT: Schedule Grid per Day --}}
    <div class="col-8">

        @if($periods->isEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Define at least one time period on the left before filling the schedule.
        </div>
        @else

        {{-- Day Tabs --}}
        <div class="card">
            <div style="padding:0.8rem 1rem; border-bottom:1px solid var(--border);">
                <div class="day-tabs">
                    @foreach($days as $day)
                    @php
                        $hasEntries = $timetable->entries
                            ->where('day', $day)
                            ->where('type', 'lesson')
                            ->count() > 0;
                    @endphp
                    <a href="{{ route('admin.timetable.edit', ['timetable' => $timetable->id, 'day' => $day]) }}"
                       class="day-tab {{ $activeDay === $day ? 'active' : '' }} {{ $hasEntries ? 'has-data' : '' }}">
                        {{ \App\Models\Timetable::DAY_LABELS[$day] ?? $day }}
                        @if($hasEntries)
                            <i class="fas fa-check-circle"
                               style="font-size:0.65rem; color:var(--success);"></i>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Grid for Active Day --}}
            <form action="{{ route('admin.timetable.save-grid', $timetable) }}" method="POST">
                @csrf
                <input type="hidden" name="day" value="{{ $activeDay }}">

                <div class="timetable-grid-wrapper">
                    <table class="timetable-grid">
                        <thead>
                            <tr>
                                <th class="col-period">
                                    <i class="fas fa-clock"></i>
                                    {{ \App\Models\Timetable::DAY_LABELS[$activeDay] ?? $activeDay }}
                                </th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th style="width:80px;">Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($periods as $idx => $period)
                            @php
                                $entry = $grid[$period->id][$activeDay] ?? null;
                                $type  = $entry?->type ?? ($period->is_break ? 'break' : 'free');
                                $k     = "e_{$idx}";
                            @endphp
                            <tr class="{{ $period->is_break ? 'is-break-row' : '' }}">

                                {{-- Period Info --}}
                                <td class="col-period">
                                    <span class="tt-period-label">{{ $period->label }}</span>
                                    <span class="tt-period-time">{{ $period->time_range }}</span>
                                    <span class="tt-period-dur">{{ $period->duration }}</span>
                                </td>

                                {{-- Subject --}}
                                <td class="tt-cell">
                                    <div class="tt-edit-cell" id="cell-{{ $k }}" data-type="{{ $type }}">
                                        <input type="hidden"
                                               name="entries[{{ $k }}][timetable_period_id]"
                                               value="{{ $period->id }}">

                                        {{-- Subject select (lesson only) --}}
                                        <select name="entries[{{ $k }}][subject_id]"
                                                class="tt-select"
                                                id="subj-{{ $k }}"
                                                style="{{ $type !== 'lesson' ? 'display:none;' : '' }}">
                                            <option value="">— Subject —</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}"
                                                        {{ $entry?->subject_id == $subject->id ? 'selected' : '' }}>
                                                    {{ $subject->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        {{-- Custom label (break only) --}}
                                        <input type="text"
                                               name="entries[{{ $k }}][custom_label]"
                                               class="tt-text-input"
                                               id="lbl-{{ $k }}"
                                               value="{{ $entry?->custom_label ?? $period->label }}"
                                               placeholder="Break label..."
                                               style="{{ $type !== 'break' ? 'display:none;' : '' }}">

                                        {{-- Free placeholder --}}
                                        <span id="free-{{ $k }}"
                                              style="color:var(--border); font-size:0.78rem; text-align:center;
                                                     {{ $type !== 'free' ? 'display:none;' : '' }}">
                                            — Free —
                                        </span>
                                    </div>
                                </td>

                                {{-- Teacher --}}
                                <td class="tt-cell">
                                    <div class="tt-edit-cell" data-type="{{ $type }}">
                                        <select name="entries[{{ $k }}][teacher_id]"
                                                class="tt-select"
                                                id="tchr-{{ $k }}"
                                                style="{{ $type !== 'lesson' ? 'display:none;' : '' }}">
                                            <option value="">— Teacher —</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}"
                                                        {{ $entry?->teacher_id == $teacher->id ? 'selected' : '' }}>
                                                    {{ $teacher->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span id="tchr-empty-{{ $k }}"
                                              style="color:var(--border); font-size:0.78rem;
                                                     {{ $type === 'lesson' ? 'display:none;' : '' }}">—</span>
                                    </div>
                                </td>

                                {{-- Type selector --}}
                                <td class="tt-cell" style="min-width:90px; width:90px;">
                                    <div class="tt-edit-cell">
                                        <select name="entries[{{ $k }}][type]"
                                                class="tt-select"
                                                onchange="onTypeChange('{{ $k }}', this.value)">
                                            <option value="free"   {{ $type === 'free'   ? 'selected' : '' }}>Free</option>
                                            <option value="lesson" {{ $type === 'lesson' ? 'selected' : '' }}>Lesson</option>
                                            <option value="break"  {{ $type === 'break'  ? 'selected' : '' }}>Break</option>
                                        </select>
                                    </div>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Save Bar --}}
                <div style="padding:1rem 1.2rem; border-top:1px solid var(--border);
                            display:flex; gap:0.8rem; align-items:center; flex-wrap:wrap;
                            background:var(--light-bg);">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save {{ \App\Models\Timetable::DAY_LABELS[$activeDay] ?? $activeDay }}
                        @php
                            $nextDay = $days[array_search($activeDay, $days) + 1] ?? null;
                        @endphp
                        @if($nextDay)
                            &rarr; Go to {{ \App\Models\Timetable::DAY_LABELS[$nextDay] ?? $nextDay }}
                        @endif
                    </button>
                    <a href="{{ route('admin.timetable.show', $timetable) }}"
                       class="btn-outline-secondary btn">
                        <i class="fas fa-eye"></i> Preview Full Timetable
                    </a>
                    <span style="font-size:0.82rem; color:var(--text-muted);">
                        <i class="fas fa-info-circle"></i>
                        Saving one day at a time. Tabs with
                        <i class="fas fa-check-circle" style="color:var(--success);"></i>
                        have lessons filled.
                    </span>
                </div>
            </form>
        </div>

        @endif
    </div>
</div>

{{-- Edit Period Modal --}}
<div id="edit-period-modal"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
            z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius); padding:1.8rem;
                width:100%; max-width:420px; box-shadow:var(--shadow-md);">
        <h3 style="font-family:var(--font-display); color:var(--primary); margin-bottom:1.2rem;">
            Edit Period
        </h3>
        <form id="edit-period-form" method="POST" novalidate>
            @csrf @method('PUT')
            <div class="mb-form">
                <label class="form-label">Label *</label>
                <input type="text" name="label" id="ep-label" class="form-control">
            </div>
            <div class="row">
                <div class="mb-form col-6">
                    <label class="form-label">Start Time *</label>
                    <input type="time" name="start_time" id="ep-start" class="form-control">
                </div>
                <div class="mb-form col-6">
                    <label class="form-label">End Time *</label>
                    <input type="time" name="end_time" id="ep-end" class="form-control">
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px; padding:0.7rem;
                        background:var(--light-bg); border-radius:var(--radius-sm);
                        margin-bottom:1rem;">
                <input type="checkbox" name="is_break" id="ep-break" value="1"
                       style="width:17px; height:17px; accent-color:var(--accent); cursor:pointer;">
                <label for="ep-break"
                       style="cursor:pointer; margin:0; font-size:0.88rem; font-weight:600;">
                    Break / Prayer / Assembly
                </label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save
                </button>
                <button type="button" class="btn-outline-secondary btn"
                        onclick="document.getElementById('edit-period-modal').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Type change handler ─────────────────────────────────────────────────────
function onTypeChange(k, type) {
    // Subject column
    const subj      = document.getElementById('subj-' + k);
    const lbl       = document.getElementById('lbl-' + k);
    const freeLbl   = document.getElementById('free-' + k);
    const tchr      = document.getElementById('tchr-' + k);
    const tchrEmpty = document.getElementById('tchr-empty-' + k);

    if (subj)      subj.style.display      = type === 'lesson' ? '' : 'none';
    if (lbl)       lbl.style.display       = type === 'break'  ? '' : 'none';
    if (freeLbl)   freeLbl.style.display   = type === 'free'   ? '' : 'none';
    if (tchr)      tchr.style.display      = type === 'lesson' ? '' : 'none';
    if (tchrEmpty) tchrEmpty.style.display = type === 'lesson' ? 'none' : '';

    // Update cell background
    const cell = document.getElementById('cell-' + k);
    if (cell) cell.dataset.type = type;
}

// ── Edit period modal ────────────────────────────────────────────────────────
function openEditPeriod(id, label, start, end, isBreak) {
    const baseUrl = '{{ route("admin.timetable.periods.update", [$timetable, "__ID__"]) }}';
    document.getElementById('edit-period-form').action = baseUrl.replace('__ID__', id);
    document.getElementById('ep-label').value  = label;
    document.getElementById('ep-start').value  = start;
    document.getElementById('ep-end').value    = end;
    document.getElementById('ep-break').checked = isBreak;
    document.getElementById('edit-period-modal').style.display = 'flex';
}

// ── Drag-to-reorder periods ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('periods-list');
    if (!list) return;

    const script  = document.createElement('script');
    script.src    = 'https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js';
    script.onload = function () {
        Sortable.create(list, {
            handle: '.fa-grip-vertical',
            animation: 150,
            onEnd: function () {
                const order = Array.from(list.querySelectorAll('[data-id]'))
                    .map(el => el.dataset.id);
                fetch('{{ route("admin.timetable.periods.reorder", $timetable) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order })
                });
            }
        });
    };
    document.head.appendChild(script);
});
</script>
@endsection