@extends('layouts.app')
@section('title', 'Classes')
@section('page-title', 'Classes')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Class Management</div>
        <div class="page-header-sub">Manage school classes and grade levels</div>
    </div>
</div>

<div class="row">
    <!-- Add Form -->
    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-plus-circle"></i> Add New Class</div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.classes.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-form">
                        <label class="form-label">Class Name <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}" placeholder="e.g. Class 1, Class 9-A">
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Grade Level <span style="color:var(--danger)">*</span></label>
                        <select name="grade_level" class="form-select {{ $errors->has('grade_level') ? 'is-invalid' : '' }}">
                            <option value="">-- Select Level --</option>
                            <option value="KG / Pre-School"      {{ old('grade_level') === 'KG / Pre-School' ? 'selected' : '' }}>KG / Pre-School</option>
                            <option value="Primary (1–5)"        {{ old('grade_level') === 'Primary (1–5)' ? 'selected' : '' }}>Primary (1–5)</option>
                            <option value="Middle (6–8)"         {{ old('grade_level') === 'Middle (6–8)' ? 'selected' : '' }}>Middle (6–8)</option>
                            <option value="Secondary (9–10)"     {{ old('grade_level') === 'Secondary (9–10)' ? 'selected' : '' }}>Secondary / Matric (9–10)</option>
                            <option value="Higher Secondary (11–12)" {{ old('grade_level') === 'Higher Secondary (11–12)' ? 'selected' : '' }}>Higher Secondary / F.A/F.Sc (11–12)</option>
                        </select>
                        @error('grade_level')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <button type="submit" class="btn-block btn btn-primary">
                        <i class="fas fa-save"></i> Add Class
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- List -->
    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fa-layer-group fas"></i> All Classes</div>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Class Name</th>
                            <th>Grade Level</th>
                            <th>Sections</th>
                            <th>Students</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $class)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $class->name }}</strong></td>
                            <td>{{ $class->grade_level }}</td>
                            <td><span class="badge badge-info">{{ $class->sections_count }}</span></td>
                            <td><span class="badge badge-primary">{{ $class->students_count }}</span></td>
                            <td>
                                <!-- Edit Modal Trigger -->
                                <button class="btn-outline-primary btn btn-sm"
                                        onclick="openEditClass({{ $class->id }}, '{{ addslashes($class->name) }}', '{{ addslashes($class->grade_level) }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-outline-danger btn btn-sm"
                                            onclick="return confirm('Delete {{ addslashes($class->name) }}?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:2rem;">No classes yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius); padding:1.8rem; width:100%; max-width:440px; box-shadow:var(--shadow-md);">
        <h3 style="font-family:var(--font-display); color:var(--primary); margin-bottom:1.2rem;">Edit Class</h3>
        <form id="edit-form" method="POST" novalidate>
            @csrf @method('PUT')
            <div class="mb-form">
                <label class="form-label">Class Name</label>
                <input type="text" name="name" id="edit-name" class="form-control">
            </div>
            <div class="mb-form">
                <label class="form-label">Grade Level</label>
                <select name="grade_level" id="edit-grade" class="form-select">
                    <option value="KG / Pre-School">KG / Pre-School</option>
                    <option value="Primary (1–5)">Primary (1–5)</option>
                    <option value="Middle (6–8)">Middle (6–8)</option>
                    <option value="Secondary (9–10)">Secondary / Matric (9–10)</option>
                    <option value="Higher Secondary (11–12)">Higher Secondary / F.A/F.Sc (11–12)</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                <button type="button" class="btn-outline-secondary btn" onclick="closeEditClass()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditClass(id, name, grade) {
    document.getElementById('edit-form').action = '/admin/classes/' + id;
    document.getElementById('edit-name').value  = name;
    document.getElementById('edit-grade').value = grade;
    document.getElementById('edit-modal').style.display = 'flex';
}
function closeEditClass() {
    document.getElementById('edit-modal').style.display = 'none';
}
</script>
@endsection