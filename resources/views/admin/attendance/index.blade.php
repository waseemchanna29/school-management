@extends('layouts.app')
@section('title', 'Attendance')
@section('page-title', 'Attendance')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Attendance Records</div>
        <div class="page-header-sub">View and manage all attendance sessions</div>
    </div>
    <a href="{{ route('admin.attendance.report') }}" class="btn btn-primary">
        <i class="fas fa-chart-bar"></i> Monthly Report
    </a>
</div>

<form method="GET">
    <div class="filter-bar">
        <div>
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Section</label>
            <select name="section_id" class="form-select">
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                        {{ $section->schoolClass->name ?? '' }} – {{ $section->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
        </div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
            </select>
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.attendance.index') }}" class="btn-outline-secondary btn">Clear</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Class / Section</th>
                    <th>Teacher</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td>
                        <strong>{{ $session->date->format('d M, Y') }}</strong>
                        <div style="font-size:0.77rem; color:var(--text-muted);">
                            {{ $session->date->format('l') }}
                        </div>
                    </td>
                    <td>
                        {{ $session->schoolClass->name ?? '—' }}
                        — {{ $session->section->name ?? '—' }}
                    </td>
                    <td>{{ $session->teacher->full_name ?? '—' }}</td>
                    <td><span class="att-pill present">{{ $session->present_count }}</span></td>
                    <td><span class="att-pill absent">{{ $session->absent_count }}</span></td>
                    <td><span class="att-pill late">{{ $session->late_count }}</span></td>
                    <td>
                        <span class="badge {{ $session->status_badge_class }}">
                            {{ ucfirst($session->status) }}
                        </span>
                        @if($session->isLocked())
                            <i class="fas fa-lock" style="color:var(--text-muted); font-size:0.8rem;"></i>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.attendance.show', $session) }}"
                               class="btn-outline-primary btn btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($session->isLocked())
                            <form action="{{ route('admin.attendance.unlock', $session) }}"
                                  method="POST"
                                  data-confirm="Unlock this session? The teacher will be able to edit it."
                                  data-type="warning"
                                  data-title="Unlock Session">
                                @csrf
                                <button type="submit" class="btn-outline-secondary btn btn-sm">
                                    <i class="fas fa-unlock"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:var(--text-muted); padding:3rem;">
                        <i class="fas fa-clipboard" style="font-size:3rem; display:block; margin-bottom:1rem; color:var(--border);"></i>
                        No attendance sessions found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sessions->hasPages())
    <div style="padding:1rem 1.4rem; border-top:1px solid var(--border);">
        {{ $sessions->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection