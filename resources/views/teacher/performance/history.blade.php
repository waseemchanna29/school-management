@extends('layouts.teacher')
@section('title', 'Marks History')
@section('page-title', 'Marks History')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Marks History</div>
        <div class="page-header-sub">Marks you have entered</div>
    </div>
    <a href="{{ route('teacher.performance.subjects') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-pen-ruler"></i> Enter Marks
    </a>
</div>

<form method="GET" style="margin-bottom:1.3rem;">
    <div class="filter-bar">
        <div>
            <label class="form-label">Academic Year</label>
            <select name="academic_year" class="form-select" onchange="this.form.submit()">
                @foreach(range(date('Y')-1, date('Y')+1) as $y)
                    @php $yr = $y . '-' . ($y+1); @endphp
                    <option value="{{ $yr }}" {{ $academicYear === $yr ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Term</label>
            <select name="term" class="form-select" onchange="this.form.submit()">
                @foreach($terms as $num => $label)
                    <option value="{{ $num }}" {{ $term == $num ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Subject</th>
                    <th>Class</th>
                    <th>Exam Type</th>
                    <th>Marks</th>
                    <th>%</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($marks as $mark)
                <tr>
                    <td>
                        <strong>{{ $mark->student->full_name }}</strong>
                        <div style="font-size:0.77rem; color:var(--text-muted);">
                            {{ $mark->student->roll_number }}
                        </div>
                    </td>
                    <td>{{ $mark->subject->name ?? '—' }}</td>
                    <td>{{ $mark->student->schoolClass->name ?? '—' }}</td>
                    <td>
                        <span class="badge badge-info">{{ $mark->exam_type }}</span>
                    </td>
                    <td>
                        <strong>{{ $mark->marks_obtained }}</strong>
                        <span style="color:var(--text-muted);">/ {{ $mark->total_marks }}</span>
                    </td>
                    <td>
                        @php $pct = $mark->percentage; @endphp
                        <span style="font-weight:700;
                                     color:{{ $pct >= 75 ? 'var(--success)' : ($pct >= 50 ? 'var(--warning)' : 'var(--danger)') }};">
                            {{ $pct }}%
                        </span>
                    </td>
                    <td style="font-size:0.83rem; color:var(--text-muted);">
                        {{ $mark->exam_date->format('d M, Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                        No marks entered for this period.
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