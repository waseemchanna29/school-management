@extends('layouts.app')
@section('title', 'Student Performance')
@section('page-title', 'Student Performance Report')

@section('content')
<div class="page-header no-print">
    <div class="d-flex align-items-center gap-3">
        <div class="profile-avatar-placeholder" style="width:52px; height:52px; font-size:1.3rem;">
            {{ strtoupper(substr($student->full_name, 0, 1)) }}
        </div>
        <div>
            <div class="page-header-title">{{ $student->full_name }}</div>
            <div class="page-header-sub">
                <code>{{ $student->roll_number }}</code>
                &bull; {{ $student->schoolClass->name ?? '' }}
                @if($student->section) — Section {{ $student->section->name }} @endif
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-outline-primary btn btn-sm">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('admin.students.show', $student) }}"
           class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Profile
        </a>
    </div>
</div>

{{-- Year + Term Selector --}}
<form method="GET" class="no-print" style="margin-bottom:1.4rem;">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div>
            <label class="form-label">Academic Year</label>
            <select name="academic_year" class="form-select" onchange="this.form.submit()">
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ $academicYear === $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>
        {{-- Term Tabs --}}
        <div style="align-self:flex-end;">
            <div class="term-tabs">
                @foreach($terms as $num => $label)
                <a href="{{ route('admin.performance.student-report', ['student' => $student->id, 'term' => $num, 'academic_year' => $academicYear]) }}"
                   class="term-tab {{ $term == $num ? 'active' : '' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>
    </div>
</form>

{{-- Report Card Header --}}
@if(!empty($report['subject_results']))
<div style="margin-bottom:1.4rem;">
    <div class="report-card-header">
        <div>
            <div class="report-card-title">Performance Report Card</div>
            <div style="color:rgba(255,255,255,0.7); font-size:0.88rem; margin-top:3px;">
                {{ $terms[$term] ?? "Term $term" }} &bull; {{ $academicYear }}
                &bull; {{ $student->schoolClass->name ?? '' }}
            </div>
        </div>
        <div style="text-align:right;">
            <div class="report-overall-grade">
                {{ $report['overall_grade']?->grade ?? '—' }}
            </div>
            <div class="report-overall-pct">
                {{ $report['overall_avg'] }}% Overall
            </div>
            @if($report['overall_grade'])
            <div style="font-size:0.82rem; color:rgba(255,255,255,0.65); margin-top:3px;">
                GPA: {{ number_format($report['overall_grade']->gpa, 2) }}
                &bull; {{ $report['overall_grade']->description }}
            </div>
            @endif
        </div>
    </div>

    {{-- Subject Results --}}
    <div class="card" style="border-radius:0 0 var(--radius) var(--radius);">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        @foreach($report['weights'] as $w)
                        <th style="text-align:center; font-size:0.72rem;">
                            {{ $w->label }}<br>
                            <span style="font-weight:400; opacity:0.75;">({{ $w->weight }}%)</span>
                        </th>
                        @endforeach
                        <th style="text-align:center;">Weighted Avg</th>
                        <th style="text-align:center;">Grade</th>
                        <th style="text-align:center;">GPA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['subject_results'] as $result)
                    <tr>
                        <td><strong>{{ $result['subject']->name ?? '—' }}</strong></td>
                        @foreach($result['exam_breakdown'] as $breakdown)
                        <td style="text-align:center;">
                            @if($breakdown['percentage'] !== null)
                                @php $p = $breakdown['percentage']; @endphp
                                <span style="font-weight:700;
                                             color:{{ $p >= 75 ? 'var(--success)' : ($p >= 50 ? '#7a5800' : 'var(--danger)') }};">
                                    {{ $p }}%
                                </span>
                                <div style="font-size:0.72rem; color:var(--text-muted);">
                                    {{ $breakdown['mark']->marks_obtained }}/{{ $breakdown['mark']->total_marks }}
                                </div>
                            @else
                                <span style="color:var(--border);">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td style="text-align:center;">
                            @php $avg = $result['weighted_avg']; @endphp
                            <div style="display:flex; align-items:center; justify-content:center; gap:0.5rem;">
                                <div class="perf-bar-wrap" style="width:60px;">
                                    <div class="perf-bar-fill"
                                         style="width:{{ $avg }}%;
                                                background:{{ $avg >= 75 ? 'var(--success)' : ($avg >= 50 ? 'var(--warning)' : 'var(--danger)') }};"></div>
                                </div>
                                <strong style="color:{{ $avg >= 75 ? 'var(--success)' : ($avg >= 50 ? '#7a5800' : 'var(--danger)') }};">
                                    {{ $avg }}%
                                </strong>
                            </div>
                        </td>
                        <td style="text-align:center;">
                            @if($result['grade'])
                            <span class="grade-badge" style="{{ $result['grade']->color_style }}">
                                {{ $result['grade']->grade }}
                            </span>
                            @else
                            <span style="color:var(--border);">—</span>
                            @endif
                        </td>
                        <td style="text-align:center; font-weight:700;">
                            {{ $result['grade'] ? number_format($result['grade']->gpa, 2) : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:var(--primary); color:var(--white);">
                        <td colspan="{{ $report['weights']->count() + 1 }}"
                            style="text-align:right; font-weight:700; padding:0.8rem 1rem;">
                            Overall Average
                        </td>
                        <td style="text-align:center; padding:0.8rem 1rem;">
                            <strong style="color:var(--accent-light); font-size:1rem;">
                                {{ $report['overall_avg'] }}%
                            </strong>
                        </td>
                        <td style="text-align:center; padding:0.8rem 1rem;">
                            @if($report['overall_grade'])
                            <span class="grade-badge" style="{{ $report['overall_grade']->color_style }}">
                                {{ $report['overall_grade']->grade }}
                            </span>
                            @endif
                        </td>
                        <td style="text-align:center; padding:0.8rem 1rem; font-weight:700; color:var(--accent-light);">
                            {{ $report['overall_grade'] ? number_format($report['overall_grade']->gpa, 2) : '—' }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Grading Scale Reference --}}
@if($report['scale'])
<div class="card no-print" style="margin-top:1rem;">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-star-half-alt"></i>
            Applied Grading Scale: {{ $report['scale']->name }}
        </div>
    </div>
    <div class="card-body">
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            @foreach($report['scale']->items as $item)
            <span class="grade-badge" style="{{ $item->color_style }}; font-size:0.78rem;">
                {{ $item->grade }} ({{ $item->min_marks }}–{{ $item->max_marks }}%)
            </span>
            @endforeach
        </div>
    </div>
</div>
@endif

@else
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    No performance data found for <strong>{{ $terms[$term] ?? "Term $term" }}</strong>
    in {{ $academicYear }}.
    Teachers need to enter marks for this student's subjects.
</div>
@endif
@endsection