@extends('layouts.app')
@section('title', 'Fee Structures')
@section('page-title', 'Fee Structures')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Fee Structures</div>
        <div class="page-header-sub">Named fee templates per class — assign to students to set their fee lines.</div>
    </div>
    <a href="{{ route('admin.fee.structures.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> New Structure
    </a>
</div>

<!-- Filters -->
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
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="monthly"  {{ request('type') === 'monthly'  ? 'selected' : '' }}>Monthly</option>
                <option value="yearly"   {{ request('type') === 'yearly'   ? 'selected' : '' }}>Yearly</option>
                <option value="one_time" {{ request('type') === 'one_time' ? 'selected' : '' }}>One-Time</option>
            </select>
        </div>
        <div>
            <label class="form-label">Academic Year</label>
            <select name="academic_year" class="form-select">
                <option value="">All Years</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ request('academic_year') === $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.fee.structures.index') }}" class="btn-outline-secondary btn">Clear</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Structure Name</th>
                    <th>Type</th>
                    <th>Class</th>
                    <th>Year</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($structures as $structure)
                <tr>
                    <td>
                        <strong>{{ $structure->name }}</strong>
                        @if($structure->notes)
                            <div style="font-size:0.77rem; color:var(--text-muted);">{{ $structure->notes }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $structure->type_badge_class }}">
                            {{ $structure->type_label }}
                        </span>
                    </td>
                    <td>{{ $structure->schoolClass->name ?? '—' }}</td>
                    <td>{{ $structure->academic_year }}</td>
                    <td>
                        <span class="badge badge-info">{{ $structure->items->count() }} items</span>
                    </td>
                    <td>
                        <strong>PKR {{ number_format($structure->total, 2) }}</strong>
                    </td>
                    <td>
                        <span class="badge {{ $structure->is_active ? 'badge-approved' : 'badge-rejected' }}">
                            {{ $structure->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.fee.structures.show', $structure) }}"
                               class="btn-outline-primary btn btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.fee.structures.edit', $structure) }}"
                               class="btn-outline-primary btn btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.fee.structures.revise', $structure) }}"
                                  method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm"
                                        title="Copy to next academic year"
                                        onclick="return confirm('Create a next-year copy of this structure?')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.fee.structures.destroy', $structure) }}"
                                  method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-outline-danger btn btn-sm"
                                        onclick="return confirm('Delete \'{{ addslashes($structure->name) }}\'?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                        <i class="fas fa-file-invoice-dollar"
                           style="font-size:2.5rem; display:block; margin-bottom:0.8rem;"></i>
                        No fee structures yet.
                        <a href="{{ route('admin.fee.structures.create') }}">Create your first one.</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($structures->hasPages())
    <div style="padding:1rem 1.4rem; border-top:1px solid var(--border);">
        {{ $structures->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection