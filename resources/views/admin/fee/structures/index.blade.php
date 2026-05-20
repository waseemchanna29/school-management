@extends('layouts.app')
@section('title', 'Fee Structures')
@section('page-title', 'Fee Structures')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Fee Structures</div>
        <div class="page-header-sub">Class-wise fee templates per academic year</div>
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
            <label class="form-label">Academic Year</label>
            <select name="academic_year" class="form-select">
                <option value="">All Years</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ request('academic_year') === $year ? 'selected' : '' }}>{{ $year }}</option>
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
                    <th>Class</th>
                    <th>Academic Year</th>
                    <th>Fee Items</th>
                    <th>Monthly Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($structures as $structure)
                <tr>
                    <td><strong>{{ $structure->schoolClass->name ?? '—' }}</strong></td>
                    <td>
                        <span class="badge badge-info">{{ $structure->academic_year }}</span>
                    </td>
                    <td>{{ $structure->items->count() }} items</td>
                    <td>
                        <strong>PKR {{ number_format($structure->total_monthly, 2) }}</strong>
                        <div style="font-size:0.77rem; color:var(--text-muted);">monthly recurring</div>
                    </td>
                    <td>
                        <span class="badge {{ $structure->is_active ? 'badge-approved' : 'badge-rejected' }}">
                            {{ $structure->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.fee.structures.show', $structure) }}" class="btn-outline-primary btn btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.fee.structures.edit', $structure) }}" class="btn-outline-primary btn btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.fee.structures.revise', $structure) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm"
                                        onclick="return confirm('Create a new year copy of this structure for revision?')"
                                        title="Revise for next year">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.fee.structures.destroy', $structure) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-outline-danger btn btn-sm"
                                        onclick="return confirm('Delete this fee structure?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                    No fee structures yet. <a href="{{ route('admin.fee.structures.create') }}">Create one.</a>
                </td></tr>
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