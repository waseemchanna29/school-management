@extends('layouts.app')
@section('title', 'Attendance Report')
@section('page-title', 'Attendance Report')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Monthly Attendance Report</div>
        <div class="page-header-sub">
            {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}
        </div>
    </div>
    <button onclick="window.print()" class="btn-outline-primary btn btn-sm no-print">
        <i class="fas fa-print"></i> Print
    </button>
</div>

<form method="GET" class="no-print" style="margin-bottom:1.4rem;">
    <div class="filter-bar">
        <div>
            <label class="form-label">Month</label>
            <select name="month" class="form-select" onchange="this.form.submit()">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0,0,0,$m,1)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Year</label>
            <select name="year" class="form-select" onchange="this.form.submit()">
                @foreach(range(date('Y')-1, date('Y')+1) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Section</label>
            <select name="section_id" class="form-select">
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" {{ $sectionId == $section->id ? 'selected' : '' }}>
                        {{ $section->schoolClass->name ?? '' }} – {{ $section->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
        </div>
    </div>
</form>

{{-- Daily chart --}}
@if($dailyData->isNotEmpty())
<div class="mb-3 card no-print">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-chart-bar"></i> Daily Attendance
        </div>
    </div>
    <div class="card-body">
        <canvas id="daily-chart" style="max-height:220px;"></canvas>
    </div>
</div>
@endif

{{-- Student summary --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-users"></i> Student Summary
        </div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Leave</th>
                    <th>Working Days</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentSummary as $row)
                @php
                    $pct   = $row['working_days'] > 0
                        ? round(($row['present'] / $row['working_days']) * 100, 1)
                        : 0;
                    $color = $pct >= 75 ? 'var(--success)' : ($pct >= 50 ? 'var(--warning)' : 'var(--danger)');
                @endphp
                <tr>
                    <td>
                        <strong>{{ $row['student']->full_name }}</strong>
                        <div style="font-size:0.77rem; color:var(--text-muted);">
                            {{ $row['student']->roll_number }}
                        </div>
                    </td>
                    <td>
                        {{ $row['student']->schoolClass->name ?? '—' }}
                        — {{ $row['student']->section->name ?? '—' }}
                    </td>
                    <td><span class="att-pill present">{{ $row['present'] }}</span></td>
                    <td><span class="att-pill absent">{{ $row['absent'] }}</span></td>
                    <td><span class="att-pill late">{{ $row['late'] }}</span></td>
                    <td><span class="att-pill leave">{{ $row['leave'] }}</span></td>
                    <td>{{ $row['total'] }}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.6rem;">
                            <div class="att-progress-bar-wrap">
                                <div class="att-progress-bar-fill"
                                     style="width:{{ $pct }}%; background:{{ $color }};"></div>
                            </div>
                            <span style="font-weight:700; font-size:0.88rem; color:{{ $color }};">
                                {{ $pct }}%
                            </span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                        No data for this period.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($dailyData->isNotEmpty())
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const ctx  = document.getElementById('daily-chart').getContext('2d');
const data = @json($dailyData);

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: data.map(d => d.date),
        datasets: [
            {
                label: 'Present',
                data: data.map(d => d.present),
                backgroundColor: 'rgba(25,135,84,0.75)',
                borderRadius: 4,
            },
            {
                label: 'Absent',
                data: data.map(d => d.absent),
                backgroundColor: 'rgba(220,53,69,0.75)',
                borderRadius: 4,
            },
        ],
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
        },
    },
});
</script>
@endif
@endsection