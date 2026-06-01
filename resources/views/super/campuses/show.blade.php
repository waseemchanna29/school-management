@extends('layouts.app')
@section('title', 'Campus Details')
@section('page-title', 'Campus Details')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">{{ $campus->name }}</div>
        <div class="page-header-sub">
            <code>{{ $campus->code }}</code> &bull;
            {{ $campus->city }}, {{ $campus->province }} &bull;
            <span class="badge {{ $campus->is_active ? 'badge-approved' : 'badge-rejected' }}">
                {{ $campus->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('super.campuses.edit', $campus) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('super.campuses.index') }}" class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-8">
        <!-- Campus Info -->
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-building"></i> Campus Information</div>
            </div>
            <div class="card-body">
                <div class="profile-meta-grid">
                    <div><span class="profile-meta-label">Campus Name</span><span class="profile-meta-value">{{ $campus->name }}</span></div>
                    <div><span class="profile-meta-label">Campus Code</span><span class="profile-meta-value"><code>{{ $campus->code }}</code></span></div>
                    <div><span class="profile-meta-label">Phone</span><span class="profile-meta-value">{{ $campus->phone ?? '—' }}</span></div>
                    <div><span class="profile-meta-label">Email</span><span class="profile-meta-value">{{ $campus->email ?? '—' }}</span></div>
                    <div><span class="profile-meta-label">Principal</span><span class="profile-meta-value">{{ $campus->principal_name ?? '—' }}</span></div>
                    <div><span class="profile-meta-label">City / District</span><span class="profile-meta-value">{{ $campus->city }}, {{ $campus->district }}</span></div>
                    <div><span class="profile-meta-label">Province</span><span class="profile-meta-value">{{ $campus->province }}</span></div>
                    <div style="grid-column:1/-1;"><span class="profile-meta-label">Address</span><span class="profile-meta-value">{{ $campus->address }}</span></div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stat-cards-grid" style="grid-template-columns:repeat(4,1fr); margin-bottom:1.4rem;">
            <div class="stat-card">
                <div class="stat-card-icon blue"><i class="fas fa-user-shield"></i></div>
                <div><span class="stat-card-value">{{ $campus->admins_count }}</span><span class="stat-card-label">Admins</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon yellow"><i class="fas fa-chalkboard-teacher"></i></div>
                <div><span class="stat-card-value">{{ $campus->teachers_count }}</span><span class="stat-card-label">Teachers</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon green"><i class="fas fa-user-graduate"></i></div>
                <div><span class="stat-card-value">{{ $campus->students_count }}</span><span class="stat-card-label">Students</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon teal"><i class="fa-layer-group fas"></i></div>
                <div><span class="stat-card-value">{{ $campus->classes_count }}</span><span class="stat-card-label">Classes</span></div>
            </div>
        </div>
    </div>

    <div class="col-4">
        <!-- Assigned Admins -->
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-user-shield"></i> Assigned Admins</div>
            </div>
            <div style="padding:0;">
                @forelse($campus->admins as $admin)
                <div style="padding:0.75rem 1.2rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:0.5rem;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="sidebar-user-avatar" style="width:32px; height:32px; font-size:0.8rem; flex-shrink:0;">
                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                        </div>
                        <div>
                            <strong style="font-size:0.88rem;">{{ $admin->name }}</strong>
                            <div style="font-size:0.77rem; color:var(--text-muted);">{{ $admin->email }}</div>
                        </div>
                    </div>
                    <form action="{{ route('super.campuses.remove-admin', [$campus, $admin]) }}" method="POST"  data-confirm="Remove {{ addslashes($admin->name) }} from this campus?')" data-type="danger" data-title="Delete">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-outline-danger btn btn-sm"
                                style="padding:0.25rem 0.6rem;">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>
                @empty
                <div style="padding:1.5rem; text-align:center; color:var(--text-muted); font-size:0.88rem;">
                    No admins assigned yet.
                </div>
                @endforelse
            </div>

            @if($availableAdmins->isNotEmpty())
            <div style="padding:1rem 1.2rem; border-top:1px solid var(--border); background:var(--light-bg);">
                <form action="{{ route('super.campuses.assign-admin', $campus) }}" method="POST">
                    @csrf
                    <label class="form-label" style="font-size:0.82rem;">Assign Admin</label>
                    <div class="d-flex gap-2">
                        <select name="user_id" class="form-select" style="flex:1;">
                            <option value="">-- Select Admin --</option>
                            @foreach($availableAdmins as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>

        <!-- Danger Zone -->
        <div class="card" style="border-color:rgba(220,53,69,0.25);">
            <div class="card-header" style="background:rgba(220,53,69,0.04);">
                <div class="card-header-title" style="color:var(--danger);">
                    <i class="fas fa-exclamation-triangle"></i> Danger Zone
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('super.campuses.destroy', $campus) }}" method="POST"  data-confirm="Permanently delete {{ addslashes($campus->name) }}?" data-type="danger" data-title="Delete">
                    @csrf @method('DELETE')
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.8rem;">
                        Cannot delete if campus has students or teachers.
                    </p>
                    <button type="submit" class="btn-block btn btn-danger"
                          >
                        <i class="fas fa-trash-alt"></i> Delete Campus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection