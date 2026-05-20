@extends('layouts.app')
@section('title', 'Admin Users')
@section('page-title', 'Admin Users')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Admin User Management</div>
        <div class="page-header-sub">Create and manage admin accounts</div>
    </div>
    <a href="{{ route('super.admins.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Add Admin
    </a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Email</th>
                    <th>Assigned Campuses</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="sidebar-user-avatar" style="width:34px; height:34px; font-size:0.85rem;">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                            <strong>{{ $admin->name }}</strong>
                        </div>
                    </td>
                    <td>{{ $admin->email }}</td>
                    <td>
                        @if($admin->campuses->isEmpty())
                            <span style="color:var(--text-muted); font-size:0.85rem;">No campus assigned</span>
                        @else
                            <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                                @foreach($admin->campuses as $campus)
                                    <span class="badge badge-primary">{{ $campus->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td>{{ $admin->created_at->format('d M, Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('super.admins.edit', $admin) }}" class="btn-outline-primary btn btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('super.admins.destroy', $admin) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-outline-danger btn btn-sm"
                                        onclick="return confirm('Remove {{ addslashes($admin->name) }}?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; color:var(--text-muted); padding:2.5rem;">No admins yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($admins->hasPages())
    <div style="padding:1rem 1.4rem; border-top:1px solid var(--border);">
        {{ $admins->links() }}
    </div>
    @endif
</div>
@endsection