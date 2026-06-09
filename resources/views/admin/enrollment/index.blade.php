@extends('layouts.app')
@section('title', 'Enrollments')
@section('page-title', 'Enrollments')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Student Enrollments</div>
        <div class="page-header-sub">
            @php $year = \App\Helpers\AcademicYearContext::current(); @endphp
            Academic Year: <strong>{{ $year?->name ?? '—' }}</strong>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.enrollment.carry-forward') }}"
           class="btn-outline-primary btn">
            <i class="fas fa-forward"></i> Carry Forward
        </a>
        <a href="{{ route('admin.enrollment.create') }}"
           class="btn-outline-primary btn">
            <i class="fas fa-user-check"></i> Enroll Existing
        </a>
        <a href="{{ route('admin.enrollment.admission') }}"
           class="btn btn-primary">
            <i class="fas fa-user-plus"></i> New Admission
        </a>
    </div>
</div>

{{-- Summary Cards --}}
<div class="fee-summary-bar" style="margin-bottom:1.4rem;">
    @foreach([
        'active'      => ['Active',      'var(--success)', 'badge-approved'],
        'passed'      => ['Passed',       'var(--info)',    'badge-info'],
        'detained'    => ['Detained',     'var(--warning)', 'badge-pending'],
        'left'        => ['Left',         'var(--danger)',  'badge-rejected'],
        'transferred' => ['Transferred',  'var(--primary)', 'badge-primary'],
    ] as $status => [$label, $color, $badge])
    <div class="fee-summary-card"
         style="border-top-color:{{ $color }}; cursor:pointer;"
         onclick="filterByStatus('{{ $status }}')">
        <span class="fee-summary-amount" style="color:{{ $color }};">
            {{ $summary[$status] ?? 0 }}
        </span>
        <span class="fee-summary-label">{{ $label }}</span>
    </div>
    @endforeach
    <div class="fee-summary-card" style="border-top-color:var(--primary);">
        <span class="fee-summary-amount">{{ $summary->sum() }}</span>
        <span class="fee-summary-label">Total</span>
    </div>
</div>

{{-- Filters --}}
<form method="GET" id="filter-form">
    <div class="filter-bar" style="margin-bottom:1.2rem;">
        <div>
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control"
                   value="{{ request('search') }}"
                   placeholder="Name, CNIC, Roll No...">
        </div>
        <div>
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select"
                    onchange="this.form.submit()">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}"
                            {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Section</label>
            <select name="section_id" class="form-select"
                    onchange="this.form.submit()">
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}"
                            {{ request('section_id') == $section->id ? 'selected' : '' }}>
                        {{ $section->schoolClass->name ?? '' }} – {{ $section->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-select" id="status-filter"
                    onchange="this.form.submit()">
                <option value="">All</option>
                @foreach(['active','passed','detained','left','transferred'] as $s)
                    <option value="{{ $s }}"
                            {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="{{ route('admin.enrollment.index') }}"
               class="btn-outline-secondary btn">Clear</a>
        </div>
    </div>
</form>

{{-- Bulk Status Form --}}
<form action="{{ route('admin.enrollment.bulk-status') }}"
      method="POST" id="bulk-form">
@csrf
<div class="card">
    <div class="card-header no-print">
        <div class="card-header-title">
            <i class="fas fa-list"></i> Enrolled Students
        </div>
        <div class="d-flex align-items-center gap-2">
            <span id="selected-count"
                  style="font-size:0.83rem; color:var(--text-muted);">
                0 selected
            </span>
            <select name="status" class="form-select" style="width:150px;"
                    id="bulk-status-select">
                @foreach(['active','passed','detained','left','transferred'] as $s)
                    <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-outline-primary btn btn-sm"
                    onclick="return confirmBulk()"
                    id="bulk-btn" disabled>
                <i class="fas fa-check-double"></i> Apply to Selected
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" id="select-all"
                               style="width:15px; height:15px;
                                      accent-color:var(--primary);"
                               onchange="toggleAll(this)">
                    </th>
                    <th>#</th>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Roll No.</th>
                    <th>Enrolled On</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $i => $enrollment)
                <tr>
                    <td>
                        <input type="checkbox"
                               name="enrollment_ids[]"
                               value="{{ $enrollment->id }}"
                               class="row-checkbox"
                               style="width:15px; height:15px;
                                      accent-color:var(--primary);"
                               onchange="updateSelected()">
                    </td>
                    <td style="color:var(--text-muted);">
                        {{ ($enrollments->currentPage() - 1)
                           * $enrollments->perPage() + $i + 1 }}
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.6rem;">
                            <div class="sidebar-user-avatar"
                                 style="width:32px; height:32px; font-size:0.8rem;">
                                {{ strtoupper(substr($enrollment->student->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <a href="{{ route('admin.students.show', $enrollment->student) }}"
                                   style="font-weight:700; color:var(--primary);
                                          font-size:0.88rem; text-decoration:none;">
                                    {{ $enrollment->student->full_name }}
                                </a>
                                <div style="font-size:0.74rem; color:var(--text-muted);">
                                    {{ $enrollment->student->father_name }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong>{{ $enrollment->schoolClass->name ?? '—' }}</strong>
                    </td>
                    <td>
                        <span class="badge badge-info">
                            {{ $enrollment->section->name ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <code style="font-size:0.83rem;">
                            {{ $enrollment->roll_number ?? '—' }}
                        </code>
                    </td>
                    <td style="font-size:0.82rem; color:var(--text-muted);">
                        {{ $enrollment->enrolled_at?->format('d M, Y') ?? '—' }}
                    </td>
                    <td>
                        <span class="badge {{ $enrollment->status_badge_class }}">
                            {{ $enrollment->status_label }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.enrollment.edit', $enrollment) }}"
                               class="btn-outline-primary btn btn-sm"
                               title="Edit Enrollment">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('admin.students.show', $enrollment->student) }}"
                               class="btn-outline-secondary btn btn-sm"
                               title="Student Profile">
                                <i class="fas fa-user"></i>
                            </a>
                            <form action="{{ route('admin.enrollment.destroy', $enrollment) }}"
                                  method="POST"
                                  data-confirm="Remove this student's enrollment for the current year?"
                                  data-type="danger"
                                  data-title="Remove Enrollment">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="btn-outline-danger btn btn-sm">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9"
                        style="text-align:center; color:var(--text-muted); padding:3rem;">
                        <i class="fas fa-user-graduate"
                           style="font-size:3rem; display:block;
                                  margin-bottom:1rem; color:var(--border);"></i>
                        No students enrolled yet for this academic year.
                        <div style="margin-top:1rem; display:flex;
                                    gap:0.8rem; justify-content:center;">
                            <a href="{{ route('admin.enrollment.carry-forward') }}"
                               class="btn btn-primary">
                                <i class="fas fa-forward"></i> Carry Forward
                            </a>
                            <a href="{{ route('admin.enrollment.admission') }}"
                               class="btn-outline-primary btn">
                                <i class="fas fa-user-plus"></i> New Admission
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($enrollments->hasPages())
    <div style="padding:1rem 1.4rem; border-top:1px solid var(--border);">
        {{ $enrollments->withQueryString()->links() }}
    </div>
    @endif
</div>
</form>

<script>
function toggleAll(master) {
    document.querySelectorAll('.row-checkbox')
        .forEach(cb => cb.checked = master.checked);
    updateSelected();
}

function updateSelected() {
    const count = document.querySelectorAll('.row-checkbox:checked').length;
    document.getElementById('selected-count').textContent =
        count + ' selected';
    document.getElementById('bulk-btn').disabled = count === 0;
    document.getElementById('select-all').checked =
        count === document.querySelectorAll('.row-checkbox').length;
}

function confirmBulk() {
    const count  = document.querySelectorAll('.row-checkbox:checked').length;
    const status = document.getElementById('bulk-status-select').value;
    if (!count) return false;
    smsConfirm(
        `Mark ${count} student(s) as "${status}"?`,
        () => document.getElementById('bulk-form').submit(),
        'Bulk Status Update',
        'warning'
    );
    return false;
}

function filterByStatus(status) {
    document.getElementById('status-filter').value = status;
    document.getElementById('filter-form').submit();
}
</script>
@endsection