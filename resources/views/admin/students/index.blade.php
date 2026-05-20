@extends('layouts.app')
@section('title', 'Students')
@section('page-title', 'Students')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Student Management</div>
        <div class="page-header-sub">View, search, and manage all enrolled students</div>
    </div>
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Enroll Student
    </a>
</div>

<form method="GET" action="{{ route('admin.students.index') }}">
    <div class="filter-bar">
        <div>
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control"
                   placeholder="Name, Roll No, GR No, Father..." value="{{ request('search') }}">
        </div>
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
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach(['active','inactive','transferred','expelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.students.index') }}" class="btn-outline-secondary btn">Clear</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Roll / GR No.</th>
                    <th>Father's Name</th>
                    <th>Class / Section</th>
                    <th>Gender</th>
                    <th>Admitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($student->photo)
                                <img src="{{ Storage::url($student->photo) }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid var(--border);">
                            @else
                                <div class="sidebar-user-avatar" style="width:34px;height:34px;font-size:0.82rem;flex-shrink:0;">
                                    {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <strong>{{ $student->full_name }}</strong>
                            </div>
                        </div>
                    </td>
                    <td>
                        <code style="font-size:0.8rem;">{{ $student->roll_number }}</code><br>
                        <span style="font-size:0.77rem; color:var(--text-muted);">{{ $student->gr_number }}</span>
                    </td>
                    <td>{{ $student->father_name }}</td>
                    <td>
                        @if($student->schoolClass)
                            <strong>{{ $student->schoolClass->name }}</strong>
                            @if($student->section) – {{ $student->section->name }} @endif
                        @endif
                    </td>
                    <td>{{ $student->gender }}</td>
                    <td>{{ $student->admission_date->format('d M, Y') }}</td>
                    <td>
                        <span class="badge {{ $student->status_badge_class }}">{{ ucfirst($student->status) }}</span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.students.show', $student) }}" class="btn-outline-primary btn btn-sm"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.students.edit', $student) }}" class="btn-outline-primary btn btn-sm"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.students.destroy', $student) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-outline-danger btn btn-sm"
                                        onclick="return confirm('Remove {{ addslashes($student->full_name) }}?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                    <i class="fas fa-user-graduate" style="font-size:2.5rem; display:block; margin-bottom:0.5rem;"></i>
                    No students found.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
    <div style="padding:1rem 1.4rem; border-top:1px solid var(--border);">
        {{ $students->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection