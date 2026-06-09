@extends('layouts.app')
@section('title', 'Marks & Grades')
@section('page-title', 'Marks & Grades')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Marks & Grades</div>
        <div class="page-header-sub">All student marks entered by teachers</div>
    </div>
    <a href="{{ route('admin.performance.class-report') }}" class="btn btn-primary">
        <i class="fa-table fas"></i> Class Report
    </a>
</div>

{{-- Filters --}}
<form method="GET">
    <div class="filter-bar">
        <div>
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}"
                            {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Subject</label>
            <select name="subject_id" class="form-select">
                <option value="">All Subjects</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}"
                            {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Term</label>
            <select name="term" class="form-select">
                <option value="">All Terms</option>
                @foreach($terms as $num => $label)
                    <option value="{{ $num }}"
                            {{ request('term') == $num ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Academic Year</label>
            <select name="academic_year" class="form-select">
                <option value="">All Years</option>
                @foreach($years as $year)
                    <option value="{{ $year }}"
                            {{ request('academic_year') === $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="{{ route('admin.performance.index') }}"
               class="btn-outline-secondary btn">Clear</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Term</th>
                    <th>Exam Type</th>
                    <th>Marks</th>
                    <th>%</th>
                    <th>Entered By</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($marks as $mark)
                @php $pct = $mark->percentage; @endphp
                <tr>
                    <td>
                        <strong>{{ $mark->student->full_name ?? '—' }}</strong>
                        <div style="font-size:0.77rem; color:var(--text-muted);">
                            {{ $mark->student->roll_number ?? '' }}
                        </div>
                    </td>
                    <td>
                        {{ $mark->student->schoolClass->name ?? '—' }}
                        @if($mark->student->section)
                            <span style="color:var(--text-muted);">
                                – {{ $mark->student->section->name }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $mark->subject->name ?? '—' }}</strong>
                    </td>
                    <td>
                        <span class="badge badge-info" style="font-size:0.75rem;">
                            Term {{ $mark->term }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-primary" style="font-size:0.75rem;">
                            {{ $mark->exam_type }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ $mark->marks_obtained }}</strong>
                        <span style="color:var(--text-muted); font-size:0.82rem;">
                            / {{ $mark->total_marks }}
                        </span>
                    </td>
                    <td>
                        <span style="font-weight:700;
                                     color:{{ $pct >= 75
                                        ? 'var(--success)'
                                        : ($pct >= 50 ? 'var(--warning)' : 'var(--danger)') }};">
                            {{ $pct }}%
                        </span>
                    </td>
                    <td style="font-size:0.83rem; color:var(--text-muted);">
                        {{ $mark->teacher->full_name ?? '—' }}
                    </td>
                    <td style="font-size:0.82rem; color:var(--text-muted);">
                        {{ $mark->exam_date->format('d M, Y') }}
                    </td>
                    <td>
                        <a href="{{ route('admin.performance.student-report', $mark->student) }}"
                           class="btn-outline-primary btn btn-sm">
                            <i class="fas fa-chart-line"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10"
                        style="text-align:center; color:var(--text-muted); padding:3rem;">
                        <i class="fas fa-chart-line"
                           style="font-size:3rem; display:block;
                                  margin-bottom:1rem; color:var(--border);"></i>
                        No marks found. Teachers need to enter marks first.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($marks->hasPages())
    <div style="padding:1rem 1.4rem; border-top:1px solid var(--border);">
        {{ $marks->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection