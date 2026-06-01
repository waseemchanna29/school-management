@extends('layouts.app')
@section('title', 'Teachers')
@section('page-title', 'Teachers')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Teacher Management</div>
        <div class="page-header-sub">View and manage all teaching staff</div>
    </div>
    <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Add Teacher
    </a>
</div>

<form method="GET" action="{{ route('admin.teachers.index') }}">
    <div class="filter-bar">
        <div>
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name, CNIC, Code..." value="{{ request('search') }}">
        </div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <a href="{{ route('admin.teachers.index') }}" class="btn-outline-secondary btn">Clear</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Teacher</th>
                    <th>Employee Code</th>
                    <th>CNIC</th>
                    <th>Phone</th>
                    <th>Qualification</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teachers as $teacher)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($teacher->photo)
                                <img src="{{ Storage::url($teacher->photo) }}" class="profile-avatar" style="width:36px; height:36px;">
                            @else
                                <div class="sidebar-user-avatar" style="width:36px; height:36px; font-size:0.85rem;">
                                    {{ strtoupper(substr($teacher->full_name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <strong>{{ $teacher->full_name }}</strong>
                                <div style="font-size:0.78rem; color:var(--text-muted);">{{ $teacher->gender }}</div>
                            </div>
                        </div>
                    </td>
                    <td><code style="font-size:0.82rem;">{{ $teacher->employee_code }}</code></td>
                    <td>{{ $teacher->cnic }}</td>
                    <td>{{ $teacher->phone }}</td>
                    <td>{{ $teacher->qualification }}</td>
                    <td><span class="badge badge-info">{{ $teacher->employment_type }}</span></td>
                    <td>
                        <span class="badge {{ $teacher->is_active ? 'badge-approved' : 'badge-rejected' }}">
                            {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn-outline-primary btn btn-sm"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn-outline-primary btn btn-sm"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" style="display:inline;" data-confirm="Remove {{ addslashes($teacher->full_name) }}?" data-type="danger" data-title="Delete">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-outline-danger btn btn-sm"
                                       >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                    <i class="fas fa-chalkboard-teacher" style="font-size:2.5rem; display:block; margin-bottom:0.5rem;"></i>
                    No teachers found.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($teachers->hasPages())
    <div style="padding:1rem 1.4rem; border-top:1px solid var(--border);">
        {{ $teachers->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection