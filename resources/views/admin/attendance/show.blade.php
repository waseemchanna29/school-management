@extends('layouts.app')
@section('title', 'Session Detail')
@section('page-title', 'Session Detail')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">
            {{ $session->date->format('l, d M Y') }}
        </div>
        <div class="page-header-sub">
            {{ $session->schoolClass->name ?? '' }} — Section {{ $session->section->name ?? '' }}
            &bull; Teacher: {{ $session->teacher->full_name ?? '—' }}
        </div>
    </div>
    <div class="d-flex gap-2 no-print">
        <button onclick="window.print()" class="btn-outline-primary btn btn-sm">
            <i class="fas fa-print"></i> Print
        </button>
        @if($session->isLocked())
        <form action="{{ route('admin.attendance.unlock', $session) }}"
              method="POST"
              data-confirm="Unlock this session? Teacher will be able to edit it."
              data-type="warning"
              data-title="Unlock Session">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm">
                <i class="fas fa-unlock"></i> Unlock
            </button>
        </form>
        @endif
        <a href="{{ route('admin.attendance.index') }}"
           class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

{{-- Summary --}}
<div class="mb-3 att-summary-pills">
    <span class="att-pill present"><i class="fas fa-check"></i> {{ $session->present_count }} Present</span>
    <span class="att-pill absent"><i class="fas fa-times"></i> {{ $session->absent_count }} Absent</span>
    <span class="att-pill late"><i class="fas fa-clock"></i> {{ $session->late_count }} Late</span>
    <span class="att-pill leave"><i class="fas fa-door-open"></i> {{ $session->leave_count }} Leave</span>
    <span class="badge {{ $session->status_badge_class }}" style="font-size:0.85rem; padding:0.4rem 0.9rem;">
        {{ ucfirst($session->status) }}
        @if($session->isLocked()) <i class="fas fa-lock"></i> @endif
    </span>
    @if($session->submitted_at)
    <span style="font-size:0.82rem; color:var(--text-muted);">
        Submitted: {{ $session->submitted_at->format('d M Y, h:i A') }}
    </span>
    @endif
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
                @foreach($session->records->sortBy('student.full_name') as $i => $record)
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
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection