@extends('layouts.app')
@section('title', 'Sections')
@section('page-title', 'Sections')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Section Management</div>
        <div class="page-header-sub">Manage sections within each class</div>
    </div>
</div>

<div class="row">
    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-plus-circle"></i> Add Section</div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sections.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-form">
                        <label class="form-label">Class <span style="color:var(--danger)">*</span></label>
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
                        <label class="form-label">Section Name <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}" placeholder="e.g. A, B, Blue, Red">
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <button type="submit" class="btn-block btn btn-primary">
                        <i class="fas fa-save"></i> Add Section
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-sitemap"></i> All Sections</div>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr><th>#</th><th>Class</th><th>Section</th><th>Students</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($sections as $section)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $section->schoolClass->name ?? '—' }}</strong></td>
                            <td>Section {{ $section->name }}</td>
                            <td><span class="badge badge-primary">{{ $section->students_count }}</span></td>
                            <td>
                                <form action="{{ route('admin.sections.destroy', $section) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-outline-danger btn btn-sm"
                                            onclick="return confirm('Delete section {{ $section->name }}?')">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center; color:var(--text-muted); padding:2rem;">No sections yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection