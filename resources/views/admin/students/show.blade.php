@extends('layouts.app')
@section('title', 'Student Profile')
@section('page-title', 'Student Profile')

@section('content')

@php
    $activeYear = \App\Helpers\AcademicYearContext::current();
    $activeTab  = request('tab', 'profile');
@endphp

{{-- Print header --}}
<div style="display:none;" class="print-only">
    <div style="text-align:center; margin-bottom:1.5rem;
                padding-bottom:1rem; border-bottom:2px solid #1a3c5e;">
        <h2 style="font-family:serif; color:#1a3c5e; margin:0;">
            Student Profile — {{ $student->full_name }}
        </h2>
        <p style="color:#666; margin:5px 0 0; font-size:0.9rem;">
            Printed: {{ now()->format('d M Y') }}
        </p>
    </div>
</div>
<style>@media print { .print-only { display:block !important; } }</style>

{{-- Page Header --}}
<div class="page-header no-print">
    <div class="d-flex align-items-center gap-3">
        @if($student->photo)
            <img src="{{ $student->photo_url }}"
                 style="width:64px; height:64px; border-radius:var(--radius);
                        object-fit:cover; border:2px solid var(--border);">
        @else
            <div class="profile-avatar-placeholder"
                 style="width:64px; height:64px; font-size:1.6rem;">
                {{ strtoupper(substr($student->full_name, 0, 1)) }}
            </div>
        @endif
        <div>
            <div class="page-header-title">{{ $student->full_name }}</div>
            <div class="page-header-sub">
                @if($enrollment)
                    <code>Roll: {{ $enrollment->roll_number ?? '—' }}</code>
                    &bull; {{ $enrollment->schoolClass->name ?? '' }}
                    @if($enrollment->section)
                        — Section {{ $enrollment->section->name }}
                    @endif
                    &bull;
                    <span class="badge {{ $enrollment->status_badge_class }}"
                          style="font-size:0.72rem;">
                        {{ $enrollment->status_label }}
                    </span>
                @else
                    <span style="color:var(--danger); font-size:0.85rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Not enrolled in {{ $activeYear?->name }}
                    </span>
                @endif
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-outline-secondary btn btn-sm">
            <i class="fas fa-print"></i>
        </button>
        <a href="{{ route('admin.students.edit', $student) }}"
           class="btn-outline-primary btn btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('admin.enrollment.index') }}"
           class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Students
        </a>
    </div>
</div>

{{-- Profile Tabs --}}
<div class="day-tabs no-print" style="margin-bottom:1.4rem;">
    <a href="?tab=profile"
       class="day-tab {{ $activeTab === 'profile' ? 'active' : '' }}">
        <i class="fas fa-user"></i> Profile
    </a>
    <a href="?tab=enrollment"
       class="day-tab {{ $activeTab === 'enrollment' ? 'active' : '' }}">
        <i class="fas fa-user-graduate"></i> Enrollment History
    </a>
    <a href="{{ route('admin.attendance.student', $student) }}"
       class="day-tab {{ $activeTab === 'attendance' ? 'active' : '' }}">
        <i class="fas fa-clipboard-check"></i> Attendance
    </a>
    <a href="{{ route('admin.performance.student-report', $student) }}"
       class="day-tab {{ $activeTab === 'performance' ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> Performance
    </a>
    <a href="{{ route('admin.fee.student.show', $student) }}"
       class="day-tab {{ $activeTab === 'fees' ? 'active' : '' }}">
        <i class="fas fa-receipt"></i> Fees
    </a>
</div>

{{-- ════════════════════════ TAB: PROFILE ════════════════════════ --}}
@if($activeTab === 'profile')

<div class="row">
    <div class="col-8">

        {{-- Current Enrollment Card --}}
        @if($enrollment)
        <div class="mb-2 card"
             style="border-left:4px solid var(--primary);">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-calendar-alt"
                       style="color:var(--accent);"></i>
                    {{ $activeYear?->name }} Enrollment
                </div>
                <a href="{{ route('admin.enrollment.edit', $enrollment) }}"
                   class="btn-outline-primary btn btn-sm">
                    <i class="fas fa-edit"></i> Edit Enrollment
                </a>
            </div>
            <div class="card-body">
                <div style="display:grid;
                            grid-template-columns:repeat(4,1fr);
                            gap:1rem;">
                    <div>
                        <div style="font-size:0.75rem; text-transform:uppercase;
                                    letter-spacing:0.8px; color:var(--text-muted);
                                    font-weight:600; margin-bottom:3px;">
                            Class
                        </div>
                        <div style="font-weight:700; color:var(--primary);">
                            {{ $enrollment->schoolClass->name ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem; text-transform:uppercase;
                                    letter-spacing:0.8px; color:var(--text-muted);
                                    font-weight:600; margin-bottom:3px;">
                            Section
                        </div>
                        <div style="font-weight:700; color:var(--primary);">
                            {{ $enrollment->section->name ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem; text-transform:uppercase;
                                    letter-spacing:0.8px; color:var(--text-muted);
                                    font-weight:600; margin-bottom:3px;">
                            Roll No.
                        </div>
                        <div style="font-weight:700; color:var(--primary);">
                            {{ $enrollment->roll_number ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem; text-transform:uppercase;
                                    letter-spacing:0.8px; color:var(--text-muted);
                                    font-weight:600; margin-bottom:3px;">
                            Status
                        </div>
                        <span class="badge {{ $enrollment->status_badge_class }}">
                            {{ $enrollment->status_label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="mb-2 alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Student is not enrolled in <strong>{{ $activeYear?->name }}</strong>.
            <a href="{{ route('admin.enrollment.create') }}"
               style="font-weight:700;">Enroll now.</a>
        </div>
        @endif

        {{-- Personal Information --}}
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-user"></i> Personal Information
                </div>
            </div>
            <div class="card-body">
                <div style="display:grid;
                            grid-template-columns:repeat(2,1fr);
                            gap:1rem 2rem;">
                    @foreach([
                        ['Full Name',        $student->full_name],
                        ['Father Name',      $student->father_name],
                        ['Mother Name',      $student->mother_name ?? '—'],
                        ['Gender',           ucfirst($student->gender ?? '—')],
                        ['Date of Birth',    $student->date_of_birth?->format('d M, Y') ?? '—'],
                        ['Blood Group',      $student->blood_group ?? '—'],
                        ['CNIC / B-Form',    $student->cnic ?? '—'],
                        ['Phone',            $student->phone ?? '—'],
                        ['Address',          $student->address ?? '—'],
                        ['Admission Date',   $student->admission_date?->format('d M, Y') ?? '—'],
                        ['Previous School',  $student->previous_school ?? '—'],
                        ['Campus',           $student->campus->name ?? '—'],
                    ] as [$label, $value])
                    <div>
                        <div style="font-size:0.75rem; text-transform:uppercase;
                                    letter-spacing:0.8px; color:var(--text-muted);
                                    font-weight:600; margin-bottom:2px;">
                            {{ $label }}
                        </div>
                        <div style="font-size:0.9rem; color:var(--text-dark);">
                            {{ $value }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Parent Record --}}
        @if($student->parentRecord)
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-users"></i> Parent / Guardian
                </div>
            </div>
            <div class="card-body">
                <div style="display:grid;
                            grid-template-columns:repeat(2,1fr);
                            gap:1rem 2rem;">
                    @php $p = $student->parentRecord; @endphp
                    @foreach([
                        ['Father Full Name',  $p->father_full_name ?? '—'],
                        ['Father Phone',      $p->father_phone ?? '—'],
                        ['Father CNIC',       $p->father_cnic ?? '—'],
                        ['Father Occupation', $p->father_occupation ?? '—'],
                        ['Mother Full Name',  $p->mother_full_name ?? '—'],
                        ['Mother Phone',      $p->mother_phone ?? '—'],
                    ] as [$label, $value])
                    <div>
                        <div style="font-size:0.75rem; text-transform:uppercase;
                                    letter-spacing:0.8px; color:var(--text-muted);
                                    font-weight:600; margin-bottom:2px;">
                            {{ $label }}
                        </div>
                        <div style="font-size:0.9rem; color:var(--text-dark);">
                            {{ $value }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- Right Column: Quick Actions --}}
    <div class="col-4">
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
            </div>
            <div class="card-body"
                 style="display:flex; flex-direction:column; gap:0.6rem;">
                <a href="{{ route('admin.attendance.student', $student) }}"
                   class="btn-block btn-outline-primary btn">
                    <i class="fas fa-clipboard-check"></i> View Attendance
                </a>
                <a href="{{ route('admin.performance.student-report', $student) }}"
                   class="btn-block btn-outline-primary btn">
                    <i class="fas fa-chart-line"></i> Performance Report
                </a>
                <a href="{{ route('admin.fee.student.show', $student) }}"
                   class="btn-block btn-outline-primary btn">
                    <i class="fas fa-receipt"></i> Fee Profile
                </a>
                @if($enrollment)
                <a href="{{ route('admin.fee.invoices.create',
                           ['student_id' => $student->id]) }}"
                   class="btn-block btn-outline-primary btn">
                    <i class="fas fa-file-invoice"></i> Generate Invoice
                </a>
                <a href="{{ route('admin.enrollment.edit', $enrollment) }}"
                   class="btn-block btn-outline-secondary btn">
                    <i class="fas fa-user-graduate"></i> Edit Enrollment
                </a>
                @endif
                <a href="{{ route('admin.students.edit', $student) }}"
                   class="btn-block btn-outline-secondary btn">
                    <i class="fas fa-edit"></i> Edit Personal Info
                </a>
            </div>
        </div>

        {{-- Master Status --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-info-circle"></i> Student Status
                </div>
            </div>
            <div class="card-body">
                <div style="text-align:center; padding:0.5rem 0;">
                    <span class="badge {{ $student->status === 'active'
                        ? 'badge-approved' : 'badge-rejected' }}"
                          style="font-size:0.95rem; padding:0.5rem 1.5rem;">
                        {{ ucfirst($student->status) }}
                    </span>
                    <div style="font-size:0.78rem; color:var(--text-muted);
                                margin-top:0.6rem;">
                        Master record status
                    </div>
                </div>
                <hr style="border-color:var(--border);">
                <div style="font-size:0.82rem; color:var(--text-muted);
                            text-align:center;">
                    Enrolled in
                    <strong>{{ $allEnrollments->count() }}</strong>
                    academic year(s)
                </div>
            </div>
        </div>
    </div>
</div>

@endif

{{-- ════════════════════════ TAB: ENROLLMENT HISTORY ════════════════════════ --}}
@if($activeTab === 'enrollment')

<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-history"></i> Enrollment History
        </div>
        <span style="font-size:0.83rem; color:var(--text-muted);">
            All academic years
        </span>
    </div>
    <div class="table-wrapper">
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
                <tr style="{{ $e->academic_year_id === \App\Helpers\AcademicYearContext::id()
                    ? 'background:rgba(37,99,168,0.04);' : '' }}">
                    <td>
                        <strong>{{ $e->academicYear->name ?? '—' }}</strong>
                        @if($e->academic_year_id === \App\Helpers\AcademicYearContext::id())
                            <span class="badge badge-approved"
                                  style="font-size:0.65rem; margin-left:5px;">
                                Current
                            </span>
                        @endif
                    </td>
                    <td>{{ $e->campus->name ?? '—' }}</td>
                    <td>
                        <strong>{{ $e->schoolClass->name ?? '—' }}</strong>
                    </td>
                    <td>
                        <span class="badge badge-info">
                            {{ $e->section->name ?? '—' }}
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
                        @if($e->academic_year_id === \App\Helpers\AcademicYearContext::id())
                        <a href="{{ route('admin.enrollment.edit', $e) }}"
                           class="btn-outline-primary btn btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8"
                        style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                        No enrollment records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif

@endsection