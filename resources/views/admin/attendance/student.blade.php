@extends('layouts.app')
@section('title', 'Student Attendance')
@section('page-title', 'Student Attendance')

@section('content')

{{-- Print header --}}
<div style="display:none;" class="print-only">
    <div style="text-align:center; margin-bottom:1.5rem;
                padding-bottom:1rem; border-bottom:2px solid #1a3c5e;">
        <h2 style="font-family:serif; color:#1a3c5e; margin:0;">
            Attendance Record — {{ $student->full_name }}
        </h2>
        <p style="color:#666; margin:5px 0 0; font-size:0.9rem;">
            {{ $student->roll_number }}
            &bull; {{ $student->schoolClass->name ?? '' }}
            — Section {{ $student->section->name ?? '' }}
            &bull; Printed: {{ now()->format('d M Y') }}
        </p>
    </div>
</div>
<style>@media print { .print-only { display:block !important; } }</style>

{{-- Page Header --}}
<div class="page-header no-print">
    <div class="d-flex align-items-center gap-3">
        <div class="profile-avatar-placeholder"
             style="width:52px; height:52px; font-size:1.3rem;">
            {{ strtoupper(substr($student->full_name, 0, 1)) }}
        </div>
        <div>
            <div class="page-header-title">{{ $student->full_name }}</div>
            <div class="page-header-sub">
                <code>{{ $student->roll_number }}</code>
                &bull; {{ $student->schoolClass->name ?? '' }}
                @if($student->section)
                    — Section {{ $student->section->name }}
                @endif
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-outline-primary btn btn-sm">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('admin.students.show', $student) }}"
           class="btn-outline-secondary btn btn-sm">
            <i class="fas fa-user"></i> Profile
        </a>
        <a href="{{ route('admin.attendance.index') }}"
           class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> All Sessions
        </a>
    </div>
</div>

{{-- Month + Year + Date Range Filters --}}
<div class="mb-3 card no-print">
    <div class="card-body">
        <div class="row">
            {{-- Monthly summary filters --}}
            <div class="col-6">
                <div style="font-weight:700; color:var(--primary);
                            font-size:0.85rem; margin-bottom:0.8rem;">
                    <i class="fas fa-calendar-alt"></i> Monthly Summary
                </div>
                <form method="GET" class="d-flex flex-wrap gap-2">
                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                    <select name="month" class="form-select" style="width:140px;"
                            onchange="this.form.submit()">
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}"
                                    {{ $month == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0,0,0,$m,1)) }}
                            </option>
                        @endforeach
                    </select>
                    <select name="year" class="form-select" style="width:100px;"
                            onchange="this.form.submit()">
                        @foreach(range(date('Y')-2, date('Y')) as $y)
                            <option value="{{ $y }}"
                                    {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Date range filters --}}
            <div class="col-6" style="border-left:1px solid var(--border);">
                <div style="font-weight:700; color:var(--primary);
                            font-size:0.85rem; margin-bottom:0.8rem;">
                    <i class="fas fa-filter"></i> Date Range (Attendance Grid)
                </div>
                <form method="GET" class="d-flex flex-wrap align-items-end gap-2">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year"  value="{{ $year }}">
                    <div>
                        <label class="form-label" style="font-size:0.78rem;">From</label>
                        <input type="date" name="date_from" class="form-control"
                               value="{{ $dateFrom }}"
                               max="{{ today()->toDateString() }}"
                               style="width:150px;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:0.78rem;">To</label>
                        <input type="date" name="date_to" class="form-control"
                               value="{{ $dateTo }}"
                               max="{{ today()->toDateString() }}"
                               style="width:150px;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i> Apply
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Monthly Summary Cards --}}
<div class="fee-summary-bar" style="margin-bottom:1.4rem;">
    <div class="fee-summary-card" style="border-top-color:var(--primary);">
        <span class="fee-summary-amount">{{ $summary['working_days'] }}</span>
        <span class="fee-summary-label">Working Days</span>
    </div>
    <div class="fee-summary-card paid">
        <span class="fee-summary-amount" style="color:var(--success);">
            {{ $summary['present'] }}
        </span>
        <span class="fee-summary-label">Present</span>
    </div>
    <div class="fee-summary-card balance">
        <span class="fee-summary-amount">{{ $summary['absent'] }}</span>
        <span class="fee-summary-label">Absent</span>
    </div>
    <div class="fee-summary-card warn">
        <span class="fee-summary-amount" style="color:var(--warning);">
            {{ $summary['late'] }}
        </span>
        <span class="fee-summary-label">Late</span>
    </div>
    <div class="fee-summary-card" style="border-top-color:var(--info);">
        <span class="fee-summary-amount" style="color:var(--info);">
            {{ $summary['leave'] }}
        </span>
        <span class="fee-summary-label">Leave</span>
    </div>
    <div class="fee-summary-card"
         style="border-top-color:{{ $summary['percentage'] >= 75 ? 'var(--success)' : ($summary['percentage'] >= 50 ? 'var(--warning)' : 'var(--danger)') }};">
        <span class="fee-summary-amount"
              style="color:{{ $summary['percentage'] >= 75 ? 'var(--success)' : ($summary['percentage'] >= 50 ? 'var(--warning)' : 'var(--danger)') }};">
            {{ $summary['percentage'] }}%
        </span>
        <span class="fee-summary-label">Attendance %</span>
    </div>
</div>

<div class="row">

    {{-- Monthly Calendar --}}
    <div class="col-5">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-calendar-alt"></i>
                    {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}
                </div>
            </div>
            <div class="card-body" style="padding:0.8rem;">

                {{-- Day labels --}}
                <div style="display:grid; grid-template-columns:repeat(7,1fr);
                            gap:2px; margin-bottom:4px;">
                    @foreach(['S','M','T','W','T','F','S'] as $d)
                    <div style="text-align:center; font-size:0.68rem; font-weight:700;
                                color:var(--text-muted); padding:0.25rem;">
                        {{ $d }}
                    </div>
                    @endforeach
                </div>

                {{-- Calendar grid --}}
                <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:2px;">
                    {{-- Empty cells --}}
                    @for($i = 0; $i < $firstDay; $i++)
                    <div></div>
                    @endfor

                    {{-- Days --}}
                    @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $ds  = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $rec = $calendarData[$ds] ?? null;
                        $st  = $rec['status'] ?? null;
                        $isFuture = $ds > today()->toDateString();
                        $bgMap = [
                            'present' => ['rgba(25,135,84,0.15)', 'var(--success)'],
                            'absent'  => ['rgba(220,53,69,0.15)', 'var(--danger)'],
                            'late'    => ['rgba(255,193,7,0.18)', '#7a5800'],
                            'leave'   => ['rgba(13,202,240,0.15)', '#055160'],
                        ];
                        [$bg, $fg] = $bgMap[$st] ?? ['var(--light-bg)', 'var(--border)'];
                    @endphp
                    <div style="text-align:center; padding:0.3rem 0.1rem;
                                border-radius:var(--radius-sm);
                                background:{{ $isFuture ? 'transparent' : $bg }};
                                font-size:0.77rem; font-weight:600;
                                color:{{ $isFuture ? 'var(--border)' : $fg }};
                                min-height:28px; line-height:1.8;"
                         title="{{ $st ? ucfirst($st) : ($isFuture ? '' : 'Not recorded') }}">
                        {{ $day }}
                        @if($st && !$isFuture)
                        <div style="font-size:0.6rem; font-weight:700; line-height:1;">
                            {{ strtoupper(substr($st,0,1)) }}
                        </div>
                        @endif
                    </div>
                    @endfor
                </div>

                {{-- Legend --}}
                <div style="display:flex; gap:0.7rem; margin-top:0.9rem;
                            flex-wrap:wrap; font-size:0.72rem;">
                    @foreach([
                        ['present','var(--success)','rgba(25,135,84,0.15)','P'],
                        ['absent', 'var(--danger)', 'rgba(220,53,69,0.15)', 'A'],
                        ['late',   '#7a5800',       'rgba(255,193,7,0.18)', 'L'],
                        ['leave',  '#055160',       'rgba(13,202,240,0.15)','Lv'],
                    ] as [$s,$fg,$bg,$lbl])
                    <div style="display:flex; align-items:center; gap:3px;">
                        <span style="width:16px; height:16px; background:{{ $bg }};
                                     color:{{ $fg }}; border-radius:3px;
                                     display:inline-flex; align-items:center;
                                     justify-content:center;
                                     font-size:0.6rem; font-weight:700;">
                            {{ $lbl }}
                        </span>
                        {{ ucfirst($s) }}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Date Range Attendance Grid --}}
    <div class="col-7">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-list"></i> Attendance Records
                </div>
                <span style="font-size:0.82rem; color:var(--text-muted);">
                    {{ \Carbon\Carbon::parse($dateFrom)->format('d M') }}
                    –
                    {{ \Carbon\Carbon::parse($dateTo)->format('d M, Y') }}
                </span>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th class="no-print">Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rangeSessions as $sess)
                        @php
                            $rec = $sess->records->first();
                            $st  = $rec?->status ?? null;
                        @endphp
                        <tr id="row-{{ $sess->id }}">
                            <td>
                                <strong>{{ $sess->date->format('d M, Y') }}</strong>
                            </td>
                            <td style="color:var(--text-muted); font-size:0.83rem;">
                                {{ $sess->date->format('l') }}
                            </td>
                            <td>
                                @if($st)
                                <span class="badge {{ $rec->status_badge_class }}"
                                      id="badge-{{ $sess->id }}">
                                    {{ ucfirst($st) }}
                                </span>
                                @else
                                <span style="color:var(--border); font-size:0.82rem;">
                                    Not recorded
                                </span>
                                @endif
                            </td>
                            <td style="color:var(--text-muted); font-size:0.83rem;"
                                id="remarks-{{ $sess->id }}">
                                {{ $rec?->remarks ?? '—' }}
                            </td>

                            {{-- Inline Quick Edit (no-print) --}}
                            <td class="no-print">
                                <button class="btn-outline-primary btn btn-sm"
                                        onclick="openInlineEdit(
                                            {{ $sess->id }},
                                            '{{ $st ?? 'present' }}',
                                            '{{ addslashes($rec?->remarks ?? '') }}'
                                        )">
                                    <i class="fas fa-edit"></i>
                                </button>

                                {{-- Inline edit row (hidden) --}}
                                <div id="inline-edit-{{ $sess->id }}"
                                     style="display:none; margin-top:6px;">
                                    <form action="{{ route('admin.attendance.update-record',
                                                   [$sess, $student]) }}"
                                          method="POST">
                                        @csrf
                                        <div style="display:flex; gap:0.4rem;
                                                    align-items:center; flex-wrap:wrap;">
                                            <select name="status" class="form-select"
                                                    id="inline-status-{{ $sess->id }}"
                                                    style="font-size:0.8rem; width:120px;">
                                                @foreach(['present','absent','late','leave'] as $s)
                                                <option value="{{ $s }}"
                                                        {{ $st === $s ? 'selected' : '' }}>
                                                    {{ ucfirst($s) }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="remarks"
                                                   id="inline-remarks-{{ $sess->id }}"
                                                   class="form-control"
                                                   style="font-size:0.8rem; width:130px;"
                                                   value="{{ $rec?->remarks }}"
                                                   placeholder="Remarks">
                                            <button type="submit"
                                                    class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn-outline-secondary btn btn-sm"
                                                    onclick="closeInlineEdit({{ $sess->id }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5"
                                style="text-align:center;
                                       color:var(--text-muted); padding:2.5rem;">
                                No submitted attendance sessions found in this date range.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
function openInlineEdit(sessionId, currentStatus, currentRemarks) {
    // Close any open edit rows
    document.querySelectorAll('[id^="inline-edit-"]').forEach(el => {
        el.style.display = 'none';
    });

    const editRow = document.getElementById('inline-edit-' + sessionId);
    if (editRow) {
        editRow.style.display = 'block';
        const sel = document.getElementById('inline-status-' + sessionId);
        if (sel) sel.value = currentStatus;
    }
}

function closeInlineEdit(sessionId) {
    const editRow = document.getElementById('inline-edit-' + sessionId);
    if (editRow) editRow.style.display = 'none';
}
</script>
@endsection