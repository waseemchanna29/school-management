@extends('layouts.app')
@section('title', 'Super Admin Dashboard')
@section('page-title', 'Super Admin Dashboard')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">System Overview</div>
        <div class="page-header-sub">Full network view across all campuses</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('super.campuses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add Campus
        </a>
        <a href="{{ route('super.admins.create') }}" class="btn-outline-primary btn">
            <i class="fas fa-user-plus"></i> Add Admin
        </a>
    </div>
</div>

<!-- Stats -->
<div class="stat-cards-grid">
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="fas fa-building"></i></div>
        <div><span class="stat-card-value">{{ $stats['campuses'] }}</span><span class="stat-card-label">Total Campuses</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="fas fa-check-circle"></i></div>
        <div><span class="stat-card-value">{{ $stats['active_campuses'] }}</span><span class="stat-card-label">Active Campuses</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon yellow"><i class="fas fa-user-shield"></i></div>
        <div><span class="stat-card-value">{{ $stats['admins'] }}</span><span class="stat-card-label">Admins</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon teal"><i class="fas fa-chalkboard-teacher"></i></div>
        <div><span class="stat-card-value">{{ $stats['teachers'] }}</span><span class="stat-card-label">Teachers (All)</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon purple"><i class="fas fa-user-graduate"></i></div>
        <div><span class="stat-card-value">{{ $stats['students'] }}</span><span class="stat-card-label">Students (All)</span></div>
    </div>
</div>

<!-- Campus Cards -->
<div class="page-header" style="margin-bottom:1.2rem;">
    <div>
        <div style="font-family:var(--font-display); font-size:1.2rem; color:var(--primary);">Campus Network</div>
        <div style="color:var(--text-muted); font-size:0.87rem;">Overview of all registered campuses</div>
    </div>
    <a href="{{ route('super.campuses.index') }}" class="btn-outline-primary btn btn-sm">View All</a>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1.2rem;">
    @forelse($campuses as $campus)
    <div class="campus-stat-card">
        <div class="campus-stat-card-top">
            <div>
                <div class="campus-stat-card-name">{{ $campus->name }}</div>
                <div class="campus-stat-card-meta">
                    <i class="fas fa-map-marker-alt" style="color:var(--accent);"></i>
                    {{ $campus->city }}, {{ $campus->province }}
                </div>
                <div class="campus-stat-card-meta" style="margin-top:3px;">
                    <code style="font-size:0.75rem;">{{ $campus->code }}</code>
                </div>
            </div>
            <span class="badge {{ $campus->is_active ? 'badge-approved' : 'badge-rejected' }}">
                {{ $campus->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <div class="campus-stat-card-body">
            <div class="campus-mini-stat">
                <span class="campus-mini-stat-value">{{ $campus->admins_count }}</span>
                <span class="campus-mini-stat-label">Admins</span>
            </div>
            <div class="campus-mini-stat">
                <span class="campus-mini-stat-value">{{ $campus->teachers_count }}</span>
                <span class="campus-mini-stat-label">Teachers</span>
            </div>
            <div class="campus-mini-stat">
                <span class="campus-mini-stat-value">{{ $campus->students_count }}</span>
                <span class="campus-mini-stat-label">Students</span>
            </div>
        </div>

        <div class="campus-stat-card-footer">
            <a href="{{ route('super.campuses.show', $campus) }}" class="btn-outline-primary btn btn-sm">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="{{ route('super.campuses.edit', $campus) }}" class="btn-outline-primary btn btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>
    @empty
    <div class="card" style="grid-column:1/-1;">
        <div class="card-body" style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fas fa-building" style="font-size:3rem; display:block; margin-bottom:1rem;"></i>
            No campuses yet. <a href="{{ route('super.campuses.create') }}">Create your first campus.</a>
        </div>
    </div>
    @endforelse
</div>
@endsection