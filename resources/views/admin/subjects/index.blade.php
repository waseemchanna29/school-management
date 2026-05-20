@extends('layouts.app')
@section('title', 'Subjects')
@section('page-title', 'Subjects')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Subject Management</div>
        <div class="page-header-sub">Add subjects per class</div>
    </div>
</div>

<div class="row">
    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-plus-circle"></i> Add Subject</div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.subjects.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-form">
                        <label class="form-label">Subject Name <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}" placeholder="e.g. Mathematics, Urdu, Islamiat">
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Subject Code <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="code" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                               value="{{ old('code') }}" placeholder="e.g. MATH-9, URD-6">
                        @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Assign to Class <span style="color:var(--danger)">*</span></label>
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
                    <button type="submit" class="btn-block btn btn-primary">
                        <i class="fas fa-save"></i> Add Subject
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-book"></i> All Subjects</div>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr><th>#</th><th>Subject</th><th>Code</th><th>Class</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $subject->name }}</strong></td>
                            <td><code style="font-size:0.82rem; background:var(--light-bg); padding:2px 8px; border-radius:4px;">{{ $subject->code }}</code></td>
                            <td>{{ $subject->schoolClass->name ?? '—' }}</td>
                            <td>
                                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-outline-danger btn btn-sm"
                                            onclick="return confirm('Delete subject {{ addslashes($subject->name) }}?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center; color:var(--text-muted); padding:2rem;">No subjects yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection