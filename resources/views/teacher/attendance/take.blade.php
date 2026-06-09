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

    {{-- Date Picker --}}
    <form method="GET" style="display:flex; align-items:center; gap:0.6rem;">
        <input type="date"
               name="date"
               class="form-control"
               value="{{ $date }}"
               max="{{ today()->toDateString() }}"
               onchange="this.form.submit()"
               style="width:180px;">
        <span style="font-size:0.8rem; color:var(--text-muted);">
            (Today or earlier only)
        </span>
    </form>
</div>

{{-- ── Future date warning ── --}}
@if(request('date') > today()->toDateString())
<div class="alert alert-danger" style="margin-bottom:1.2rem;">
    <i class="fas fa-exclamation-circle"></i>
    <strong>Future date selected.</strong>
    Attendance can only be taken for today or past dates.
    Redirecting to today...
    <script>
        setTimeout(() => {
            window.location.href = '{{ route("teacher.attendance.take") }}';
        }, 1500);
    </script>
</div>
@endif

{{-- ── Locked banner ── --}}
@if($session && $session->isLocked())
<div style="background:rgba(220,53,69,0.08); border:1.5px solid rgba(220,53,69,0.3);
            border-radius:var(--radius); padding:1rem 1.4rem;
            display:flex; align-items:center; gap:1rem; margin-bottom:1.4rem;">
    <i class="fas fa-lock" style="font-size:1.5rem; color:var(--danger);"></i>
    <div>
        <div style="font-weight:700; color:var(--danger); font-size:0.95rem;">
            Attendance Locked
        </div>
        <div style="color:var(--text-muted); font-size:0.85rem; margin-top:2px;">
            This session was submitted on
            {{ $session->submitted_at?->format('d M, Y \a\t h:i A') ?? 'an earlier date' }}.
            Contact admin to unlock if changes are needed.
        </div>
    </div>
    <div style="margin-left:auto; flex-shrink:0;">
        <a href="{{ route('teacher.attendance.show', $session) }}"
           class="btn-outline-secondary btn btn-sm">
            <i class="fas fa-eye"></i> View
        </a>
    </div>
</div>
@elseif($session && $session->isSubmitted())
<div style="background:rgba(255,193,7,0.09); border:1.5px solid rgba(255,193,7,0.4);
            border-radius:var(--radius); padding:1rem 1.4rem;
            display:flex; align-items:center; gap:1rem; margin-bottom:1.4rem;">
    <i class="fas fa-check-circle" style="font-size:1.5rem; color:var(--warning);"></i>
    <div>
        <div style="font-weight:700; color:#7a5800; font-size:0.95rem;">
            Already Submitted
        </div>
        <div style="color:var(--text-muted); font-size:0.85rem; margin-top:2px;">
            Attendance for this date has been submitted and locked.
        </div>
    </div>
</div>
@elseif($session && !$isLocked)
<div style="background:rgba(25,135,84,0.07); border:1.5px solid rgba(25,135,84,0.25);
            border-radius:var(--radius); padding:0.8rem 1.2rem;
            display:flex; align-items:center; gap:0.8rem; margin-bottom:1.2rem;">
    <i class="fas fa-pencil-alt" style="color:var(--success);"></i>
    <span style="font-size:0.87rem; color:var(--success); font-weight:600;">
        Draft saved — you can continue editing or submit to lock.
    </span>
</div>
@endif

@if($students->isEmpty())
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    No active students found in this section.
</div>
@else

{{-- ── Editable Form ── --}}
@if($isEditable)
<form action="{{ route('teacher.attendance.save') }}" method="POST" id="att-form">
@csrf
<input type="hidden" name="date" value="{{ $date }}">
@endif

<div class="mb-2 card">
    <div class="card-header">
        <div style="display:flex; align-items:center; gap:0.8rem; flex-wrap:wrap;">
            <div class="card-header-title">
                <i class="fas fa-users"></i>
                {{ $students->count() }} Students
            </div>
            <span style="font-size:0.85rem; color:var(--text-muted);">
                {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}
            </span>
        </div>

        {{-- Mark All — only if editable --}}
        @if($isEditable)
        <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
            <button type="button" class="btn-outline-secondary btn btn-sm"
                    onclick="markAll('present')">
                <i class="fas fa-check"></i> All Present
            </button>
            <button type="button" class="btn-outline-secondary btn btn-sm"
                    onclick="markAll('absent')">
                <i class="fas fa-times"></i> All Absent
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
            <div class="sidebar-user-avatar"
                 style="width:36px; height:36px; font-size:0.85rem; flex-shrink:0;">
                {{ strtoupper(substr($student->full_name, 0, 1)) }}
            </div>

            {{-- Name --}}
            <div class="att-student-name">
                {{ $student->full_name }}
                <span class="att-student-roll">{{ $student->roll_number }}</span>
            </div>

            @if($isEditable)
            {{-- Hidden status input --}}
            <input type="hidden"
                   name="attendance[{{ $student->id }}][status]"
                   id="status-{{ $student->id }}"
                   value="{{ $status }}">

            {{-- Status Buttons --}}
            <div class="att-status-group">
                @foreach(['present','absent','late','leave'] as $s)
                <button type="button"
                        class="att-status-btn {{ $status === $s ? 'selected-'.$s : '' }}"
                        id="btn-{{ $student->id }}-{{ $s }}"
                        onclick="setStatus({{ $student->id }}, '{{ $s }}')">
                    @if($s === 'present')      <i class="fas fa-check"></i> P
                    @elseif($s === 'absent')   <i class="fas fa-times"></i> A
                    @elseif($s === 'late')     <i class="fas fa-clock"></i> L
                    @else                      <i class="fas fa-door-open"></i> Lv
                    @endif
                </button>
                @endforeach
            </div>

            {{-- Remarks --}}
            <input type="text"
                   name="attendance[{{ $student->id }}][remarks]"
                   class="form-control"
                   style="width:150px; font-size:0.8rem;"
                   placeholder="Remarks..."
                   value="{{ $existing?->remarks }}">

            @else
            {{-- Read-only badge --}}
            <span class="badge {{ $existing?->status_badge_class ?? 'badge-primary' }}"
                  style="font-size:0.82rem; padding:0.35rem 0.9rem;">
                {{ ucfirst($existing?->status ?? '—') }}
            </span>
            @if($existing?->remarks)
            <span style="font-size:0.8rem; color:var(--text-muted); font-style:italic;">
                {{ $existing->remarks }}
            </span>
            @endif
            @endif

        </div>
        @endforeach
    </div>
</div>

{{-- ── Live Summary + Action Bar ── --}}
@if($isEditable)
<div style="background:var(--white); border:1px solid var(--border);
            border-radius:var(--radius); padding:0.9rem 1.2rem;
            margin-bottom:1.2rem;
            display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
    <strong style="color:var(--primary); font-size:0.88rem;">Live Summary:</strong>
    <span class="att-pill present" id="count-present">0 Present</span>
    <span class="att-pill absent"  id="count-absent">0 Absent</span>
    <span class="att-pill late"    id="count-late">0 Late</span>
    <span class="att-pill leave"   id="count-leave">0 Leave</span>
</div>

<div style="display:flex; gap:0.8rem; align-items:center; flex-wrap:wrap;">
    <button type="submit" form="att-form" class="btn-outline-primary btn btn-lg">
        <i class="fas fa-save"></i> Save Draft
    </button>

    @if($session && !$session->isSubmitted() && !$session->isLocked())
    <button type="button" class="btn btn-primary btn-lg"
            onclick="smsConfirm(
                'Once submitted, this attendance will be permanently locked and cannot be edited.',
                () => document.getElementById('submit-form').submit(),
                'Submit & Lock Attendance',
                'warning'
            )">
        <i class="fas fa-paper-plane"></i> Submit & Lock
    </button>
    @endif

    <span style="font-size:0.82rem; color:var(--text-muted);">
        <i class="fas fa-info-circle"></i>
        Saving as draft keeps it editable. Submit to permanently lock.
    </span>
</div>

{{-- Hidden submit form --}}
@if($session)
<form action="{{ route('teacher.attendance.submit', $session) }}"
      method="POST" id="submit-form" style="display:none;">
    @csrf
</form>
@endif

</form>
@endif

@endif

@if($isEditable)
<script>
const statuses = {};

// Init from existing data
@foreach($students as $student)
@php $existing = $session?->records?->firstWhere('student_id', $student->id); @endphp
statuses[{{ $student->id }}] = '{{ $existing?->status ?? "present" }}';
@endforeach

function setStatus(studentId, status) {
    document.getElementById('status-' + studentId).value = status;

    ['present','absent','late','leave'].forEach(s => {
        const btn = document.getElementById('btn-' + studentId + '-' + s);
        if (btn) btn.className = 'att-status-btn';
    });

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
    const counts = { present: 0, absent: 0, late: 0, leave: 0 };
    Object.values(statuses).forEach(s => { if (counts[s] !== undefined) counts[s]++; });
    document.getElementById('count-present').textContent = counts.present + ' Present';
    document.getElementById('count-absent').textContent  = counts.absent  + ' Absent';
    document.getElementById('count-late').textContent    = counts.late    + ' Late';
    document.getElementById('count-leave').textContent   = counts.leave   + ' Leave';
}

// Block form submit if date is future (extra client-side guard)
document.getElementById('att-form').addEventListener('submit', function (e) {
    const dateVal  = document.querySelector('input[name="date"]').value;
    const today    = new Date().toISOString().split('T')[0];
    if (dateVal > today) {
        e.preventDefault();
        smsAlert('Attendance cannot be saved for future dates.', 'danger', 'Invalid Date');
    }
});

updateSummary();
</script>
@endif
@endsection