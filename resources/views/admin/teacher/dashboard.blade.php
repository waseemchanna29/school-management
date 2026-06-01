@extends('layouts.teacher')
@section('title', 'Teacher Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Welcome, {{ $teacher->full_name }}</div>
        <div class="page-header-sub">
            {{ $teacher->specialization ?? $teacher->qualification }}
            &bull; {{ $teacher->campus->name ?? '' }}
        </div>
    </div>
    @if($section)
    <a href="{{ route('teacher.attendance.take') }}" class="btn btn-primary">
        <i class="fas fa-clipboard-check"></i> Take Today's Attendance
    </a>
    @endif
</div>

{{-- Section Info --}}
@if($section)
<div class="mb-3 card"
     style="border-left:4px solid var(--primary); background:linear-gradient(135deg,rgba(37,99,168,0.04),rgba(37,99,168,0.01));">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between" style="flex-wrap:wrap; gap:1rem;">
            <div>
                <div style="font-size:0.78rem; color:var(--text-muted); text-transform:uppercase;
                            letter-spacing:1px; font-weight:600; margin-bottom:4px;">
                    Your Assigned Section
                </div>
                <div style="font-size:1.4rem; font-weight:700; color:var(--primary);">
                    {{ $section->schoolClass->name ?? '' }} — Section {{ $section->name }}
                </div>
                <div style="color:var(--text-muted); font-size:0.88rem; margin-top:3px;">
                    {{ $section->students->count() }} active students
                </div>
            </div>

            {{-- Today's attendance status --}}
            @if($todaySession)
                <div style="text-align:center;">
                    <span class="badge {{ $todaySession->status_badge_class }}"
                          style="font-size:0.88rem; padding:0.5rem 1.2rem;">
                        Today: {{ ucfirst($todaySession->status) }}
                    </span>
                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:4px;">
                        {{ $todaySession->present_count }} present /
                        {{ $todaySession->absent_count }} absent
                    </div>
                </div>
            @else
                <div style="text-align:center;">
                    <span class="badge badge-rejected" style="font-size:0.88rem; padding:0.5rem 1.2rem;">
                        Today: Not Taken
                    </span>
                    <div style="margin-top:8px;">
                        <a href="{{ route('teacher.attendance.take') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-clipboard-check"></i> Take Now
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@else
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    You are not assigned as class teacher of any section yet.
    Please contact the admin to assign you a section.
</div>
@endif

{{-- Month Stats --}}
<div class="stat-cards-grid" style="grid-template-columns:repeat(2,1fr); max-width:500px; margin-bottom:1.8rem;">
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="fas fa-calendar-check"></i></div>
        <div>
            <span class="stat-card-value">{{ $monthStats['sessions'] }}</span>
            <span class="stat-card-label">Sessions This Month</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="fas fa-check-double"></i></div>
        <div>
            <span class="stat-card-value">{{ $monthStats['submitted'] }}</span>
            <span class="stat-card-label">Submitted</span>
        </div>
    </div>
</div>

{{-- Recent Sessions --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-history"></i> Recent Sessions</div>
        <a href="{{ route('teacher.attendance.history') }}" class="btn-outline-primary btn btn-sm">
            View All
        </a>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Date</th><th>Section</th><th>Present</th><th>Absent</th><th>Late</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($recentSessions as $session)
                <tr>
                    <td><strong>{{ $session->date->format('d M, Y') }}</strong></td>
                    <td>{{ $session->schoolClass->name ?? '' }} – {{ $session->section->name ?? '' }}</td>
                    <td><span class="att-pill present">{{ $session->present_count }}</span></td>
                    <td><span class="att-pill absent">{{ $session->absent_count }}</span></td>
                    <td><span class="att-pill late">{{ $session->late_count }}</span></td>
                    <td>
                        <span class="badge {{ $session->status_badge_class }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('teacher.attendance.show', $session) }}"
                           class="btn-outline-primary btn btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:var(--text-muted); padding:2rem;">
                        No sessions yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection