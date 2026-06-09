@extends('layouts.teacher')
@section('title', 'Enter Marks')
@section('page-title', 'Enter Marks')

@section('content')
    <div class="page-header">
        <div>
            <div class="page-header-title">
                {{ $subject->name }} — {{ $weight?->label ?? $examType }}
            </div>
            <div class="page-header-sub">
                {{ $subject->schoolClass->name ?? '' }}
                &bull; {{ \App\Services\PerformanceService::TERMS[$term] ?? "Term $term" }}
                &bull; Academic Year: <strong>{{ \App\Helpers\AcademicYearContext::current()?->name }}</strong>
                @if ($weight)
                    &bull; Weight: <strong>{{ $weight->weight }}%</strong>
                @endif
            </div>
        </div>
        <a href="{{ route('teacher.performance.subjects') }}" class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('teacher.performance.enter-marks', $subject) }}" class="mb-2 card no-print"
        style="border-left:4px solid var(--primary-light);">
        <div class="card-body">
            <div class="filter-bar">
                <div>
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year" class="form-select">
                        @foreach ($years as $year)
                            <option value="{{ $year }}" {{ $academicYear === $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Term</label>
                    <select name="term" class="form-select">
                        @foreach ($terms as $num => $label)
                            <option value="{{ $num }}" {{ $term == $num ? 'selected' : '' }}>{{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Exam Type</label>
                    <select name="exam_type" class="form-select">
                        @foreach ($weights as $w)
                            <option value="{{ $w->exam_type }}" {{ $examType === $w->exam_type ? 'selected' : '' }}>
                                {{ $w->label }} ({{ $w->weight }}%)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="align-self:flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Load
                    </button>
                </div>
            </div>
        </div>
    </form>

    @if ($students->isEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            No active students found in {{ $subject->schoolClass->name ?? 'this class' }}.
        </div>
    @else
        <form action="{{ route('teacher.performance.save-marks', $subject) }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="academic_year" value="{{ $academicYear }}">
            <input type="hidden" name="term" value="{{ $term }}">
            <input type="hidden" name="exam_type" value="{{ $examType }}">

            <div class="mb-2 card">
                <div class="card-header">
                    <div class="card-header-title">
                        <i class="fas fa-users"></i>
                        {{ $students->count() }} Students
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label" style="margin:0; font-size:0.85rem;">Exam Date *</label>
                        <input type="date" name="exam_date" class="form-control"
                            value="{{ old('exam_date', today()->toDateString()) }}" style="width:160px;">
                        <label class="form-label" style="margin:0; font-size:0.85rem;">Out of</label>
                        <input type="number" name="total_marks" id="total-marks" class="form-control"
                            value="{{ old('total_marks', 100) }}" min="1" max="1000" style="width:80px;"
                            onchange="updateMaxValues()">
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="marks-table">
                        <thead>
                            <tr>
                                <th style="text-align:left; min-width:200px;">#&nbsp;&nbsp;&nbsp;Student</th>
                                <th style="text-align:center; min-width:120px;">
                                    Marks <span style="font-weight:400; opacity:0.75;">(out of <span
                                            id="total-display">100</span>)</span>
                                </th>
                                <th style="text-align:center; min-width:80px;">%</th>
                                <th style="min-width:150px;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $i => $student)
                                @php $existing = $existingMarks->get($student->id); @endphp
                                <tr id="row-{{ $student->id }}">
                                    <td>
                                        <div style="display:flex; align-items:center; gap:0.7rem;">
                                            <span style="font-size:0.8rem; color:var(--text-muted); min-width:22px;">
                                                {{ $i + 1 }}
                                            </span>
                                            <div class="sidebar-user-avatar"
                                                style="width:30px; height:30px; font-size:0.78rem; flex-shrink:0;">
                                                {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight:600; font-size:0.88rem;">
                                                    {{ $student->full_name }}
                                                </div>
                                                <div style="font-size:0.75rem; color:var(--text-muted);">
                                                    {{-- Roll from enrollment --}}
                                                    Roll: {{ $student->enrollment?->roll_number ?? '—' }}
                                                    &bull;
                                                    Sec: {{ $student->enrollment?->section?->name ?? '—' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td style="text-align:center;">
                                        <input type="number" name="marks[{{ $student->id }}][obtained]"
                                            id="marks-{{ $student->id }}"
                                            class="marks-input {{ $existing ? ($existing->percentage >= 75 ? 'mark-high' : ($existing->percentage >= 50 ? 'mark-mid' : 'mark-low')) : '' }}"
                                            value="{{ old("marks.{$student->id}.obtained", $existing?->marks_obtained) }}"
                                            min="0" max="100" placeholder="—"
                                            oninput="updateRow({{ $student->id }}, this.value)">
                                    </td>
                                    <td style="text-align:center;">
                                        <span id="pct-{{ $student->id }}"
                                            style="font-weight:700; font-size:0.9rem; color:var(--text-muted);">
                                            @if ($existing)
                                                {{ $existing->percentage }}%
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <input type="text" name="marks[{{ $student->id }}][remarks]"
                                            class="form-control" style="font-size:0.82rem;"
                                            value="{{ old("marks.{$student->id}.remarks", $existing?->remarks) }}"
                                            placeholder="Optional">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="display:flex; gap:0.8rem;">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Marks
                </button>
                <a href="{{ route('teacher.performance.subjects') }}" class="btn-outline-secondary btn btn-lg">
                    Cancel
                </a>
            </div>
        </form>
    @endif

    <script>
        function updateRow(studentId, value) {
            const total = parseFloat(document.getElementById('total-marks').value) || 100;
            const marks = parseFloat(value);
            const pctEl = document.getElementById('pct-' + studentId);
            const input = document.getElementById('marks-' + studentId);

            if (isNaN(marks) || value === '') {
                pctEl.textContent = '—';
                pctEl.style.color = 'var(--text-muted)';
                input.className = 'marks-input';
                return;
            }

            const pct = Math.min(100, (marks / total) * 100);
            pctEl.textContent = pct.toFixed(1) + '%';

            if (pct >= 75) {
                pctEl.style.color = 'var(--success)';
                input.className = 'marks-input mark-high';
            } else if (pct >= 50) {
                pctEl.style.color = '#7a5800';
                input.className = 'marks-input mark-mid';
            } else {
                pctEl.style.color = 'var(--danger)';
                input.className = 'marks-input mark-low';
            }
        }

        function updateMaxValues() {
            const total = document.getElementById('total-marks').value;
            document.getElementById('total-display').textContent = total;
            document.querySelectorAll('.marks-input').forEach(input => {
                input.max = total;
            });
        }
    </script>
@endsection
