@extends('layouts.teacher')
@section('title', 'Session Detail')
@section('page-title', 'Session Detail')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">
            {{ $session->date->format('l, d M Y') }}
        </div>
        <div class="page-header-sub">
            {{ $session->section->schoolClass->name ?? '' }}
            — Section {{ $session->section->name ?? '' }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-outline-primary btn btn-sm no-print">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('teacher.attendance.history') }}"
           class="btn-outline-secondary btn btn-sm no-print">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

{{-- Summary pills --}}
<div class="mb-3 att-summary-pills">
    <span class="att-pill present">
        <i class="fas fa-check"></i> {{ $session->present_count }} Present
    </span>
    <span class="att-pill absent">
        <i class="fas fa-times"></i> {{ $session->absent_count }} Absent
    </span>
    <span class="att-pill late">
        <i class="fas fa-clock"></i> {{ $session->late_count }} Late
    </span>
    <span class="att-pill leave">
        <i class="fas fa-door-open"></i> {{ $session->leave_count }} Leave
    </span>
    <span class="badge {{ $session->status_badge_class }}" style="font-size:0.85rem; padding:0.4rem 0.9rem;">
        {{ ucfirst($session->status) }}
        @if($session->isLocked()) <i class="fas fa-lock"></i> @endif
    </span>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Roll No.</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($session->records->sortBy('student.full_name') as $i => $record)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $record->student->full_name }}</strong></td>
                    <td><code style="font-size:0.82rem;">{{ $record->student->roll_number }}</code></td>
                    <td>
                        <span class="badge {{ $record->status_badge_class }}">
                            {{ ucfirst($record->status) }}
                        </span>
                    </td>
                    <td style="color:var(--text-muted); font-size:0.85rem;">
                        {{ $record->remarks ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--text-muted); padding:2rem;">
                        No records.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Submit button (if draft) --}}
@if(!$session->isSubmitted() && !$session->isLocked())
<div style="margin-top:1.2rem;" class="no-print">
    <form action="{{ route('teacher.attendance.submit', $session) }}" method="POST"
          data-confirm="Once submitted, attendance will be locked permanently."
          data-type="warning"
          data-title="Submit Attendance">
        @csrf
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Submit & Lock
        </button>
    </form>
</div>
@endif
@endsection