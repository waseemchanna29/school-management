@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">School Overview</div>
        <div class="page-header-sub">Welcome back, {{ Auth::user()->name }}.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Enroll Student
        </a>
        <!-- Monthly Invoice Button -->
        <button class="btn btn-warning" onclick="document.getElementById('monthly-modal').style.display='flex'">
            <i class="fas fa-file-invoice-dollar"></i> Generate Monthly Invoices
        </button>
    </div>
</div>

<!-- Stats -->
<div class="stat-cards-grid">
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="fas fa-user-graduate"></i></div>
        <div><span class="stat-card-value">{{ $stats['students'] }}</span><span class="stat-card-label">Total Students</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="fas fa-check-circle"></i></div>
        <div><span class="stat-card-value">{{ $stats['active_students'] }}</span><span class="stat-card-label">Active Students</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon yellow"><i class="fas fa-chalkboard-teacher"></i></div>
        <div><span class="stat-card-value">{{ $stats['teachers'] }}</span><span class="stat-card-label">Teachers</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon teal"><i class="fa-layer-group fas"></i></div>
        <div><span class="stat-card-value">{{ $stats['classes'] }}</span><span class="stat-card-label">Active Classes</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon purple"><i class="fas fa-book"></i></div>
        <div><span class="stat-card-value">{{ $stats['subjects'] }}</span><span class="stat-card-label">Subjects</span></div>
    </div>
</div>

<div class="row">
    <!-- Recent Students -->
    <div class="mb-3 col-8">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-user-graduate"></i> Recently Enrolled</div>
                <a href="{{ route('admin.students.index') }}" class="btn-outline-primary btn btn-sm">View All</a>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th><th>Roll No.</th><th>Class</th><th>Gender</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentStudents as $student)
                        <tr>
                            <td>
                                <a href="{{ route('admin.students.show', $student) }}"
                                   style="font-weight:600; color:var(--primary);">
                                    {{ $student->full_name }}
                                </a>
                                <div style="font-size:0.78rem; color:var(--text-muted);">
                                    {{ $student->father_name }}
                                </div>
                            </td>
                            <td><code style="font-size:0.82rem;">{{ $student->roll_number }}</code></td>
                            <td>
                                @if($student->schoolClass)
                                    {{ $student->schoolClass->name }}
                                    @if($student->section) — {{ $student->section->name }} @endif
                                @endif
                            </td>
                            <td>{{ $student->gender }}</td>
                            <td>
                                <span class="badge {{ $student->status_badge_class }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; color:var(--text-muted); padding:2rem;">
                                No students yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Teachers -->
    <div class="mb-3 col-4">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-chalkboard-teacher"></i> Teachers</div>
                <a href="{{ route('admin.teachers.create') }}" class="btn-outline-primary btn btn-sm">
                    <i class="fas fa-plus"></i> Add
                </a>
            </div>
            @forelse($recentTeachers as $teacher)
            <div style="padding:0.8rem 1.3rem; border-bottom:1px solid var(--border);
                        display:flex; align-items:center; gap:0.8rem;">
                <div class="sidebar-user-avatar"
                     style="width:36px; height:36px; font-size:0.85rem; background:var(--primary);">
                    {{ strtoupper(substr($teacher->full_name, 0, 1)) }}
                </div>
                <div style="flex:1; overflow:hidden;">
                    <a href="{{ route('admin.teachers.show', $teacher) }}"
                       style="font-weight:600; font-size:0.88rem; color:var(--primary);
                              display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $teacher->full_name }}
                    </a>
                    <span style="font-size:0.78rem; color:var(--text-muted);">
                        {{ $teacher->employment_type }}
                    </span>
                </div>
                <span class="badge {{ $teacher->is_active ? 'badge-approved' : 'badge-rejected' }}">
                    {{ $teacher->is_active ? 'Active' : 'Off' }}
                </span>
            </div>
            @empty
            <div style="padding:2rem; text-align:center; color:var(--text-muted);">No teachers yet.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- Generate Monthly Invoices Modal -->
<div id="monthly-modal"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5);
            z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius); padding:2rem;
                width:100%; max-width:480px; box-shadow:var(--shadow-md);">

        <div style="display:flex; align-items:center; gap:0.8rem; margin-bottom:1.3rem;">
            <div style="width:44px; height:44px; background:rgba(232,160,32,0.15);
                        border-radius:var(--radius); display:flex; align-items:center;
                        justify-content:center; font-size:1.2rem; color:var(--accent);">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h3 style="font-family:var(--font-display); color:var(--primary); margin:0;">
                    Generate Monthly Invoices
                </h3>
                <p style="color:var(--text-muted); font-size:0.83rem; margin:2px 0 0;">
                    Creates invoices for all active students with monthly fee lines.
                </p>
            </div>
        </div>

        <div class="alert alert-info" style="margin-bottom:1.2rem;">
            <i class="fas fa-info-circle"></i>
            Students who already have an invoice for the selected month will be skipped automatically.
        </div>

        <form action="{{ route('admin.dashboard.generate-monthly') }}" method="POST" novalidate>
            @csrf

            @php
                $currentYear  = date('Y');
                $currentMonth = (int) date('n');
                $academicYear = $currentMonth >= 4
                    ? "$currentYear-" . ($currentYear + 1)
                    : ($currentYear - 1) . "-$currentYear";
            @endphp

            <div class="row">
                <div class="mb-form col-6">
                    <label class="form-label">Month *</label>
                    <select name="month" class="form-select">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m === $currentMonth ? 'selected' : '' }}>
                                {{ date('F', mktime(0,0,0,$m,1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-form col-6">
                    <label class="form-label">Year *</label>
                    <input type="number" name="year" class="form-control"
                           value="{{ $currentYear }}" min="2020" max="2099">
                </div>
            </div>

            <div class="row">
                <div class="mb-form col-6">
                    <label class="form-label">Academic Year *</label>
                    <select name="academic_year" class="form-select">
                        @for($y = (int)date('Y') - 1; $y <= (int)date('Y') + 1; $y++)
                            @php $ay = $y . '-' . ($y+1); @endphp
                            <option value="{{ $ay }}" {{ $ay === $academicYear ? 'selected' : '' }}>
                                {{ $ay }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="mb-form col-6">
                    <label class="form-label">Due Date *</label>
                    <input type="date" name="due_date" class="form-control"
                           value="{{ date('Y-m-t') }}">
                </div>
            </div>

            <div style="display:flex; gap:0.8rem; margin-top:0.5rem;">
                <button type="submit" class="btn btn-warning"
                        onclick="return confirm('Generate monthly invoices for all active students this month?')">
                    <i class="fas fa-bolt"></i> Generate Now
                </button>
                <button type="button" class="btn-outline-secondary btn"
                        onclick="document.getElementById('monthly-modal').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection