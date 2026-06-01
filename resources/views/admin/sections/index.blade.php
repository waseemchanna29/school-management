@extends('layouts.app')
@section('title', 'Sections')
@section('page-title', 'Sections')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Section Management</div>
        <div class="page-header-sub">Manage sections and assign class teachers</div>
    </div>
</div>

<div class="row">
    {{-- Add Section --}}
    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-plus-circle"></i> Add Section</div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sections.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-form">
                        <label class="form-label">Class *</label>
                        <select name="class_id" class="form-select {{ $errors->has('class_id') ? 'is-invalid' : '' }}">
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Section Name *</label>
                        <input type="text" name="name"
                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}" placeholder="e.g. A, B, Blue">
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <button type="submit" class="btn-block btn btn-primary">
                        <i class="fas fa-save"></i> Add Section
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Sections List --}}
    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-sitemap"></i> All Sections</div>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Students</th>
                            <th>Class Teacher</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sections as $section)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $section->schoolClass->name ?? '—' }}</strong></td>
                            <td>Section {{ $section->name }}</td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $section->students_count }}
                                </span>
                            </td>
                            <td>
                                @if($section->classTeacher)
                                    <div style="display:flex; align-items:center; gap:0.5rem;">
                                        <div class="sidebar-user-avatar"
                                             style="width:28px; height:28px; font-size:0.75rem; flex-shrink:0;">
                                            {{ strtoupper(substr($section->classTeacher->full_name, 0, 1)) }}
                                        </div>
                                        <span style="font-size:0.85rem; font-weight:600;">
                                            {{ $section->classTeacher->full_name }}
                                        </span>
                                    </div>
                                @else
                                    <span style="color:var(--text-muted); font-size:0.83rem;">
                                        Not assigned
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    {{-- Assign Teacher Button --}}
                                    <button class="btn-outline-primary btn btn-sm"
                                            onclick="openAssignTeacher(
                                                {{ $section->id }},
                                                '{{ addslashes($section->schoolClass->name ?? '') }} – {{ $section->name }}',
                                                {{ $section->class_teacher_id ?? 'null' }}
                                            )"
                                            title="Assign Class Teacher">
                                        <i class="fas fa-user-tie"></i>
                                    </button>

                                    <form action="{{ route('admin.sections.destroy', $section) }}"
                                          method="POST"
                                          data-confirm="Delete section {{ $section->name }}?"
                                          data-type="danger"
                                          data-title="Delete Section">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-outline-danger btn btn-sm">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center; color:var(--text-muted); padding:2rem;">
                                No sections yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Assign Class Teacher Modal --}}
<div id="assign-teacher-modal"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
            z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius); padding:1.8rem;
                width:100%; max-width:440px; box-shadow:var(--shadow-md);">
        <h3 style="font-family:var(--font-display); color:var(--primary); margin-bottom:0.4rem;">
            Assign Class Teacher
        </h3>
        <p id="assign-section-label"
           style="color:var(--text-muted); font-size:0.88rem; margin-bottom:1.2rem;"></p>

        <form id="assign-teacher-form" method="POST" novalidate>
            @csrf
            <div class="mb-form">
                <label class="form-label">Class Teacher</label>
                <select name="class_teacher_id" id="teacher-select" class="form-select">
                    <option value="">-- Remove / No Teacher --</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">
                            {{ $teacher->full_name }}
                            ({{ $teacher->specialization ?? $teacher->qualification }})
                        </option>
                    @endforeach
                </select>
                <small style="color:var(--text-muted); font-size:0.79rem; margin-top:4px; display:block;">
                    A teacher can only be class teacher of one section at a time.
                </small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Assign
                </button>
                <button type="button" class="btn-outline-secondary btn"
                        onclick="document.getElementById('assign-teacher-modal').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignTeacher(sectionId, sectionLabel, currentTeacherId) {
    const baseUrl = '{{ route("admin.sections.assign-teacher", ["section" => "__ID__"]) }}';
    document.getElementById('assign-teacher-form').action = baseUrl.replace('__ID__', sectionId);
    document.getElementById('assign-section-label').textContent = sectionLabel;
    document.getElementById('teacher-select').value = currentTeacherId || '';
    document.getElementById('assign-teacher-modal').style.display = 'flex';
}
</script>
@endsection