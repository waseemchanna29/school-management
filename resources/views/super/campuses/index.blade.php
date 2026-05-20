@extends('layouts.app')
@section('title', 'Campuses')
@section('page-title', 'Campuses')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Campus Management</div>
        <div class="page-header-sub">Manage all school campuses in the network</div>
    </div>
    <a href="{{ route('super.campuses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> Add Campus
    </a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Campus</th>
                    <th>Code</th>
                    <th>Location</th>
                    <th>Principal</th>
                    <th>Admins</th>
                    <th>Teachers</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campuses as $campus)
                <tr>
                    <td>
                        <strong>{{ $campus->name }}</strong>
                        @if($campus->email)
                        <div style="font-size:0.78rem; color:var(--text-muted);">{{ $campus->email }}</div>
                        @endif
                    </td>
                    <td><code style="font-size:0.81rem;">{{ $campus->code }}</code></td>
                    <td>{{ $campus->city }}, {{ $campus->province }}</td>
                    <td>{{ $campus->principal_name ?? '—' }}</td>
                    <td><span class="badge badge-primary">{{ $campus->admins_count }}</span></td>
                    <td><span class="badge badge-info">{{ $campus->teachers_count }}</span></td>
                    <td><span class="badge badge-approved">{{ $campus->students_count }}</span></td>
                    <td>
                        <span class="badge {{ $campus->is_active ? 'badge-approved' : 'badge-rejected' }}">
                            {{ $campus->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('super.campuses.show', $campus) }}" class="btn-outline-primary btn btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('super.campuses.edit', $campus) }}" class="btn-outline-primary btn btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('super.campuses.destroy', $campus) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-outline-danger btn btn-sm"
                                        onclick="return confirm('Delete {{ addslashes($campus->name) }}?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center; color:var(--text-muted); padding:2.5rem;">No campuses yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($campuses->hasPages())
    <div style="padding:1rem 1.4rem; border-top:1px solid var(--border);">
        {{ $campuses->links() }}
    </div>
    @endif
</div>
@endsection