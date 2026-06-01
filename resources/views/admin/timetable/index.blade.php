@extends('layouts.app')
@section('title', 'Timetables')
@section('page-title', 'Timetables')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Timetables</div>
        <div class="page-header-sub">Class and section weekly schedules</div>
    </div>
    <a href="{{ route('admin.timetable.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> New Timetable
    </a>
</div>

<form method="GET">
    <div class="filter-bar">
        <div>
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}"
                            {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Section</label>
            <select name="section_id" class="form-select">
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}"
                            {{ request('section_id') == $section->id ? 'selected' : '' }}>
                        {{ $section->schoolClass->name ?? '' }} – {{ $section->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Year</label>
            <select name="academic_year" class="form-select">
                <option value="">All Years</option>
                @foreach($years as $year)
                    <option value="{{ $year }}"
                            {{ request('academic_year') === $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
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
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.timetable.index') }}" class="btn-outline-secondary btn">Clear</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Year</th>
                    <th>Days</th>
                    <th>Periods</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($timetables as $tt)
                <tr>
                    <td><strong>{{ $tt->name }}</strong></td>
                    <td>{{ $tt->schoolClass->name ?? '—' }}</td>
                    <td>
                        <span class="badge badge-info">{{ $tt->section->name ?? '—' }}</span>
                    </td>
                    <td>{{ $tt->academic_year }}</td>
                    <td>
                        <div style="display:flex; gap:3px; flex-wrap:wrap;">
                            @foreach($tt->days as $d)
                                <span class="badge badge-primary"
                                      style="font-size:0.7rem;">{{ $d }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-info">
                            {{ $tt->periods_count ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $tt->is_active ? 'badge-approved' : 'badge-rejected' }}">
                            {{ $tt->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.timetable.show', $tt) }}"
                               class="btn-outline-primary btn btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.timetable.edit', $tt) }}"
                               class="btn-outline-primary btn btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.timetable.toggle', $tt) }}"
                                  method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-outline-secondary btn btn-sm"
                                        title="{{ $tt->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $tt->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.timetable.destroy', $tt) }}"
                                  method="POST" style="display:inline;" data-confirm="Delete this timetable?" data-type="danger" data-title="Delete">
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
                <tr>
                    <td colspan="8"
                        style="text-align:center; color:var(--text-muted); padding:3rem;">
                        <i class="fas fa-calendar-alt"
                           style="font-size:3rem; display:block; margin-bottom:1rem; color:var(--border);"></i>
                        No timetables yet.
                        <a href="{{ route('admin.timetable.create') }}">Create your first one.</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($timetables->hasPages())
    <div style="padding:1rem 1.4rem; border-top:1px solid var(--border);">
        {{ $timetables->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection

@php
    // Eager load period count for listing
    if (method_exists($timetables->getCollection()->first() ?? new stdClass, 'loadCount')) {
        $timetables->getCollection()->loadCount('periods');
    }
@endphp