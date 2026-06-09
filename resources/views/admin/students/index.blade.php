@extends('layouts.app')
@section('title', 'Students')
@section('page-title', 'Students')

@section('content')

@php
    $activeYear = \App\Helpers\AcademicYearContext::current();
@endphp

<div class="page-header">
    <div>
        <div class="page-header-title">Students</div>
        <div class="page-header-sub">
            <i class="fas fa-calendar-alt" style="color:var(--accent);"></i>
            {{ $activeYear?->name ?? '—' }}
            &bull; Showing enrolled students for this academic year
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.enrollment.index') }}"
           class="btn-outline-primary btn btn-sm">
            <i class="fas fa-user-graduate"></i> Manage Enrollments
        </a>
        <a href="{{ route('admin.enrollment.admission') }}"
           class="btn btn-primary">
            <i class="fas fa-user-plus"></i> New Admission
        </a>
    </div>
</div>

{{-- Filters --}}
<form method="GET">
    <div class="filter-bar">
        <div>
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control"
                   value="{{ request('search') }}"
                   placeholder="Name, CNIC, Roll No, Father...">
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
            <select name="status" class="form-select"
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
            <a href="{{ route('admin.students.index') }}"
               class="btn-outline-secondary btn">Clear</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-users"></i>
            {{ $enrollments->total() }} student(s)
        </div>
        <span style="font-size:0.82rem; color:var(--text-muted);">
            Academic Year: <strong>{{ $activeYear?->name }}</strong>
        </span>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Roll No.</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Gender</th>
                    <th>Father</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $i => $enrollment)
                @php $student = $enrollment->student; @endphp
                <tr>
                    <td style="color:var(--text-muted); font-size:0.82rem;">
                        {{ ($enrollments->currentPage() - 1)
                           * $enrollments->perPage() + $i + 1 }}
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.7rem;">
                            @if($student->photo)
                                <img src="{{ $student->photo_url }}"
                                     style="width:34px; height:34px; border-radius:50%;
                                            object-fit:cover; border:2px solid var(--border);">
                            @else
                                <div class="sidebar-user-avatar"
                                     style="width:34px; height:34px; font-size:0.85rem; flex-shrink:0;">
                                    {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('admin.students.show', $student) }}"
                                   style="font-weight:700; color:var(--primary);
                                          font-size:0.88rem; text-decoration:none;">
                                    {{ $student->full_name }}
                                </a>
                                @if($student->cnic)
                                <div style="font-size:0.74rem; color:var(--text-muted);">
                                    {{ $student->cnic }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <code style="font-size:0.83rem;">
                            {{ $enrollment->roll_number ?? '—' }}
                        </code>
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
                        <span class="badge badge-primary"
                              style="font-size:0.72rem;">
                            {{ ucfirst($student->gender ?? '—') }}
                        </span>
                    </td>
                    <td style="font-size:0.85rem; color:var(--text-muted);">
                        {{ $student->father_name }}
                    </td>
                    <td>
                        <span class="badge {{ $enrollment->status_badge_class }}">
                            {{ $enrollment->status_label }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.students.show', $student) }}"
                               class="btn-outline-primary btn btn-sm"
                               title="View Profile">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.students.edit', $student) }}"
                               class="btn-outline-primary btn btn-sm"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('admin.fee.student.show', $student) }}"
                               class="btn-outline-secondary btn btn-sm"
                               title="Fees">
                                <i class="fas fa-receipt"></i>
                            </a>
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
                        No students enrolled for
                        <strong>{{ $activeYear?->name }}</strong>.
                        <div style="margin-top:1rem; display:flex; gap:0.8rem;
                                    justify-content:center;">
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
@endsection