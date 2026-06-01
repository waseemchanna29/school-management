@extends('layouts.teacher')
@section('title', 'Student Report')
@section('page-title', 'Student Attendance Report')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Student Attendance Report</div>
        <div class="page-header-sub">
            {{ $section->schoolClass->name ?? '' }} — Section {{ $section->name }}
            &bull; {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}
        </div>
    </div>
    <button onclick="window.print()" class="btn-outline-primary btn btn-sm no-print">
        <i class="fas fa-print"></i> Print
    </button>
</div>

{{-- Month filter --}}
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
    </div>
</form>

<div class="mb-3 card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-calendar-check"></i>
            Working Days: {{ $workingDays }}
        </div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Leave</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary as $i => $row)
                @php
                    $pct   = $row['percentage'];
                    $color = $pct >= 75 ? 'var(--success)' : ($pct >= 50 ? 'var(--warning)' : 'var(--danger)');
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $row['student']->full_name }}</strong>
                        <div style="font-size:0.77rem; color:var(--text-muted);">
                            {{ $row['student']->roll_number }}
                        </div>
                    </td>
                    <td><span class="att-pill present">{{ $row['present'] }}</span></td>
                    <td><span class="att-pill absent">{{ $row['absent'] }}</span></td>
                    <td><span class="att-pill late">{{ $row['late'] }}</span></td>
                    <td><span class="att-pill leave">{{ $row['leave'] }}</span></td>
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
                    <td colspan="7" style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                        No attendance data for this month.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Chart --}}
@if(count($summary) > 0)
<div class="card no-print">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-chart-pie"></i> Attendance Overview</div>
    </div>
    <div class="card-body" style="max-height:300px; display:flex; justify-content:center;">
        <canvas id="summary-chart" style="max-width:400px;"></canvas>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('summary-chart').getContext('2d');
const labels  = @json(collect($summary)->pluck('student')->map(fn($s) => $s->full_name));
const present = @json(collect($summary)->pluck('present'));
const absent  = @json(collect($summary)->pluck('absent'));

new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Present',
                data: present,
                backgroundColor: 'rgba(25,135,84,0.75)',
                borderRadius: 4,
            },
            {
                label: 'Absent',
                data: absent,
                backgroundColor: 'rgba(220,53,69,0.75)',
                borderRadius: 4,
            },
        ],
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            x: { ticks: { font: { size: 10 } } },
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
        },
    },
});
</script>
@endif
@endsection