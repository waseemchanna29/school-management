@extends('layouts.teacher')
@section('title', 'Take Attendance')
@section('page-title', 'Take Attendance')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Take Attendance</div>
        <div class="page-header-sub">
            {{ $section->schoolClass->name ?? '' }} — Section {{ $section->name }}
        </div>
    </div>
    {{-- Date picker --}}
    <form method="GET" style="display:flex; align-items:center; gap:0.6rem;">
        <input type="date" name="date" class="form-control"
               value="{{ $date }}" max="{{ today()->toDateString() }}"
               onchange="this.form.submit()"
               style="width:170px;">
    </form>
</div>

@if($session && $session->isLocked())
<div class="alert alert-danger">
    <i class="fas fa-lock"></i>
    This session is <strong>locked</strong>.
    Contact admin to unlock if changes are needed.
</div>
@elseif($session && $session->isSubmitted())
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    This session has been <strong>submitted</strong> and is locked.
</div>
@endif

@if($students->isEmpty())
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    No active students found in this section.
</div>
@else

<form action="{{ route('teacher.attendance.save') }}" method="POST" id="att-form">
@csrf
<input type="hidden" name="date" value="{{ $date }}">

<div class="mb-2 card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-users"></i>
            {{ $students->count() }} Students —
            {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}
        </div>

        {{-- Mark All buttons --}}
        @if(!($session && $session->isSubmitted()))
        <div style="display:flex; gap:0.4rem;">
            <button type="button" class="btn-outline-secondary btn btn-sm"
                    onclick="markAll('present')">
                All Present
            </button>
            <button type="button" class="btn-outline-secondary btn btn-sm"
                    onclick="markAll('absent')">
                All Absent
            </button>
        </div>
        @endif
    </div>

    <div>
        @foreach($students as $student)
        @php
            $existing = $session?->records?->firstWhere('student_id', $student->id);
            $status   = $existing?->status ?? 'present';
        @endphp
        <div class="att-student-row">
            {{-- Avatar --}}
            <div class="sidebar-user-avatar" style="width:36px; height:36px; font-size:0.85rem; flex-shrink:0;">
                {{ strtoupper(substr($student->full_name, 0, 1)) }}
            </div>

            {{-- Name --}}
            <div class="att-student-name">
                {{ $student->full_name }}
                <span class="att-student-roll">{{ $student->roll_number }}</span>
            </div>

            {{-- Status buttons --}}
            @if($session && $session->isSubmitted())
                {{-- View only --}}
                <span class="badge {{ $existing?->status_badge_class ?? 'badge-primary' }}">
                    {{ ucfirst($existing?->status ?? '—') }}
                </span>
            @else
                <input type="hidden" name="attendance[{{ $student->id }}][status]"
                       id="status-{{ $student->id }}" value="{{ $status }}">

                <div class="att-status-group">
                    @foreach(['present','absent','late','leave'] as $s)
                    <button type="button"
                            class="att-status-btn {{ $status === $s ? 'selected-'.$s : '' }}"
                            id="btn-{{ $student->id }}-{{ $s }}"
                            onclick="setStatus({{ $student->id }}, '{{ $s }}')">
                        @if($s === 'present') <i class="fas fa-check"></i> P
                        @elseif($s === 'absent') <i class="fas fa-times"></i> A
                        @elseif($s === 'late') <i class="fas fa-clock"></i> L
                        @else <i class="fas fa-door-open"></i> Lv
                        @endif
                    </button>
                    @endforeach
                </div>

                <input type="text" name="attendance[{{ $student->id }}][remarks]"
                       class="form-control" style="width:130px; font-size:0.8rem;"
                       placeholder="Remarks..."
                       value="{{ $existing?->remarks }}">
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- Live summary bar --}}
@if(!($session && $session->isSubmitted()))
<div style="padding:0.9rem 1.2rem; background:var(--white); border:1px solid var(--border);
            border-radius:var(--radius); margin-bottom:1.2rem; display:flex; align-items:center;
            gap:1rem; flex-wrap:wrap;">
    <strong style="color:var(--primary); font-size:0.88rem;">Summary:</strong>
    <span class="att-pill present" id="count-present">0 Present</span>
    <span class="att-pill absent"  id="count-absent">0 Absent</span>
    <span class="att-pill late"    id="count-late">0 Late</span>
    <span class="att-pill leave"   id="count-leave">0 Leave</span>
</div>

<div style="display:flex; gap:0.8rem; align-items:center; flex-wrap:wrap;">
    <button type="submit" class="btn-outline-primary btn btn-lg">
        <i class="fas fa-save"></i> Save as Draft
    </button>

    @if($session && !$session->isSubmitted())
    <button type="button" class="btn btn-primary btn-lg"
            onclick="smsConfirm(
                'Once submitted, attendance will be locked and cannot be edited.',
                () => submitAttendance(),
                'Submit Attendance',
                'warning'
            )">
        <i class="fas fa-paper-plane"></i> Submit & Lock
    </button>
    @endif
</div>
@endif

</form>

{{-- Submit form --}}
@if($session && !$session->isSubmitted())
<form action="{{ route('teacher.attendance.submit', $session) }}" method="POST"
      id="submit-form" style="display:none;">
    @csrf
</form>
@endif

@endif

<script>
const statuses = {};

// Init statuses from existing data
@foreach($students as $student)
@php $existing = $session?->records?->firstWhere('student_id', $student->id); @endphp
statuses[{{ $student->id }}] = '{{ $existing?->status ?? "present" }}';
@endforeach

function setStatus(studentId, status) {
    // Update hidden input
    document.getElementById('status-' + studentId).value = status;

    // Remove all selected classes from this student's buttons
    ['present','absent','late','leave'].forEach(s => {
        const btn = document.getElementById('btn-' + studentId + '-' + s);
        if (btn) btn.className = 'att-status-btn';
    });

    // Add selected class
    const active = document.getElementById('btn-' + studentId + '-' + status);
    if (active) active.className = 'att-status-btn selected-' + status;

    statuses[studentId] = status;
    updateSummary();
}

function markAll(status) {
    @foreach($students as $student)
    setStatus({{ $student->id }}, status);
    @endforeach
}

function updateSummary() {
    const counts = { present:0, absent:0, late:0, leave:0 };
    Object.values(statuses).forEach(s => { if (counts[s] !== undefined) counts[s]++; });
    document.getElementById('count-present').textContent = counts.present + ' Present';
    document.getElementById('count-absent').textContent  = counts.absent  + ' Absent';
    document.getElementById('count-late').textContent    = counts.late    + ' Late';
    document.getElementById('count-leave').textContent   = counts.leave   + ' Leave';
}

function submitAttendance() {
    // Save first, then submit
    const form = document.getElementById('att-form');
    const submitForm = document.getElementById('submit-form');

    // Change save form action to auto-submit afterwards
    if (submitForm) {
        form.addEventListener('submit', function handler() {
            form.removeEventListener('submit', handler);
        });
        submitForm.submit();
    }
}

// Init summary on load
updateSummary();
</script>
@endsection