@extends('layouts.app')
@section('title', 'Attendance Session')
@section('page-title', 'Attendance Session')

@section('content')

{{-- Print-only header --}}
<div style="display:none;" class="print-only">
    <div style="text-align:center; margin-bottom:1.5rem;
                padding-bottom:1rem; border-bottom:2px solid #1a3c5e;">
        <h2 style="font-family:serif; color:#1a3c5e; margin:0;">
            Attendance — {{ $session->date->format('l, d M Y') }}
        </h2>
        <p style="color:#666; margin:5px 0 0; font-size:0.9rem;">
            {{ $session->schoolClass->name ?? '' }}
            — Section {{ $session->section->name ?? '' }}
            &bull; Teacher: {{ $session->teacher->full_name ?? '—' }}
            &bull; Printed: {{ now()->format('d M Y, h:i A') }}
        </p>
    </div>
</div>
<style>@media print { .print-only { display:block !important; } }</style>

{{-- Page Header --}}
<div class="page-header no-print">
    <div>
        <div class="page-header-title">
            {{ $session->date->format('l, d M Y') }}
        </div>
        <div class="page-header-sub">
            {{ $session->schoolClass->name ?? '' }}
            — Section {{ $session->section->name ?? '' }}
            &bull; Class Teacher: {{ $session->teacher->full_name ?? '—' }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-outline-primary btn btn-sm">
            <i class="fas fa-print"></i> Print
        </button>

        {{-- Lock / Unlock --}}
        @if($session->isLocked())
        <form action="{{ route('admin.attendance.unlock', $session) }}"
              method="POST"
              data-confirm="Unlock this session? Teacher will be able to edit it again."
              data-type="warning" data-title="Unlock Session">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm">
                <i class="fas fa-unlock"></i> Unlock
            </button>
        </form>
        @else
        <form action="{{ route('admin.attendance.lock', $session) }}"
              method="POST"
              data-confirm="Lock this session? It will be marked as submitted."
              data-type="info" data-title="Lock Session">
            @csrf
            <button type="submit" class="btn-outline-primary btn btn-sm">
                <i class="fas fa-lock"></i> Lock
            </button>
        </form>
        @endif

        {{-- Delete --}}
        <form action="{{ route('admin.attendance.destroy', $session) }}"
              method="POST"
              data-confirm="Delete this entire attendance session? All records will be permanently removed."
              data-type="danger" data-title="Delete Session">
            @csrf @method('DELETE')
            <button type="submit" class="btn-outline-danger btn btn-sm">
                <i class="fas fa-trash-alt"></i> Delete
            </button>
        </form>

        <a href="{{ route('admin.attendance.index') }}"
           class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

{{-- Status Banner --}}
<div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;
            margin-bottom:1.4rem;">
    <div class="att-summary-pills">
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
    </div>
    <span class="badge {{ $session->status_badge_class }}"
          style="font-size:0.85rem; padding:0.4rem 1rem;">
        {{ ucfirst($session->status) }}
        @if($session->isLocked())
            <i class="fas fa-lock"></i>
        @endif
    </span>
    @if($session->submitted_at)
    <span style="font-size:0.82rem; color:var(--text-muted);">
        Submitted: {{ $session->submitted_at->format('d M Y, h:i A') }}
    </span>
    @endif
</div>

{{-- Admin Edit Note --}}
<div class="alert alert-info no-print" style="margin-bottom:1.2rem; font-size:0.87rem;">
    <i class="fas fa-shield-alt"></i>
    <strong>Admin Override:</strong>
    You can edit any student's attendance record regardless of session lock status.
    Changes are saved immediately per student, or use <strong>Save All</strong> for bulk update.
</div>

{{-- Bulk Save Form --}}
<form action="{{ route('admin.attendance.update-session', $session) }}"
      method="POST" id="bulk-form">
@csrf

<div class="card">
    <div class="card-header no-print">
        <div class="card-header-title">
            <i class="fas fa-users"></i>
            {{ $allStudents->count() }} Students
        </div>
        <div class="d-flex align-items-center gap-2">
            {{-- Quick mark all --}}
            <button type="button" class="btn-outline-secondary btn btn-sm"
                    onclick="markAllAdmin('present')">
                All Present
            </button>
            <button type="button" class="btn-outline-secondary btn btn-sm"
                    onclick="markAllAdmin('absent')">
                All Absent
            </button>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save"></i> Save All
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Roll No.</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th class="no-print">Quick Save</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allStudents as $i => $student)
                @php
                    $record  = $session->records->firstWhere('student_id', $student->id);
                    $status  = $record?->status ?? 'present';
                    $remarks = $record?->remarks ?? '';
                @endphp
                <tr id="student-row-{{ $student->id }}">
                    <td style="color:var(--text-muted);">{{ $i + 1 }}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.6rem;">
                            <div class="sidebar-user-avatar"
                                 style="width:30px; height:30px; font-size:0.78rem; flex-shrink:0;">
                                {{ strtoupper(substr($student->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <strong style="font-size:0.88rem;">
                                    {{ $student->full_name }}
                                </strong>
                                <div style="font-size:0.75rem; color:var(--text-muted);">
                                    {{ $student->section->name ?? '' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <code style="font-size:0.82rem;">{{ $student->roll_number }}</code>
                    </td>
                    <td>
                        {{-- Inline status selector --}}
                        <select name="records[{{ $student->id }}][status]"
                                id="sel-{{ $student->id }}"
                                class="form-select"
                                style="font-size:0.85rem; font-weight:600; width:130px;
                                       border-color:{{ $status === 'present' ? 'var(--success)'
                                            : ($status === 'absent' ? 'var(--danger)'
                                            : ($status === 'late' ? 'var(--warning)'
                                            : 'var(--info)')) }};"
                                onchange="colorSelect(this)">
                            <option value="present"
                                    {{ $status === 'present' ? 'selected' : '' }}>
                                ✓ Present
                            </option>
                            <option value="absent"
                                    {{ $status === 'absent' ? 'selected' : '' }}>
                                ✗ Absent
                            </option>
                            <option value="late"
                                    {{ $status === 'late' ? 'selected' : '' }}>
                                ⏱ Late
                            </option>
                            <option value="leave"
                                    {{ $status === 'leave' ? 'selected' : '' }}>
                                🚪 Leave
                            </option>
                        </select>
                    </td>
                    <td>
                        <input type="text"
                               name="records[{{ $student->id }}][remarks]"
                               class="form-control"
                               style="font-size:0.82rem; width:180px;"
                               value="{{ $remarks }}"
                               placeholder="Optional remarks">
                    </td>
                    <td class="no-print">
                        {{-- Per-student quick save form --}}
                        <form action="{{ route('admin.attendance.update-record',
                                       [$session, $student]) }}"
                              method="POST" class="quick-save-form"
                              data-student="{{ $student->id }}"
                              style="display:inline;">
                            @csrf
                            <input type="hidden" name="status"
                                   id="qs-status-{{ $student->id }}"
                                   value="{{ $status }}">
                            <input type="hidden" name="remarks"
                                   id="qs-remarks-{{ $student->id }}"
                                   value="{{ $remarks }}">
                            <button type="button"
                                    class="btn-outline-primary btn btn-sm"
                                    onclick="quickSave({{ $student->id }})"
                                    title="Save this student only">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Bottom Save All --}}
    <div class="card-body no-print"
         style="border-top:1px solid var(--border);
                display:flex; gap:0.8rem; align-items:center;">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save All Changes
        </button>
        <span style="font-size:0.82rem; color:var(--text-muted);">
            <i class="fas fa-info-circle"></i>
            Changes affect all students. Use <strong>Quick Save</strong>
            (<i class="fas fa-check"></i>) to save one student at a time.
        </span>
    </div>
</div>
</form>

<script>
// Color select border based on value
function colorSelect(sel) {
    const colors = {
        present : 'var(--success)',
        absent  : 'var(--danger)',
        late    : 'var(--warning)',
        leave   : 'var(--info)',
    };
    sel.style.borderColor = colors[sel.value] || 'var(--border)';

    // Sync hidden input for quick save
    const sid = sel.id.replace('sel-', '');
    const qs  = document.getElementById('qs-status-' + sid);
    if (qs) qs.value = sel.value;
}

// Quick save: copy current row values to hidden form and submit
function quickSave(studentId) {
    const sel     = document.getElementById('sel-' + studentId);
    const remarks = document.querySelector(
        `input[name="records[${studentId}][remarks]"]`);

    const qsStatus  = document.getElementById('qs-status-' + studentId);
    const qsRemarks = document.getElementById('qs-remarks-' + studentId);

    if (sel)     qsStatus.value  = sel.value;
    if (remarks) qsRemarks.value = remarks.value;

    // Find and submit the quick-save form for this student
    const form = document.querySelector(
        `.quick-save-form[data-student="${studentId}"]`);
    if (form) form.submit();
}

// Mark all students with one status
function markAllAdmin(status) {
    document.querySelectorAll('[id^="sel-"]').forEach(sel => {
        sel.value = status;
        colorSelect(sel);
    });
}

// Init colors on load
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[id^="sel-"]').forEach(colorSelect);
});
</script>
@endsection