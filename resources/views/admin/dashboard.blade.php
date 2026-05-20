@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">School Overview</div>
        <div class="page-header-sub">Welcome back, {{ Auth::user()->name }}. Here's what's happening today.</div>
    </div>
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Enroll Student
    </a>
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
                <div class="card-header-title"><i class="fas fa-user-graduate"></i> Recently Enrolled Students</div>
                <a href="{{ route('admin.students.index') }}" class="btn-outline-primary btn btn-sm">View All</a>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Roll No.</th>
                            <th>Class</th>
                            <th>Gender</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentStudents as $student)
                        <tr>
                            <td>
                                <a href="{{ route('admin.students.show', $student) }}" style="font-weight:600; color:var(--primary);">
                                    {{ $student->full_name }}
                                </a>
                                <div style="font-size:0.78rem; color:var(--text-muted);">{{ $student->father_name }}</div>
                            </td>
                            <td><code style="font-size:0.82rem;">{{ $student->roll_number }}</code></td>
                            <td>
                                @if($student->schoolClass)
                                    {{ $student->schoolClass->name }}
                                    @if($student->section) – {{ $student->section->name }} @endif
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
                        <tr><td colspan="5" style="text-align:center; color:var(--text-muted); padding:2rem;">No students yet.</td></tr>
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
            <div style="padding:0;">
                @forelse($recentTeachers as $teacher)
                <div style="padding:0.8rem 1.3rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:0.8rem;">
                    <div class="sidebar-user-avatar" style="width:36px; height:36px; font-size:0.85rem; background:var(--primary);">
                        {{ strtoupper(substr($teacher->full_name, 0, 1)) }}
                    </div>
                    <div style="flex:1; overflow:hidden;">
                        <a href="{{ route('admin.teachers.show', $teacher) }}"
                           style="font-weight:600; font-size:0.88rem; color:var(--primary); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ $teacher->full_name }}
                        </a>
                        <span style="font-size:0.78rem; color:var(--text-muted);">{{ $teacher->employment_type }}</span>
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
</div>
@endsection