@extends('layouts.app')
@section('title', 'Edit Student')
@section('page-title', 'Edit Student')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">
            Edit — {{ $student->full_name }}
        </div>
        <div class="page-header-sub">
            Personal information only.
            To change class/section/roll, use
            <a href="{{ route('admin.enrollment.index') }}">Enrollment</a>.
        </div>
    </div>
    <a href="{{ route('admin.students.show', $student) }}"
       class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div style="max-width:700px;">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.students.update', $student) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  novalidate>
                @csrf @method('PUT')

                <div class="form-section-title">
                    <i class="fas fa-user"></i> Personal Information
                </div>

                <div class="row">
                    <div class="mb-form col-6">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name"
                               class="form-control {{ $errors->has('full_name')
                                   ? 'is-invalid' : '' }}"
                               value="{{ old('full_name', $student->full_name) }}">
                        @error('full_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-form col-3">
                        <label class="form-label">Gender *</label>
                        <select name="gender" class="form-select">
                            <option value="male"
                                    {{ old('gender', $student->gender) === 'male'
                                        ? 'selected' : '' }}>Male</option>
                            <option value="female"
                                    {{ old('gender', $student->gender) === 'female'
                                        ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="mb-form col-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth"
                               class="form-control"
                               value="{{ old('date_of_birth',
                                   $student->date_of_birth?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="mb-form col-6">
                        <label class="form-label">Father's Name *</label>
                        <input type="text" name="father_name"
                               class="form-control {{ $errors->has('father_name')
                                   ? 'is-invalid' : '' }}"
                               value="{{ old('father_name', $student->father_name) }}">
                        @error('father_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Mother's Name</label>
                        <input type="text" name="mother_name"
                               class="form-control"
                               value="{{ old('mother_name', $student->mother_name) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="mb-form col-4">
                        <label class="form-label">CNIC / B-Form</label>
                        <input type="text" name="cnic" class="form-control"
                               value="{{ old('cnic', $student->cnic) }}"
                               placeholder="42201-1234567-1">
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $student->phone) }}">
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Blood Group</label>
                        <select name="blood_group" class="form-select">
                            <option value="">— Select —</option>
                            @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                                <option value="{{ $bg }}"
                                        {{ old('blood_group', $student->blood_group)
                                            === $bg ? 'selected' : '' }}>
                                    {{ $bg }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="mb-form col-8">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control"
                               value="{{ old('address', $student->address) }}">
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Master Status</label>
                        <select name="status" class="form-select">
                            <option value="active"
                                    {{ old('status', $student->status)
                                        === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="left"
                                    {{ old('status', $student->status)
                                        === 'left' ? 'selected' : '' }}>
                                Left School
                            </option>
                            <option value="graduated"
                                    {{ old('status', $student->status)
                                        === 'graduated' ? 'selected' : '' }}>
                                Graduated
                            </option>
                        </select>
                    </div>
                </div>

                {{-- Photo --}}
                <div class="mb-form">
                    <label class="form-label">Photo</label>
                    @if($student->photo)
                    <div style="display:flex; align-items:center;
                                gap:1rem; margin-bottom:0.6rem;">
                        <img src="{{ $student->photo_url }}"
                             style="width:60px; height:60px; border-radius:50%;
                                    object-fit:cover; border:2px solid var(--border);">
                        <span style="font-size:0.82rem; color:var(--text-muted);">
                            Current photo
                        </span>
                    </div>
                    @endif
                    <input type="file" name="photo" class="form-control"
                           accept=".jpg,.jpeg,.png">
                    <small style="color:var(--text-muted); font-size:0.79rem;">
                        JPG or PNG, max 1MB
                    </small>
                </div>

                <div style="padding-top:1rem; border-top:1px solid var(--border);
                            display:flex; gap:0.8rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.students.show', $student) }}"
                       class="btn-outline-secondary btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Info note --}}
    <div style="margin-top:1rem; padding:0.9rem 1.2rem;
                background:rgba(37,99,168,0.06); border-radius:var(--radius-sm);
                font-size:0.83rem; color:var(--text-muted);
                border-left:3px solid var(--primary-light);">
        <i class="fas fa-info-circle" style="color:var(--primary);"></i>
        To update class, section, or roll number — use
        <a href="{{ route('admin.enrollment.index') }}"
           style="color:var(--primary); font-weight:600;">
            Enrollment Management
        </a>
        as these are year-specific.
    </div>
</div>
@endsection