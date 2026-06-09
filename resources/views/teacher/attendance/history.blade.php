@extends('layouts.teacher')
@section('title', 'Attendance History')
@section('page-title', 'Attendance History')

@section('content')

<div class="page-header">
    <div>
        <div class="page-header-title">Attendance History</div>
        <div class="page-header-sub">
            Day-wise sessions and student-wise breakdown
        </div>
    </div>
    <a href="{{ route('teacher.attendance.take') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus-circle"></i> Take Attendance
    </a>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- VIEW MODE TABS                                              --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="day-tabs" style="margin-bottom:1.4rem;">
    <a href="{{ request()->fullUrlWithQuery(['view' => 'day']) }}"
       class="day-tab {{ $viewMode === 'day' ? 'active' : '' }}">
        <i class="fas fa-calendar-day"></i> Day-wise Sessions
    </a>
    <a href="{{ request()->fullUrlWithQuery(['view' => 'student']) }}"
       class="day-tab {{ $viewMode === 'student' ? 'active' : '' }}">
        <i class="fas fa-users"></i> Student-wise View
    </a>
    <a href="{{ request()->fullUrlWithQuery(['view' => 'calendar']) }}"
       class="day-tab {{ $viewMode === 'calendar' ? 'active' : '' }}">
        <i class="fas fa-calendar-alt"></i> Monthly Calendar
    </a>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- TAB 1: DAY-WISE SESSIONS                                    --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if($viewMode === 'day')

{{-- Filters --}}
<form method="GET">
    <input type="hidden" name="view" value="day">
    <div class="filter-bar" style="margin-bottom:1.2rem;">
        <div>
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control"
                   value="{{ request('date', today()->toDateString()) }}"
                   max="{{ today()->toDateString() }}">
        </div>
        <div>
            <label class="form-label">Month</label>
            <select name="month" class="form-select">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0,0,0,$m,1)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Year</label>
            <select name="year" class="form-select">
                @foreach(range(date('Y')-2, date('Y')) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="{{ route('teacher.attendance.history') }}"
               class="btn-outline-secondary btn">Clear</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
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
                    </td>
                    <td style="color:var(--text-muted);">
                        {{ $session->date->format('l') }}
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
                            <i class="fas fa-lock"
                               style="color:var(--text-muted); font-size:0.75rem;
                                      margin-left:3px;"
                               title="Locked"></i>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('teacher.attendance.show', $session) }}"
                               class="btn-outline-primary btn btn-sm"
                               title="View Detail">
                                <i class="fas fa-eye"></i>
                            </a>

                            {{-- Edit only if not locked/submitted --}}
                            @if(!$session->isSubmitted() && !$session->isLocked())
                            <a href="{{ route('teacher.attendance.take',
                                       ['date' => $session->date->toDateString()]) }}"
                               class="btn-outline-primary btn btn-sm"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @else
                            <span class="btn-outline-secondary btn btn-sm"
                                  style="cursor:not-allowed; opacity:0.45;"
                                  title="Locked — contact admin to unlock">
                                <i class="fas fa-lock"></i>
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9"
                        style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                        <i class="fas fa-calendar-times"
                           style="font-size:2.5rem; display:block;
                                  margin-bottom:0.8rem; color:var(--border);"></i>
                        No sessions found for the selected filters.
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

@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- TAB 2: STUDENT-WISE VIEW                                    --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if($viewMode === 'student')

{{-- Date Range Filter --}}
<form method="GET">
    <input type="hidden" name="view" value="student">
    <div class="filter-bar" style="margin-bottom:1.4rem;">
        <div>
            <label class="form-label">Date From</label>
            <input type="date" name="date_from" class="form-control"
                   value="{{ $dateFrom }}"
                   max="{{ today()->toDateString() }}">
        </div>
        <div>
            <label class="form-label">Date To</label>
            <input type="date" name="date_to" class="form-control"
                   value="{{ $dateTo }}"
                   max="{{ today()->toDateString() }}">
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Apply
            </button>
            <a href="{{ request()->fullUrlWithQuery(['view' => 'student',
                         'date_from' => now()->startOfMonth()->toDateString(),
                         'date_to'   => today()->toDateString()]) }}"
               class="btn-outline-secondary btn">This Month</a>
        </div>
    </div>
</form>

@if($section && $students->isNotEmpty() && $sessionDays->isNotEmpty())

{{-- Section info --}}
<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem; flex-wrap:wrap;">
    <div style="font-weight:700; color:var(--primary); font-size:0.95rem;">
        {{ $section->schoolClass->name ?? '' }} — Section {{ $section->name }}
    </div>
    <span class="badge badge-info">{{ $sessionDays->count() }} submitted sessions</span>
    <span style="font-size:0.82rem; color:var(--text-muted);">
        {{ \Carbon\Carbon::parse($dateFrom)->format('d M') }}
        –
        {{ \Carbon\Carbon::parse($dateTo)->format('d M, Y') }}
    </span>
</div>

{{-- Scrollable grid --}}
<div class="card">
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.82rem; min-width:600px;">
            <thead>
                <tr>
                    {{-- Student column --}}
                    <th style="background:var(--primary); color:var(--white);
                               padding:0.7rem 1rem; text-align:left;
                               position:sticky; left:0; z-index:2;
                               font-size:0.78rem; text-transform:uppercase;
                               min-width:180px; border:1px solid rgba(255,255,255,0.1);">
                        Student
                    </th>

                    {{-- Date columns --}}
                    @foreach($sessionDays as $sess)
                    <th style="background:var(--primary); color:var(--white);
                               padding:0.5rem 0.4rem; text-align:center;
                               font-size:0.75rem; white-space:nowrap;
                               border:1px solid rgba(255,255,255,0.1); min-width:56px;">
                        {{ $sess->date->format('d') }}<br>
                        <span style="font-weight:400; opacity:0.75; font-size:0.68rem;">
                            {{ $sess->date->format('M') }}
                        </span>
                        @if($sess->isLocked())
                        <br><i class="fas fa-lock" style="font-size:0.6rem; opacity:0.6;"></i>
                        @endif
                    </th>
                    @endforeach

                    {{-- Summary columns --}}
                    <th style="background:var(--primary-dark); color:var(--white);
                               padding:0.5rem 0.7rem; text-align:center;
                               font-size:0.72rem; white-space:nowrap; min-width:50px;
                               border:1px solid rgba(255,255,255,0.1);">P</th>
                    <th style="background:var(--primary-dark); color:var(--white);
                               padding:0.5rem 0.7rem; text-align:center;
                               font-size:0.72rem; white-space:nowrap; min-width:50px;
                               border:1px solid rgba(255,255,255,0.1);">A</th>
                    <th style="background:var(--primary-dark); color:var(--white);
                               padding:0.5rem 0.7rem; text-align:center;
                               font-size:0.72rem; white-space:nowrap; min-width:50px;
                               border:1px solid rgba(255,255,255,0.1);">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                @php
                    $grid    = $studentGrid[$student->id] ?? [];
                    $present = collect($grid)->filter(fn($s) => $s === 'present')->count();
                    $absent  = collect($grid)->filter(fn($s) => $s === 'absent')->count();
                    $late    = collect($grid)->filter(fn($s) => $s === 'late')->count();
                    $leave   = collect($grid)->filter(fn($s) => $s === 'leave')->count();
                    $total   = $sessionDays->count();
                    $pct     = $total > 0
                        ? round(($present / $total) * 100, 1)
                        : 0;
                    $pctColor = $pct >= 75 ? 'var(--success)'
                        : ($pct >= 50 ? '#7a5800' : 'var(--danger)');
                @endphp
                <tr style="border-bottom:1px solid var(--border);">

                    {{-- Sticky student name --}}
                    <td style="padding:0.6rem 1rem; position:sticky; left:0;
                               background:var(--white); z-index:1;
                               border-right:2px solid var(--border);">
                        <div style="font-weight:600; color:var(--text-dark); font-size:0.85rem;">
                            {{ $student->full_name }}
                        </div>
                        <div style="font-size:0.72rem; color:var(--text-muted);">
                            {{ $student->roll_number }}
                        </div>
                    </td>

                    {{-- Status per day --}}
                    @foreach($sessionDays as $sess)
                    @php $status = $grid[$sess->date->toDateString()] ?? null; @endphp
                    <td style="text-align:center; padding:0.35rem 0.25rem;
                               border:1px solid var(--border);">
                        @if($status === 'present')
                            <span style="display:inline-block; width:28px; height:28px;
                                         background:rgba(25,135,84,0.15);
                                         color:var(--success); border-radius:50%;
                                         font-size:0.75rem; font-weight:700;
                                         line-height:28px; text-align:center;">
                                P
                            </span>
                        @elseif($status === 'absent')
                            <span style="display:inline-block; width:28px; height:28px;
                                         background:rgba(220,53,69,0.15);
                                         color:var(--danger); border-radius:50%;
                                         font-size:0.75rem; font-weight:700;
                                         line-height:28px; text-align:center;">
                                A
                            </span>
                        @elseif($status === 'late')
                            <span style="display:inline-block; width:28px; height:28px;
                                         background:rgba(255,193,7,0.18);
                                         color:#7a5800; border-radius:50%;
                                         font-size:0.75rem; font-weight:700;
                                         line-height:28px; text-align:center;">
                                L
                            </span>
                        @elseif($status === 'leave')
                            <span style="display:inline-block; width:28px; height:28px;
                                         background:rgba(13,202,240,0.15);
                                         color:#055160; border-radius:50%;
                                         font-size:0.75rem; font-weight:700;
                                         line-height:28px; text-align:center;">
                                Lv
                            </span>
                        @else
                            <span style="color:var(--border); font-size:0.75rem;">—</span>
                        @endif
                    </td>
                    @endforeach

                    {{-- Summary --}}
                    <td style="text-align:center; padding:0.5rem;
                               background:rgba(25,135,84,0.06);
                               font-weight:700; color:var(--success); font-size:0.82rem;">
                        {{ $present }}
                    </td>
                    <td style="text-align:center; padding:0.5rem;
                               background:rgba(220,53,69,0.05);
                               font-weight:700; color:var(--danger); font-size:0.82rem;">
                        {{ $absent }}
                    </td>
                    <td style="text-align:center; padding:0.5rem;
                               font-weight:700; font-size:0.82rem;
                               color:{{ $pctColor }};">
                        {{ $pct }}%
                    </td>
                </tr>
                @endforeach

                {{-- Footer totals --}}
                <tr style="background:var(--light-bg); font-weight:700;">
                    <td style="padding:0.65rem 1rem; position:sticky; left:0;
                               background:var(--light-bg); border-right:2px solid var(--border);
                               font-size:0.82rem; color:var(--primary);">
                        Daily Totals
                    </td>
                    @foreach($sessionDays as $sess)
                    @php
                        $dayPresent = $sess->records->where('status','present')->count();
                        $dayTotal   = $sess->records->count();
                    @endphp
                    <td style="text-align:center; padding:0.4rem 0.25rem;
                               font-size:0.75rem; color:var(--text-muted);
                               border:1px solid var(--border);">
                        <span style="color:var(--success); font-weight:700;">{{ $dayPresent }}</span>
                        <span style="color:var(--border);">/{{ $dayTotal }}</span>
                    </td>
                    @endforeach
                    <td colspan="3" style="padding:0.5rem;"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Legend --}}
<div style="display:flex; gap:1rem; margin-top:0.8rem;
            font-size:0.79rem; flex-wrap:wrap; align-items:center;">
    <strong style="color:var(--text-muted);">Legend:</strong>
    @foreach(['present' => ['P','var(--success)','rgba(25,135,84,0.15)'],
               'absent'  => ['A','var(--danger)', 'rgba(220,53,69,0.15)'],
               'late'    => ['L','#7a5800',       'rgba(255,193,7,0.18)'],
               'leave'   => ['Lv','#055160',      'rgba(13,202,240,0.15)']] as $s => $cfg)
    <div style="display:flex; align-items:center; gap:5px;">
        <span style="width:22px; height:22px; background:{{ $cfg[2] }};
                     color:{{ $cfg[1] }}; border-radius:50%; display:inline-flex;
                     align-items:center; justify-content:center;
                     font-weight:700; font-size:0.7rem;">
            {{ $cfg[0] }}
        </span>
        {{ ucfirst($s) }}
    </div>
    @endforeach
    <div style="display:flex; align-items:center; gap:5px;">
        <span style="color:var(--border); font-size:0.85rem;">—</span>
        Not recorded
    </div>
</div>

@elseif($section && $students->isNotEmpty() && $sessionDays->isEmpty())
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    No <strong>submitted</strong> attendance sessions found for
    {{ \Carbon\Carbon::parse($dateFrom)->format('d M') }}
    –
    {{ \Carbon\Carbon::parse($dateTo)->format('d M, Y') }}.
    Only submitted (locked) sessions appear in the student-wise view.
</div>
@elseif(!$section)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    You are not assigned as class teacher of any section.
</div>
@endif

@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- TAB 3: MONTHLY CALENDAR                                     --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if($viewMode === 'calendar')

{{-- Month / Year selector --}}
<form method="GET" style="margin-bottom:1.4rem;">
    <input type="hidden" name="view" value="calendar">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <select name="month" class="form-select" style="width:140px;"
                onchange="this.form.submit()">
            @foreach(range(1,12) as $m)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                    {{ date('F', mktime(0,0,0,$m,1)) }}
                </option>
            @endforeach
        </select>
        <select name="year" class="form-select" style="width:100px;"
                onchange="this.form.submit()">
            @foreach(range(date('Y')-2, date('Y')) as $y)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <a href="{{ request()->fullUrlWithQuery(['view' => 'calendar', 'month' => date('n'), 'year' => date('Y')]) }}"
           class="btn-outline-secondary btn btn-sm">
            This Month
        </a>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-calendar-alt"></i>
            {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}
        </div>
        <div class="att-summary-pills">
            @php
                $submittedCount = $calendarSessions->where('status','submitted')->count();
                $draftCount     = $calendarSessions->where('status','draft')->count();
            @endphp
            <span class="att-pill present">{{ $submittedCount }} Submitted</span>
            @if($draftCount)
            <span class="att-pill late">{{ $draftCount }} Draft</span>
            @endif
        </div>
    </div>

    <div class="card-body">
        {{-- Day headers --}}
        <div style="display:grid; grid-template-columns:repeat(7,1fr);
                    gap:4px; margin-bottom:4px;">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
            <div style="text-align:center; font-size:0.75rem; font-weight:700;
                        color:var(--primary); padding:0.4rem;
                        text-transform:uppercase; letter-spacing:0.5px;">
                {{ $d }}
            </div>
            @endforeach
        </div>

        {{-- Calendar grid --}}
        <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:4px;">

            {{-- Empty cells before first day --}}
            @for($i = 0; $i < $firstDay; $i++)
            <div style="min-height:64px;"></div>
            @endfor

            {{-- Days --}}
            @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $sess    = $calendarSessions->get($dateStr);
                $isToday = $dateStr === today()->toDateString();
                $isFuture= $dateStr > today()->toDateString();
            @endphp

            <div style="min-height:64px; border-radius:var(--radius-sm);
                        border:1.5px solid {{ $isToday ? 'var(--primary)' : 'var(--border)' }};
                        padding:0.4rem;
                        background:{{ $isFuture ? 'var(--light-bg)' :
                            ($sess
                                ? ($sess->isLocked() ? 'rgba(25,135,84,0.07)' : 'rgba(255,193,7,0.08)')
                                : 'var(--white)') }};
                        position:relative; transition:all var(--transition);">

                {{-- Day number --}}
                <div style="font-size:0.82rem; font-weight:{{ $isToday ? '700' : '400' }};
                             color:{{ $isToday ? 'var(--primary)' :
                                ($isFuture ? 'var(--border)' : 'var(--text-dark)') }};">
                    {{ $day }}
                    @if($isToday)
                    <span style="font-size:0.6rem; color:var(--primary); margin-left:3px;">TODAY</span>
                    @endif
                </div>

                @if($sess && !$isFuture)
                    {{-- Session info --}}
                    <div style="margin-top:4px;">
                        <div class="att-pill {{ $sess->isLocked() ? 'present' : 'late' }}"
                             style="font-size:0.65rem; padding:0.15rem 0.5rem;
                                    display:inline-flex; margin-bottom:3px;">
                            {{ $sess->isLocked() ? '✓ Done' : '✎ Draft' }}
                        </div>
                        <div style="font-size:0.68rem; color:var(--text-muted); line-height:1.4;">
                            <span style="color:var(--success);">{{ $sess->present_count }}P</span>
                            /
                            <span style="color:var(--danger);">{{ $sess->absent_count }}A</span>
                        </div>
                    </div>

                    {{-- Link --}}
                    @if($sess->isLocked() || $sess->isSubmitted())
                    <a href="{{ route('teacher.attendance.show', $sess) }}"
                       style="position:absolute; inset:0; opacity:0;" title="View session">
                    </a>
                    @else
                    <a href="{{ route('teacher.attendance.take', ['date' => $dateStr]) }}"
                       style="position:absolute; inset:0; opacity:0;" title="Continue draft">
                    </a>
                    @endif

                @elseif(!$isFuture)
                    {{-- No session — link to take --}}
                    <div style="margin-top:6px; font-size:0.68rem; color:var(--border);">
                        Not taken
                    </div>
                    <a href="{{ route('teacher.attendance.take', ['date' => $dateStr]) }}"
                       style="position:absolute; inset:0; opacity:0;" title="Take attendance">
                    </a>
                @else
                    <div style="margin-top:6px; font-size:0.68rem; color:var(--border);">
                        —
                    </div>
                @endif

            </div>
            @endfor

        </div>

        {{-- Legend --}}
        <div style="display:flex; gap:1.2rem; margin-top:1.2rem; flex-wrap:wrap;
                    font-size:0.79rem; border-top:1px solid var(--border); padding-top:0.8rem;">
            <div style="display:flex; align-items:center; gap:6px;">
                <div style="width:14px; height:14px; border-radius:3px;
                             background:rgba(25,135,84,0.1);
                             border:1.5px solid rgba(25,135,84,0.3);"></div>
                Submitted (locked)
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                <div style="width:14px; height:14px; border-radius:3px;
                             background:rgba(255,193,7,0.1);
                             border:1.5px solid rgba(255,193,7,0.3);"></div>
                Draft (not submitted)
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                <div style="width:14px; height:14px; border-radius:3px;
                             background:var(--white);
                             border:1.5px solid var(--border);"></div>
                Not taken (click to take)
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                <div style="width:14px; height:14px; border-radius:3px;
                             background:var(--light-bg);
                             border:1.5px solid var(--border);"></div>
                Future date (not allowed)
            </div>
        </div>
    </div>
</div>

@endif

@endsection