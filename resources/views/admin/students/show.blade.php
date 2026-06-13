@extends('layouts.app')
@section('title', 'Student Profile')
@section('page-title', 'Student Profile')

@section('content')

{{-- Print header --}}
<div class="print-only" style="display:none;">
    <div style="text-align:center; margin-bottom:1.5rem;
                padding-bottom:1rem; border-bottom:2px solid #1a3c5e;">
        <h2 style="font-family:serif; color:#1a3c5e; margin:0;">
            {{ $student->full_name }}
        </h2>
        <p style="color:#666; margin:5px 0 0; font-size:0.9rem;">
            {{ $selectedYear?->name ?? '' }}
            @if($enrollment)
                &bull; {{ $enrollment->schoolClass?->name }}
                — Section {{ $enrollment->section?->name }}
                &bull; Roll: {{ $enrollment->roll_number ?? '—' }}
            @endif
            &bull; Printed: {{ now()->format('d M Y') }}
        </p>
    </div>
</div>
<style>@media print { .print-only { display:block !important; } }</style>

{{-- ══════════════════════════════════════════════════════════════
     PROFILE HEADER
══════════════════════════════════════════════════════════════ --}}
<div style="background:linear-gradient(135deg, var(--primary-dark), var(--primary));
            border-radius:var(--radius); padding:1.5rem 1.8rem;
            margin-bottom:0; display:flex; align-items:flex-start;
            justify-content:space-between; flex-wrap:wrap; gap:1rem;
            border-bottom-left-radius:0; border-bottom-right-radius:0;">

    <div style="display:flex; align-items:center; gap:1.2rem;">
        {{-- Avatar --}}
        @if($student->photo)
            <img src="{{ $student->photo_url }}"
                 style="width:72px; height:72px; border-radius:var(--radius);
                        object-fit:cover; border:3px solid rgba(255,255,255,0.3);">
        @else
            <div style="width:72px; height:72px; border-radius:var(--radius);
                        background:rgba(255,255,255,0.15);
                        display:flex; align-items:center; justify-content:center;
                        font-size:1.8rem; font-weight:700; color:var(--white);
                        border:3px solid rgba(255,255,255,0.25);">
                {{ strtoupper(substr($student->full_name, 0, 1)) }}
            </div>
        @endif

        <div>
            <div style="font-family:var(--font-display); font-size:1.4rem;
                        font-weight:700; color:var(--white); line-height:1.2;">
                {{ $student->full_name }}
            </div>
            <div style="color:rgba(255,255,255,0.75); font-size:0.88rem;
                        margin-top:4px;">
                {{ $student->father_name }}
                &bull; {{ ucfirst($student->gender ?? '') }}
                @if($student->date_of_birth)
                    &bull; DOB: {{ $student->date_of_birth->format('d M, Y') }}
                @endif
            </div>

            {{-- Current enrollment badge --}}
            @if($enrollment)
            <div style="display:flex; gap:0.5rem; margin-top:0.6rem;
                        flex-wrap:wrap;">
                <span style="background:rgba(255,255,255,0.15);
                             color:var(--white); padding:0.25rem 0.75rem;
                             border-radius:20px; font-size:0.8rem; font-weight:600;">
                    <i class="fas fa-chalkboard"></i>
                    {{ $enrollment->schoolClass?->name }}
                    — {{ $enrollment->section?->name }}
                </span>
                <span style="background:rgba(255,255,255,0.15);
                             color:var(--white); padding:0.25rem 0.75rem;
                             border-radius:20px; font-size:0.8rem; font-weight:600;">
                    <i class="fas fa-hashtag"></i>
                    Roll: {{ $enrollment->roll_number ?? '—' }}
                </span>
                <span style="background:rgba(25,135,84,0.3);
                             color:#a3e9c3; padding:0.25rem 0.75rem;
                             border-radius:20px; font-size:0.8rem; font-weight:600;
                             border:1px solid rgba(25,135,84,0.4);">
                    {{ $enrollment->status_label }}
                </span>
            </div>
            @else
            <div style="margin-top:0.6rem;">
                <span style="background:rgba(220,53,69,0.3);
                             color:#ffa8b0; padding:0.25rem 0.8rem;
                             border-radius:20px; font-size:0.8rem;
                             border:1px solid rgba(220,53,69,0.4);">
                    <i class="fas fa-exclamation-triangle"></i>
                    Not enrolled in {{ $selectedYear?->name }}
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- Right: Year Switcher + Actions --}}
    <div style="display:flex; flex-direction:column;
                align-items:flex-end; gap:0.6rem;">

        {{-- Year Switcher --}}
        <div style="display:flex; align-items:center; gap:0.5rem;">
            <span style="font-size:0.8rem; color:rgba(255,255,255,0.6);">
                Viewing year:
            </span>
            <form method="GET" style="display:inline;">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <select name="year_id" class="form-select"
                        style="font-size:0.82rem; font-weight:700;
                               background:rgba(255,255,255,0.15);
                               color:var(--white); border:1px solid rgba(255,255,255,0.3);
                               padding:0.3rem 0.7rem; width:auto; cursor:pointer;"
                        onchange="this.form.submit()">
                    @foreach($studentYears as $sy)
                        <option value="{{ $sy->id }}"
                                {{ $yearId == $sy->id ? 'selected' : '' }}
                                style="background:var(--primary-dark); color:white;">
                            {{ $sy->name }}
                            {{ $sy->is_current ? '(Current)' : '' }}
                        </option>
                    @endforeach
                    @if($studentYears->isEmpty())
                        <option value="{{ $yearId }}">
                            {{ $selectedYear?->name ?? 'No years' }}
                        </option>
                    @endif
                </select>
            </form>
        </div>

        {{-- Action buttons --}}
        <div class="d-flex gap-2 no-print">
            <button onclick="window.print()"
                    class="btn btn-sm"
                    style="background:rgba(255,255,255,0.15);
                           color:var(--white);
                           border:1px solid rgba(255,255,255,0.3);">
                <i class="fas fa-print"></i>
            </button>
            <a href="{{ route('admin.students.edit', $student) }}"
               class="btn btn-sm"
               style="background:rgba(255,255,255,0.15);
                      color:var(--white);
                      border:1px solid rgba(255,255,255,0.3);">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.students.index') }}"
               class="btn btn-sm"
               style="background:rgba(255,255,255,0.15);
                      color:var(--white);
                      border:1px solid rgba(255,255,255,0.3);">
                <i class="fa-arrow-left fas"></i> Back
            </a>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     PROFILE TABS
══════════════════════════════════════════════════════════════ --}}
<div class="no-print"
     style="background:var(--white); border-bottom:2px solid var(--border);
            border-left:1px solid var(--border);
            border-right:1px solid var(--border);
            display:flex; overflow-x:auto;">
    @foreach([
        ['overview',    'fa-tachometer-alt', 'Overview'],
        ['enrollment',  'fa-user-graduate',  'Enrollment'],
        ['attendance',  'fa-clipboard-check','Attendance'],
        ['performance', 'fa-chart-line',     'Performance'],
        ['fees',        'fa-receipt',        'Fees'],
    ] as [$tab, $icon, $label])
    <a href="?tab={{ $tab }}&year_id={{ $yearId }}"
       style="padding:0.85rem 1.4rem; font-size:0.88rem; font-weight:600;
              color:{{ $activeTab === $tab ? 'var(--primary)' : 'var(--text-muted)' }};
              border-bottom:3px solid {{ $activeTab === $tab
                  ? 'var(--primary)' : 'transparent' }};
              margin-bottom:-2px; white-space:nowrap; text-decoration:none;
              transition:all var(--transition); display:inline-flex;
              align-items:center; gap:0.5rem;">
        <i class="fas {{ $icon }}" style="font-size:0.82rem;"></i>
        {{ $label }}
    </a>
    @endforeach
</div>

<div style="background:var(--white); border:1px solid var(--border);
            border-top:none; border-radius:0 0 var(--radius) var(--radius);
            padding:1.5rem;">

{{-- ══════════════════════════════════════════════════════════════
     TAB: OVERVIEW
══════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'overview')

<div class="row">
    <div class="col-7">

        {{-- Enrollment summary --}}
        @if($enrollment)
        <div style="background:rgba(37,99,168,0.04);
                    border:1px solid rgba(37,99,168,0.15);
                    border-radius:var(--radius-sm);
                    padding:1rem 1.3rem; margin-bottom:1.2rem;">
            <div style="display:grid; grid-template-columns:repeat(4,1fr);
                        gap:0.8rem;">
                @foreach([
                    ['Class',    $enrollment->schoolClass?->name ?? '—'],
                    ['Section',  $enrollment->section?->name ?? '—'],
                    ['Roll No.', $enrollment->roll_number ?? '—'],
                    ['Status',   $enrollment->status_label],
                ] as [$lbl, $val])
                <div style="text-align:center;">
                    <div style="font-size:0.7rem; text-transform:uppercase;
                                letter-spacing:0.8px; color:var(--text-muted);
                                font-weight:600; margin-bottom:3px;">
                        {{ $lbl }}
                    </div>
                    <div style="font-weight:700; color:var(--primary);
                                font-size:0.92rem;">
                        {{ $val }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Personal Info --}}
        <div style="margin-bottom:1.2rem;">
            <div style="font-weight:700; color:var(--primary);
                        font-size:0.85rem; margin-bottom:0.8rem;
                        text-transform:uppercase; letter-spacing:0.5px;">
                <i class="fas fa-user"></i> Personal Information
            </div>
            <div style="display:grid; grid-template-columns:repeat(2,1fr);
                        gap:0.8rem 2rem;">
                @foreach([
                    ['Father',       $student->father_name],
                    ['Mother',       $student->mother_name ?? '—'],
                    ['CNIC',         $student->cnic ?? '—'],
                    ['Phone',        $student->phone ?? '—'],
                    ['Blood Group',  $student->blood_group ?? '—'],
                    ['Address',      $student->address ?? '—'],
                    ['Admission',    $student->admission_date?->format('d M, Y') ?? '—'],
                    ['Campus',       $student->campus?->name ?? '—'],
                ] as [$lbl, $val])
                <div>
                    <div style="font-size:0.72rem; text-transform:uppercase;
                                letter-spacing:0.7px; color:var(--text-muted);
                                font-weight:600; margin-bottom:2px;">
                        {{ $lbl }}
                    </div>
                    <div style="font-size:0.87rem; color:var(--text-dark);">
                        {{ $val }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Parent Info --}}
        @if($student->parentRecord)
        <div>
            <div style="font-weight:700; color:var(--primary);
                        font-size:0.85rem; margin-bottom:0.8rem;
                        text-transform:uppercase; letter-spacing:0.5px;">
                <i class="fas fa-users"></i> Parent / Guardian
            </div>
            <div style="display:grid; grid-template-columns:repeat(2,1fr);
                        gap:0.6rem 2rem;">
                @php $p = $student->parentRecord; @endphp
                @foreach([
                    ['Father Name',  $p->father_full_name ?? '—'],
                    ['Father Phone', $p->father_phone     ?? '—'],
                    ['Mother Name',  $p->mother_full_name ?? '—'],
                    ['Mother Phone', $p->mother_phone     ?? '—'],
                ] as [$lbl, $val])
                <div>
                    <div style="font-size:0.72rem; text-transform:uppercase;
                                letter-spacing:0.7px; color:var(--text-muted);
                                font-weight:600; margin-bottom:2px;">
                        {{ $lbl }}
                    </div>
                    <div style="font-size:0.87rem; color:var(--text-dark);">
                        {{ $val }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="col-5">

        {{-- Quick Stats --}}
        @if(!empty($attendanceData))
        <div style="background:var(--light-bg); border-radius:var(--radius-sm);
                    padding:1rem 1.2rem; margin-bottom:1rem;">
            <div style="font-weight:700; color:var(--primary);
                        font-size:0.82rem; margin-bottom:0.8rem;
                        text-transform:uppercase; letter-spacing:0.5px;">
                <i class="fas fa-clipboard-check"></i>
                Attendance — {{ $selectedYear?->name }}
            </div>
            @php $ys = $attendanceData['yearSummary']; @endphp
            <div style="display:grid; grid-template-columns:repeat(3,1fr);
                        gap:0.5rem; margin-bottom:0.7rem;">
                @foreach([
                    ['Present', $ys['present'],  'var(--success)'],
                    ['Absent',  $ys['absent'],   'var(--danger)'],
                    ['Total',   $ys['total'],    'var(--primary)'],
                ] as [$lbl, $val, $color])
                <div style="text-align:center; padding:0.5rem;
                            background:var(--white);
                            border-radius:var(--radius-sm);">
                    <div style="font-size:1.1rem; font-weight:700;
                                color:{{ $color }};">
                        {{ $val }}
                    </div>
                    <div style="font-size:0.7rem; color:var(--text-muted);">
                        {{ $lbl }}
                    </div>
                </div>
                @endforeach
            </div>
            <div style="display:flex; align-items:center; gap:0.6rem;">
                <div class="perf-bar-wrap" style="flex:1;">
                    @php $pct = $ys['percentage']; @endphp
                    <div class="perf-bar-fill"
                         style="width:{{ $pct }}%;
                                background:{{ $pct >= 75
                                    ? 'var(--success)'
                                    : ($pct >= 50 ? 'var(--warning)'
                                    : 'var(--danger)') }};"></div>
                </div>
                <strong style="font-size:0.88rem;
                               color:{{ $pct >= 75 ? 'var(--success)'
                                   : ($pct >= 50 ? 'var(--warning)'
                                   : 'var(--danger)') }};">
                    {{ $pct }}%
                </strong>
            </div>
            <a href="?tab=attendance&year_id={{ $yearId }}"
               style="font-size:0.78rem; color:var(--primary);
                      display:block; margin-top:0.5rem;">
                View full attendance →
            </a>
        </div>
        @endif

        {{-- Fee Quick Stats --}}
        @if(!empty($feeData))
        <div style="background:var(--light-bg); border-radius:var(--radius-sm);
                    padding:1rem 1.2rem; margin-bottom:1rem;">
            <div style="font-weight:700; color:var(--primary);
                        font-size:0.82rem; margin-bottom:0.8rem;
                        text-transform:uppercase; letter-spacing:0.5px;">
                <i class="fas fa-receipt"></i>
                Fees — {{ $selectedYear?->name }}
            </div>
            @php $fs = $feeData['feeSummary']; @endphp
            <div style="display:grid; grid-template-columns:repeat(3,1fr);
                        gap:0.5rem; margin-bottom:0.5rem;">
                @foreach([
                    ['Billed',  'PKR '.number_format($fs['total_billed'],0),  'var(--primary)'],
                    ['Paid',    'PKR '.number_format($fs['total_paid'],0),    'var(--success)'],
                    ['Balance', 'PKR '.number_format($fs['total_balance'],0), 'var(--danger)'],
                ] as [$lbl, $val, $color])
                <div style="text-align:center; padding:0.5rem;
                            background:var(--white);
                            border-radius:var(--radius-sm);">
                    <div style="font-size:0.8rem; font-weight:700;
                                color:{{ $color }};">
                        {{ $val }}
                    </div>
                    <div style="font-size:0.68rem; color:var(--text-muted);">
                        {{ $lbl }}
                    </div>
                </div>
                @endforeach
            </div>
            <a href="?tab=fees&year_id={{ $yearId }}"
               style="font-size:0.78rem; color:var(--primary);
                      display:block; margin-top:0.5rem;">
                View fee details →
            </a>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div style="display:flex; flex-direction:column; gap:0.5rem;">
            <a href="{{ route('admin.enrollment.admission') }}"
               class="btn-outline-primary btn btn-sm">
                <i class="fas fa-user-graduate"></i> Manage Enrollment
            </a>
            <a href="{{ route('admin.fee.invoices.create',
                       ['student_id' => $student->id]) }}"
               class="btn-outline-primary btn btn-sm">
                <i class="fas fa-file-invoice"></i> Generate Invoice
            </a>
            <a href="{{ route('admin.students.edit', $student) }}"
               class="btn-outline-secondary btn btn-sm">
                <i class="fas fa-edit"></i> Edit Personal Info
            </a>
            @if($enrollment)
            <a href="{{ route('admin.enrollment.edit', $enrollment) }}"
               class="btn-outline-secondary btn btn-sm">
                <i class="fas fa-edit"></i> Edit Enrollment
            </a>
            @endif
        </div>
    </div>
</div>

@endif

{{-- ══════════════════════════════════════════════════════════════
     TAB: ENROLLMENT HISTORY
══════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'enrollment')

<div class="table-wrapper" style="margin:-1.5rem;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Campus</th>
                <th>Class</th>
                <th>Section</th>
                <th>Roll No.</th>
                <th>Enrolled On</th>
                <th>Status</th>
                <th class="no-print">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($allEnrollments as $e)
            <tr style="{{ $e->academic_year_id == $yearId
                ? 'background:rgba(37,99,168,0.05);' : '' }}">
                <td>
                    <strong>{{ $e->academicYear?->name ?? '—' }}</strong>
                    @if($e->academicYear?->is_current)
                        <span class="badge badge-approved"
                              style="font-size:0.65rem; margin-left:4px;">
                            Current
                        </span>
                    @endif
                    @if($e->academic_year_id == $yearId)
                        <span class="badge badge-info"
                              style="font-size:0.65rem; margin-left:4px;">
                            Viewing
                        </span>
                    @endif
                </td>
                <td style="font-size:0.85rem;">
                    {{ $e->campus?->name ?? '—' }}
                </td>
                <td>
                    <strong>{{ $e->schoolClass?->name ?? '—' }}</strong>
                </td>
                <td>
                    <span class="badge badge-info">
                        {{ $e->section?->name ?? '—' }}
                    </span>
                </td>
                <td>
                    <code style="font-size:0.82rem;">
                        {{ $e->roll_number ?? '—' }}
                    </code>
                </td>
                <td style="font-size:0.82rem; color:var(--text-muted);">
                    {{ $e->enrolled_at?->format('d M, Y') ?? '—' }}
                </td>
                <td>
                    <span class="badge {{ $e->status_badge_class }}">
                        {{ $e->status_label }}
                    </span>
                </td>
                <td class="no-print">
                    <div class="d-flex gap-1">
                        <a href="?tab=overview&year_id={{ $e->academic_year_id }}"
                           class="btn-outline-primary btn btn-sm"
                           title="View this year">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($e->academic_year_id === \App\Helpers\AcademicYearContext::id())
                        <a href="{{ route('admin.enrollment.edit', $e) }}"
                           class="btn-outline-secondary btn btn-sm"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8"
                    style="text-align:center; color:var(--text-muted); padding:3rem;">
                    No enrollment records found.
                    <a href="{{ route('admin.enrollment.create') }}">
                        Enroll this student.
                    </a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endif

{{-- ══════════════════════════════════════════════════════════════
     TAB: ATTENDANCE
══════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'attendance')

@if(!$enrollment)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    Student is not enrolled in <strong>{{ $selectedYear?->name }}</strong>.
    No attendance data available for this year.
</div>
@else
@php
    extract($attendanceData);
    $pctColor = $yearSummary['percentage'] >= 75 ? 'var(--success)'
        : ($yearSummary['percentage'] >= 50 ? 'var(--warning)' : 'var(--danger)');
@endphp

{{-- Year summary bar --}}
<div class="fee-summary-bar" style="margin-bottom:1.4rem;">
    <div class="fee-summary-card" style="border-top-color:var(--primary);">
        <span class="fee-summary-amount">{{ $yearSummary['total'] }}</span>
        <span class="fee-summary-label">Working Days</span>
    </div>
    <div class="fee-summary-card paid">
        <span class="fee-summary-amount"
              style="color:var(--success);">{{ $yearSummary['present'] }}</span>
        <span class="fee-summary-label">Present</span>
    </div>
    <div class="fee-summary-card balance">
        <span class="fee-summary-amount">{{ $yearSummary['absent'] }}</span>
        <span class="fee-summary-label">Absent</span>
    </div>
    <div class="fee-summary-card warn">
        <span class="fee-summary-amount"
              style="color:var(--warning);">{{ $yearSummary['late'] }}</span>
        <span class="fee-summary-label">Late</span>
    </div>
    <div class="fee-summary-card" style="border-top-color:var(--info);">
        <span class="fee-summary-amount"
              style="color:var(--info);">{{ $yearSummary['leave'] }}</span>
        <span class="fee-summary-label">Leave</span>
    </div>
    <div class="fee-summary-card"
         style="border-top-color:{{ $pctColor }};">
        <span class="fee-summary-amount"
              style="color:{{ $pctColor }};">
            {{ $yearSummary['percentage'] }}%
        </span>
        <span class="fee-summary-label">Overall</span>
    </div>
</div>

<div class="row">

    {{-- Calendar --}}
    <div class="col-5">
        <div style="background:var(--light-bg); border-radius:var(--radius);
                    padding:1rem;">
            {{-- Month/Year Nav --}}
            <form method="GET"
                  style="display:flex; gap:0.5rem; margin-bottom:1rem;
                         align-items:center;">
                <input type="hidden" name="tab"     value="attendance">
                <input type="hidden" name="year_id" value="{{ $yearId }}">
                <select name="att_month" class="form-select" style="flex:1;"
                        onchange="this.form.submit()">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}"
                                {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0,0,0,$m,1)) }}
                        </option>
                    @endforeach
                </select>
                <select name="att_year" class="form-select" style="width:90px;"
                        onchange="this.form.submit()">
                    @foreach(range(date('Y')-2, date('Y')) as $y)
                        <option value="{{ $y }}"
                                {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </form>

            {{-- Monthly Summary --}}
            <div style="display:flex; gap:0.5rem; margin-bottom:0.8rem;
                        flex-wrap:wrap;">
                @php $mpct = $attSummary['working_days'] > 0
                    ? round(($attSummary['present']
                        / $attSummary['working_days']) * 100, 1) : 0;
                @endphp
                <span class="att-pill present">
                    {{ $attSummary['present'] }}P
                </span>
                <span class="att-pill absent">
                    {{ $attSummary['absent'] }}A
                </span>
                <span class="att-pill late">
                    {{ $attSummary['late'] }}L
                </span>
                <span class="att-pill leave">
                    {{ $attSummary['leave'] }}Lv
                </span>
                <span style="font-weight:700; font-size:0.82rem;
                             color:{{ $mpct >= 75 ? 'var(--success)'
                                 : ($mpct >= 50 ? 'var(--warning)'
                                 : 'var(--danger)') }};">
                    {{ $mpct }}%
                </span>
            </div>

            {{-- Calendar Grid --}}
            <div style="display:grid; grid-template-columns:repeat(7,1fr);
                        gap:2px; margin-bottom:4px;">
                @foreach(['S','M','T','W','T','F','S'] as $d)
                <div style="text-align:center; font-size:0.65rem;
                            font-weight:700; color:var(--text-muted);
                            padding:0.2rem;">
                    {{ $d }}
                </div>
                @endforeach
            </div>

            <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:2px;">
                @for($i = 0; $i < $firstDay; $i++)
                <div></div>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $ds = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $st = $calendarMap[$ds] ?? null;
                    $isFuture = $ds > today()->toDateString();
                    $bgMap = [
                        'present' => ['rgba(25,135,84,0.2)',  'var(--success)'],
                        'absent'  => ['rgba(220,53,69,0.2)',  'var(--danger)'],
                        'late'    => ['rgba(255,193,7,0.25)', '#7a5800'],
                        'leave'   => ['rgba(13,202,240,0.2)', '#055160'],
                    ];
                    [$bg, $fg] = $bgMap[$st] ?? ['transparent', 'var(--text-muted)'];
                    $isToday = $ds === today()->toDateString();
                @endphp
                <div style="text-align:center; padding:0.25rem 0.1rem;
                            min-height:30px; border-radius:4px;
                            background:{{ $isFuture ? 'transparent' : $bg }};
                            font-size:0.75rem; font-weight:600;
                            color:{{ $isFuture ? 'var(--border)'
                                : ($isToday ? 'var(--primary)' : $fg) }};
                            border:{{ $isToday
                                ? '2px solid var(--primary)'
                                : '1px solid transparent' }};"
                     title="{{ $st ? ucfirst($st) : ($isFuture ? '' : '—') }}">
                    {{ $day }}
                    @if($st && !$isFuture)
                    <div style="font-size:0.55rem; font-weight:800;
                                line-height:1; margin-top:1px;">
                        {{ strtoupper(substr($st,0,1)) }}
                    </div>
                    @endif
                </div>
                @endfor
            </div>

            {{-- Legend --}}
            <div style="display:flex; gap:0.6rem; margin-top:0.8rem;
                        flex-wrap:wrap; font-size:0.7rem;">
                @foreach([
                    ['present','var(--success)','rgba(25,135,84,0.2)','P'],
                    ['absent', 'var(--danger)', 'rgba(220,53,69,0.2)', 'A'],
                    ['late',   '#7a5800',       'rgba(255,193,7,0.25)','L'],
                    ['leave',  '#055160',       'rgba(13,202,240,0.2)','Lv'],
                ] as [$s,$fg,$bg,$lbl])
                <div style="display:flex; align-items:center; gap:3px;">
                    <span style="width:14px; height:14px; background:{{ $bg }};
                                 color:{{ $fg }}; border-radius:3px;
                                 display:inline-flex; align-items:center;
                                 justify-content:center;
                                 font-size:0.58rem; font-weight:700;">
                        {{ $lbl }}
                    </span>
                    {{ ucfirst($s) }}
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Monthly Session List --}}
    <div class="col-7">
        <div class="table-wrapper"
             style="border:1px solid var(--border);
                    border-radius:var(--radius-sm);">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlySessions as $sess)
                    @php
                        $rec = $sess->records->first();
                        $st  = $rec?->status;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $sess->date->format('d M') }}</strong>
                        </td>
                        <td style="font-size:0.8rem; color:var(--text-muted);">
                            {{ $sess->date->format('D') }}
                        </td>
                        <td>
                            @if($st)
                            <span class="badge {{ $rec->status_badge_class }}">
                                {{ ucfirst($st) }}
                            </span>
                            @else
                            <span style="color:var(--border); font-size:0.8rem;">
                                Not recorded
                            </span>
                            @endif
                        </td>
                        <td style="font-size:0.8rem; color:var(--text-muted);">
                            {{ $rec?->remarks ?? '—' }}
                        </td>
                        <td class="no-print">
                            <a href="{{ route('admin.attendance.show', $sess) }}"
                               class="btn-outline-primary btn btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5"
                            style="text-align:center; color:var(--text-muted);
                                   padding:2rem;">
                            No sessions found for
                            {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endif

{{-- ══════════════════════════════════════════════════════════════
     TAB: PERFORMANCE
══════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'performance')

@if(!$enrollment)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    Student is not enrolled in <strong>{{ $selectedYear?->name }}</strong>.
    No performance data available.
</div>
@elseif(empty($performanceData['termReports']))
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    No marks entered yet for <strong>{{ $selectedYear?->name }}</strong>.
    Teachers need to enter marks for this student.
</div>
@else
@php
    $termReports = $performanceData['termReports'];
    $activeTerm  = $performanceData['activeTerm'];
@endphp

{{-- Term Tabs --}}
<div style="display:flex; gap:0; border-bottom:2px solid var(--border);
            margin-bottom:1.2rem; overflow-x:auto;">
    @foreach($termReports as $termNum => $termData)
    <a href="?tab=performance&year_id={{ $yearId }}&term={{ $termNum }}"
       style="padding:0.65rem 1.3rem; font-size:0.88rem; font-weight:600;
              color:{{ $activeTerm == $termNum ? 'var(--primary)' : 'var(--text-muted)' }};
              border-bottom:3px solid {{ $activeTerm == $termNum
                  ? 'var(--primary)' : 'transparent' }};
              margin-bottom:-2px; text-decoration:none;
              transition:all var(--transition); white-space:nowrap;">
        {{ $termData['label'] }}
        @if($termData['report']['overall_grade'])
            <span class="grade-badge"
                  style="{{ $termData['report']['overall_grade']->color_style }};
                          font-size:0.68rem; margin-left:5px;">
                {{ $termData['report']['overall_grade']->grade }}
            </span>
        @endif
    </a>
    @endforeach
</div>

@if(isset($termReports[$activeTerm]))
@php
    $report     = $termReports[$activeTerm]['report'];
    $overallAvg = $report['overall_avg'];
    $oGrade     = $report['overall_grade'];
    $oColor     = $overallAvg >= 75 ? 'var(--success)'
        : ($overallAvg >= 50 ? 'var(--warning)' : 'var(--danger)');
@endphp

{{-- Overall Result Banner --}}
<div style="background:linear-gradient(135deg, var(--primary-dark), var(--primary));
            border-radius:var(--radius); padding:1.2rem 1.5rem;
            display:flex; align-items:center; justify-content:space-between;
            flex-wrap:wrap; gap:1rem; margin-bottom:1.2rem;">
    <div>
        <div style="color:rgba(255,255,255,0.75); font-size:0.8rem;
                    text-transform:uppercase; letter-spacing:0.8px; font-weight:600;">
            {{ $termReports[$activeTerm]['label'] }} — Overall Result
        </div>
        <div style="font-family:var(--font-display); font-size:1.1rem;
                    color:var(--white); margin-top:3px;">
            {{ count($report['subject_results']) }} Subject(s)
        </div>
    </div>
    <div style="text-align:center;">
        <div style="font-size:3rem; font-weight:900; color:var(--accent-light);
                    line-height:1;">
            {{ $oGrade?->grade ?? '—' }}
        </div>
        <div style="font-size:0.82rem; color:rgba(255,255,255,0.7);">
            {{ $overallAvg }}% — {{ $oGrade?->description ?? '' }}
        </div>
        <div style="font-size:0.78rem; color:rgba(255,255,255,0.6);">
            GPA: {{ $oGrade ? number_format($oGrade->gpa, 2) : '—' }}
        </div>
    </div>
</div>

{{-- Subject Results --}}
<div class="table-wrapper"
     style="border:1px solid var(--border); border-radius:var(--radius-sm);">
    <table class="data-table">
        <thead>
            <tr>
                <th>Subject</th>
                @foreach($report['weights'] as $w)
                <th style="text-align:center; font-size:0.72rem; white-space:nowrap;">
                    {{ $w->label }}
                    <span style="opacity:0.65;">({{ $w->weight }}%)</span>
                </th>
                @endforeach
                <th style="text-align:center;">Weighted</th>
                <th style="text-align:center;">Grade</th>
                <th style="text-align:center;">GPA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['subject_results'] as $result)
            @php $avg = $result['weighted_avg']; @endphp
            <tr>
                <td>
                    <strong>{{ $result['subject']?->name ?? '—' }}</strong>
                </td>
                @foreach($result['exam_breakdown'] as $bd)
                <td style="text-align:center;">
                    @if($bd['percentage'] !== null)
                    @php $p = $bd['percentage']; @endphp
                    <span style="font-weight:700; font-size:0.88rem;
                                 color:{{ $p >= 75 ? 'var(--success)'
                                     : ($p >= 50 ? '#7a5800' : 'var(--danger)') }};">
                        {{ $p }}%
                    </span>
                    <div style="font-size:0.7rem; color:var(--text-muted);">
                        {{ $bd['mark']->marks_obtained }}
                        /{{ $bd['mark']->total_marks }}
                    </div>
                    @else
                    <span style="color:var(--border);">—</span>
                    @endif
                </td>
                @endforeach
                <td style="text-align:center;">
                    <div style="display:flex; align-items:center;
                                justify-content:center; gap:0.4rem;">
                        <div class="perf-bar-wrap" style="width:50px;">
                            <div class="perf-bar-fill"
                                 style="width:{{ $avg }}%;
                                        background:{{ $avg >= 75
                                            ? 'var(--success)'
                                            : ($avg >= 50 ? 'var(--warning)'
                                            : 'var(--danger)') }};"></div>
                        </div>
                        <strong style="font-size:0.85rem;
                                       color:{{ $avg >= 75 ? 'var(--success)'
                                           : ($avg >= 50 ? '#7a5800'
                                           : 'var(--danger)') }};">
                            {{ $avg }}%
                        </strong>
                    </div>
                </td>
                <td style="text-align:center;">
                    @if($result['grade'])
                    <span class="grade-badge"
                          style="{{ $result['grade']->color_style }}">
                        {{ $result['grade']->grade }}
                    </span>
                    @else
                    <span style="color:var(--border);">—</span>
                    @endif
                </td>
                <td style="text-align:center; font-weight:700;">
                    {{ $result['grade']
                        ? number_format($result['grade']->gpa, 2)
                        : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:var(--primary); color:var(--white);">
                <td colspan="{{ count($report['weights']) + 1 }}"
                    style="text-align:right; font-weight:700; padding:0.75rem 1rem;">
                    Overall Average
                </td>
                <td style="text-align:center; padding:0.75rem 1rem;">
                    <strong style="color:var(--accent-light); font-size:1rem;">
                        {{ $overallAvg }}%
                    </strong>
                </td>
                <td style="text-align:center; padding:0.75rem 1rem;">
                    @if($oGrade)
                    <span class="grade-badge"
                          style="{{ $oGrade->color_style }}">
                        {{ $oGrade->grade }}
                    </span>
                    @endif
                </td>
                <td style="text-align:center; padding:0.75rem 1rem;
                           font-weight:700; color:var(--accent-light);">
                    {{ $oGrade ? number_format($oGrade->gpa, 2) : '—' }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
@endif
@endif

@endif

{{-- ══════════════════════════════════════════════════════════════
     TAB: FEES
══════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'fees')

@if(!$enrollment)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    Student is not enrolled in <strong>{{ $selectedYear?->name }}</strong>.
    No fee data available.
</div>
@else
@php
    $invoices     = $feeData['invoices'];
    $feeScheduler = $feeData['feeScheduler'];
    $feeSummary   = $feeData['feeSummary'];
@endphp

{{-- Fee Summary --}}
<div class="fee-summary-bar" style="margin-bottom:1.4rem;">
    <div class="fee-summary-card" style="border-top-color:var(--primary);">
        <span class="fee-summary-amount">
            PKR {{ number_format($feeSummary['total_billed'], 0) }}
        </span>
        <span class="fee-summary-label">Total Billed</span>
    </div>
    <div class="fee-summary-card paid">
        <span class="fee-summary-amount"
              style="color:var(--success);">
            PKR {{ number_format($feeSummary['total_paid'], 0) }}
        </span>
        <span class="fee-summary-label">Total Paid</span>
    </div>
    <div class="fee-summary-card balance">
        <span class="fee-summary-amount">
            PKR {{ number_format($feeSummary['total_balance'], 0) }}
        </span>
        <span class="fee-summary-label">Balance Due</span>
    </div>
    <div class="fee-summary-card"
         style="border-top-color:var(--danger);">
        <span class="fee-summary-amount"
              style="color:var(--danger);">
            {{ $feeSummary['unpaid_count'] }}
        </span>
        <span class="fee-summary-label">Unpaid Invoices</span>
    </div>
    <div class="fee-summary-card paid">
        <span class="fee-summary-amount"
              style="color:var(--success);">
            {{ $feeSummary['paid_count'] }}
        </span>
        <span class="fee-summary-label">Paid Invoices</span>
    </div>
</div>

<div class="row">

    {{-- Scheduler Info --}}
    <div class="col-4">
        <div style="background:var(--light-bg); border-radius:var(--radius);
                    padding:1rem 1.2rem; margin-bottom:1rem;">
            <div style="font-weight:700; color:var(--primary); font-size:0.85rem;
                        margin-bottom:0.8rem; text-transform:uppercase;
                        letter-spacing:0.5px;">
                <i class="fas fa-file-invoice-dollar"></i>
                Fee Scheduler
            </div>
            @if($feeScheduler?->feeScheduler)
            <div style="font-weight:700; color:var(--primary); font-size:0.9rem;
                        margin-bottom:0.6rem;">
                {{ $feeScheduler->feeScheduler->name }}
            </div>
            @foreach($feeScheduler->feeScheduler->items as $item)
            <div style="display:flex; justify-content:space-between;
                        font-size:0.82rem; padding:0.25rem 0;
                        border-bottom:1px dotted var(--border);">
                <span style="color:var(--text-muted);">{{ $item->label }}</span>
                <strong>PKR {{ number_format($item->amount, 0) }}</strong>
            </div>
            @endforeach
            <div style="display:flex; justify-content:space-between;
                        font-size:0.88rem; font-weight:700; margin-top:0.5rem;
                        padding-top:0.5rem; border-top:1px solid var(--border);">
                <span>Monthly Total</span>
                <span style="color:var(--primary);">
                    PKR {{ number_format($feeScheduler->feeScheduler->total, 0) }}
                </span>
            </div>
            @else
            <div style="color:var(--text-muted); font-size:0.85rem;">
                No scheduler assigned.
                <a href="{{ route('admin.fee.student.show', $student) }}">
                    Assign one.
                </a>
            </div>
            @endif
        </div>

        <a href="{{ route('admin.fee.student.show', $student) }}"
           class="btn-block btn-outline-primary btn btn-sm">
            <i class="fas fa-cog"></i> Manage Fee Settings
        </a>
        <a href="{{ route('admin.fee.invoices.create',
                   ['student_id' => $student->id]) }}"
           class="btn-block btn btn-primary btn-sm"
           style="margin-top:0.5rem;">
            <i class="fas fa-plus"></i> Generate Invoice
        </a>
    </div>

    {{-- Invoices List --}}
    <div class="col-8">
        <div class="table-wrapper"
             style="border:1px solid var(--border);
                    border-radius:var(--radius-sm);">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Month</th>
                        <th>Net Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Due</th>
                        <th class="no-print"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td>
                            <code style="font-size:0.78rem;">
                                {{ $inv->invoice_number }}
                            </code>
                        </td>
                        <td style="font-size:0.85rem;">
                            {{ date('M', mktime(0,0,0,$inv->billing_month,1)) }}
                        </td>
                        <td>
                            <strong>
                                PKR {{ number_format($inv->net_amount, 0) }}
                            </strong>
                        </td>
                        <td style="color:var(--success);">
                            PKR {{ number_format($inv->paid_amount, 0) }}
                        </td>
                        <td style="font-weight:700;
                                   color:{{ $inv->balance > 0
                                       ? 'var(--danger)' : 'var(--success)' }};">
                            PKR {{ number_format($inv->balance, 0) }}
                        </td>
                        <td>
                            <span class="badge {{ $inv->status_badge_class }}">
                                {{ ucfirst($inv->status) }}
                            </span>
                        </td>
                        <td style="font-size:0.78rem;
                                   color:{{ $inv->due_date->isPast()
                                       && $inv->status === 'unpaid'
                                       ? 'var(--danger)' : 'var(--text-muted)' }};">
                            {{ $inv->due_date->format('d M') }}
                            @if($inv->due_date->isPast()
                                && $inv->status === 'unpaid')
                            <div style="font-size:0.68rem; font-weight:700;">
                                OVERDUE
                            </div>
                            @endif
                        </td>
                        <td class="no-print">
                            <a href="{{ route('admin.fee.invoices.show', $inv) }}"
                               class="btn-outline-primary btn btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8"
                            style="text-align:center;
                                   color:var(--text-muted); padding:2rem;">
                            No invoices for {{ $selectedYear?->name }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($invoices->isNotEmpty())
                <tfoot>
                    <tr style="background:var(--light-bg); font-weight:700;">
                        <td colspan="2"
                            style="padding:0.7rem 1rem; text-align:right;
                                   color:var(--primary);">
                            Year Total
                        </td>
                        <td style="padding:0.7rem 1rem;">
                            PKR {{ number_format($feeSummary['total_billed'], 0) }}
                        </td>
                        <td style="padding:0.7rem 1rem; color:var(--success);">
                            PKR {{ number_format($feeSummary['total_paid'], 0) }}
                        </td>
                        <td style="padding:0.7rem 1rem;
                                   color:{{ $feeSummary['total_balance'] > 0
                                       ? 'var(--danger)' : 'var(--success)' }};">
                            PKR {{ number_format($feeSummary['total_balance'], 0) }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@endif

@endif

</div>{{-- end tab content div --}}
@endsection