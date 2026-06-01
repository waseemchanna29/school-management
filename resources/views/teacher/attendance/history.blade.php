@extends('layouts.teacher')
@section('title', 'Attendance History')
@section('page-title', 'Attendance History')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Attendance History</div>
        <div class="page-header-sub">
            {{ $section?->schoolClass?->name }} — Section {{ $section?->name }}
        </div>
    </div>
    <a href="{{ route('teacher.attendance.take') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> Take Attendance
    </a>
</div>

{{-- Month filter --}}
<form method="GET" style="margin-bottom:1.4rem;">
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

{{-- Chart --}}
@if($chartData->isNotEmpty())
<div class="mb-3 card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-chart-bar"></i>
            Daily Attendance —
            {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}
        </div>
    </div>
    <div class="card-body">
        <canvas id="att-chart" style="max-height:220px;"></canvas>
    </div>
</div>
@endif

{{-- Sessions list --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-list"></i> Sessions</div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Leave</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td>
                        <strong>{{ $session->date->format('d M, Y') }}</strong>
                        <div style="font-size:0.77rem; color:var(--text-muted);">
                            {{ $session->date->format('l') }}
                        </div>
                    </td>
                    <td>{{ $session->records->count() }}</td>
                    <td><span class="att-pill present">{{ $session->present_count }}</span></td>
                    <td><span class="att-pill absent">{{ $session->absent_count }}</span></td>
                    <td><span class="att-pill late">{{ $session->late_count }}</span></td>
                    <td><span class="att-pill leave">{{ $session->leave_count }}</span></td>
                    <td>
                        <span class="badge {{ $session->status_badge_class }}">
                            {{ ucfirst($session->status) }}
                        </span>
                        @if($session->isLocked())
                            <i class="fas fa-lock" style="color:var(--text-muted); font-size:0.8rem; margin-left:3px;"></i>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('teacher.attendance.show', $session) }}"
                               class="btn-outline-primary btn btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(!$session->isSubmitted())
                            <a href="{{ route('teacher.attendance.take', ['date' => $session->date->toDateString()]) }}"
                               class="btn-outline-primary btn btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                        No sessions for this month.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sessions->hasPages())
    <div style="padding:1rem 1.4rem; border-top:1px solid var(--border);">
        {{ $sessions->withQueryString()->links() }}
    </div>
    @endif
</div>

@if($chartData->isNotEmpty())
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('att-chart').getContext('2d');
const data = @json($chartData);

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
            {
                label: 'Late',
                data: data.map(d => d.late),
                backgroundColor: 'rgba(255,193,7,0.75)',
                borderRadius: 4,
            },
        ],
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
        },
        scales: {
            x: { stacked: false },
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
        },
    },
});
</script>
@endif
@endsection