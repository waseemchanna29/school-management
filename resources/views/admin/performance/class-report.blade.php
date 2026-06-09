@extends('layouts.app')
@section('title', 'Class Report')
@section('page-title', 'Class Performance Report')

@section('content')
    <div class="page-header">
        <div>
            <div class="page-header-title">Class Performance Report</div>
            <div class="page-header-sub">
                View subject-wise marks for a class, term and exam type
            </div>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn-outline-primary btn btn-sm no-print">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="{{ route('admin.performance.index') }}" class="btn-outline-secondary btn btn-sm no-print">
                <i class="fas fa-list"></i> All Marks
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" id="report-form" class="no-print">
        <div class="mb-3 card">
            <div class="card-body">
                <div class="row">
                    <div class="mb-form col-3">
                        <label class="form-label">Class *</label>
                        <select name="class_id" class="form-select" id="class-select" onchange="loadSubjects(this.value)">
                            <option value="">-- Select Class --</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form col-3">
                        <label class="form-label">Subject *</label>
                        <select name="subject_id" class="form-select" id="subject-select">
                            <option value="">-- Select Subject --</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form col-2">
                        <label class="form-label">Term</label>
                        <select name="term" class="form-select">
                            @foreach ($terms as $num => $label)
                                <option value="{{ $num }}" {{ $term == $num ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form col-2">
                        <label class="form-label">Exam Type</label>
                        <select name="exam_type" class="form-select">
                            @foreach ($weights as $weight)
                                <option value="{{ $weight->exam_type }}"
                                    {{ $examType === $weight->exam_type ? 'selected' : '' }}>
                                    {{ $weight->label }} ({{ $weight->weight }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form col-2">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year_id" class="form-select">
                            @foreach ($years as $ay)
                                <option value="{{ $ay->id }}" {{ $yearId == $ay->id ? 'selected' : '' }}>
                                    {{ $ay->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display:flex; gap:0.8rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Generate Report
                    </button>
                    <a href="{{ route('admin.performance.class-report') }}" class="btn-outline-secondary btn">
                        Clear
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- Results --}}
    @if ($classId && $subjectId && $marks->isNotEmpty())

        {{-- Summary Stats --}}
        @php
            $avgPct = round($marks->avg('percentage'), 1);
            $highest = $marks->max('percentage');
            $lowest = $marks->min('percentage');
            $passCount = $marks->filter(fn($m) => $m->percentage >= 45)->count();
            $failCount = $marks->count() - $passCount;
        @endphp

        <div class="fee-summary-bar" style="margin-bottom:1.4rem;">
            <div class="fee-summary-card total">
                <span class="fee-summary-amount">{{ $marks->count() }}</span>
                <span class="fee-summary-label">Total Students</span>
            </div>
            <div class="fee-summary-card paid">
                <span class="fee-summary-amount">{{ $avgPct }}%</span>
                <span class="fee-summary-label">Class Average</span>
            </div>
            <div class="fee-summary-card total" style="border-top-color:var(--success);">
                <span class="fee-summary-amount" style="color:var(--success);">{{ $highest }}%</span>
                <span class="fee-summary-label">Highest</span>
            </div>
            <div class="fee-summary-card balance">
                <span class="fee-summary-amount">{{ $lowest }}%</span>
                <span class="fee-summary-label">Lowest</span>
            </div>
            <div class="fee-summary-card paid" style="border-top-color:var(--success);">
                <span class="fee-summary-amount" style="color:var(--success);">{{ $passCount }}</span>
                <span class="fee-summary-label">Passed</span>
            </div>
            <div class="fee-summary-card balance">
                <span class="fee-summary-amount">{{ $failCount }}</span>
                <span class="fee-summary-label">Failed</span>
            </div>
        </div>

        {{-- Chart --}}
        <div class="mb-3 card no-print">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-chart-bar"></i> Score Distribution
                </div>
            </div>
            <div class="card-body">
                <canvas id="class-chart" style="max-height:220px;"></canvas>
            </div>
        </div>

        {{-- Marks Table --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-list"></i>
                    {{ $marks->first()->subject->name ?? '' }}
                    — {{ $terms[$term] ?? "Term $term" }}
                    — {{ collect($weights)->firstWhere('exam_type', $examType)?->label ?? $examType }}
                </div>
                <span style="font-size:0.83rem; color:var(--text-muted);">
                    {{ $academicYear }}
                </span>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Section</th>
                            <th style="text-align:center;">Marks</th>
                            <th style="text-align:center;">%</th>
                            <th style="text-align:center;">Grade</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($marks->sortByDesc('percentage') as $i => $mark)
                            @php
                                $pct = $mark->percentage;
                                $grade = $mark->grade;
                                $color = $pct >= 75 ? 'var(--success)' : ($pct >= 50 ? '#7a5800' : 'var(--danger)');
                            @endphp
                            <tr>
                                <td style="color:var(--text-muted);">{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $mark->student->full_name ?? '—' }}</strong>
                                    <div style="font-size:0.77rem; color:var(--text-muted);">
                                        {{ $mark->student->currentEnrollment?->roll_number ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    {{ $mark->student->section->name ?? '—' }}
                                </td>
                                <td style="text-align:center;">
                                    <strong>{{ $mark->marks_obtained }}</strong>
                                    <span style="color:var(--text-muted); font-size:0.82rem;">
                                        / {{ $mark->total_marks }}
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <strong style="color:{{ $color }}; font-size:1rem;">
                                        {{ $pct }}%
                                    </strong>
                                </td>
                                <td style="text-align:center;">
                                    @if ($grade)
                                        <span class="grade-badge" style="{{ $grade->color_style }}">
                                            {{ $grade->grade }}
                                        </span>
                                    @else
                                        <span style="color:var(--border);">—</span>
                                    @endif
                                </td>
                                <td style="min-width:160px;">
                                    <div style="display:flex; align-items:center; gap:0.6rem;">
                                        <div class="perf-bar-wrap" style="width:100px;">
                                            <div class="perf-bar-fill"
                                                style="width:{{ $pct }}%;
                                            background:{{ $color }};">
                                            </div>
                                        </div>
                                        <span style="font-size:0.78rem; color:{{ $color }}; font-weight:600;">
                                            {{ $grade?->description ?? '' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Footer avg row --}}
                    <tfoot>
                        <tr style="background:var(--light-bg); font-weight:700;">
                            <td colspan="3" style="padding:0.75rem 1rem; text-align:right; color:var(--primary);">
                                Class Average
                            </td>
                            <td style="text-align:center; padding:0.75rem 1rem;">
                                {{ round($marks->avg('marks_obtained'), 1) }}
                                <span style="color:var(--text-muted); font-weight:400; font-size:0.82rem;">
                                    / {{ $marks->first()->total_marks }}
                                </span>
                            </td>
                            <td
                                style="text-align:center; padding:0.75rem 1rem;
                               color:{{ $avgPct >= 75 ? 'var(--success)' : ($avgPct >= 50 ? 'var(--warning)' : 'var(--danger)') }};">
                                {{ $avgPct }}%
                            </td>
                            <td colspan="2" style="padding:0.75rem 1rem;"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @elseif($classId && $subjectId && $marks->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            No marks found for the selected filters.
            Make sure teachers have entered marks for this subject and exam type.
        </div>
    @elseif(!$classId || !$subjectId)
        <div class="card">
            <div class="card-body" style="text-align:center; padding:4rem;">
                <i class="fa-table fas"
                    style="font-size:3.5rem; color:var(--border);
                  display:block; margin-bottom:1rem;"></i>
                <h3 style="color:var(--text-muted); margin-bottom:0.5rem;">
                    Select Filters to Generate Report
                </h3>
                <p style="color:var(--text-muted); font-size:0.9rem;">
                    Choose a class, subject, term and exam type above,
                    then click <strong>Generate Report</strong>.
                </p>
            </div>
        </div>
    @endif

    {{-- Chart.js --}}
    @if ($marks->isNotEmpty())
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('class-chart');
                if (!ctx) return;

                const labels = @json($marks->sortByDesc('percentage')->values()->map(fn($m) => $m->student->full_name ?? 'Unknown'));
                const scores = @json($marks->sortByDesc('percentage')->values()->map(fn($m) => $m->percentage));
                const colors = scores.map(s =>
                    s >= 75 ? 'rgba(25,135,84,0.75)' :
                    s >= 50 ? 'rgba(255,193,7,0.75)' :
                    'rgba(220,53,69,0.75)'
                );

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Score %',
                            data: scores,
                            backgroundColor: colors,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ctx.parsed.y + '%'
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    font: {
                                        size: 10
                                    },
                                    maxRotation: 45
                                }
                            },
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    stepSize: 10,
                                    callback: v => v + '%'
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endif

    {{-- Dynamic subject loading when class changes --}}
    <script>
        function loadSubjects(classId) {
            const sel = document.getElementById('subject-select');
            sel.innerHTML = '<option value="">Loading...</option>';

            if (!classId) {
                sel.innerHTML = '<option value="">-- Select Subject --</option>';
                return;
            }

            // Filter subjects client-side if already loaded,
            // or do a simple page reload with class_id set
            const url = new URL(window.location.href);
            url.searchParams.set('class_id', classId);
            url.searchParams.delete('subject_id');
            window.location.href = url.toString();
        }
    </script>
@endsection
