@extends('layouts.app')
@section('title', 'Edit Teacher')
@section('page-title', 'Edit Teacher')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Edit Teacher</div>
        <div class="page-header-sub">{{ $teacher->full_name }} &bull; <code>{{ $teacher->employee_code }}</code></div>
    </div>
    <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('admin.teachers.update', $teacher) }}" method="POST" enctype="multipart/form-data" novalidate>
@csrf @method('PUT')

<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-id-card"></i> Personal Information</div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $teacher->full_name) }}">
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Father's Name *</label>
                <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $teacher->father_name) }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">CNIC *</label>
                <input type="text" name="cnic" class="form-control" value="{{ old('cnic', $teacher->cnic) }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Phone *</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $teacher->phone) }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Emergency Phone</label>
                <input type="text" name="emergency_phone" class="form-control" value="{{ old('emergency_phone', $teacher->emergency_phone) }}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Gender *</label>
                <select name="gender" class="form-select">
                    @foreach(['Male','Female','Other'] as $g)
                        <option value="{{ $g }}" {{ old('gender', $teacher->gender) === $g ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Date of Birth *</label>
                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $teacher->date_of_birth->format('Y-m-d')) }}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Religion</label>
                <select name="religion" class="form-select">
                    <option value="">-- None --</option>
                    @foreach(['Islam','Christianity','Hinduism','Sikhism','Other'] as $r)
                        <option value="{{ $r }}" {{ old('religion', $teacher->religion) === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Domicile</label>
                <select name="domicile" class="form-select">
                    <option value="">-- None --</option>
                    @foreach(['Punjab','Sindh','KPK','Balochistan','Gilgit-Baltistan','AJK','ICT'] as $p)
                        <option value="{{ $p }}" {{ old('domicile', $teacher->domicile) === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-8">
                <label class="form-label">Address *</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $teacher->address) }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">City *</label>
                <input type="text" name="city" class="form-control" value="{{ old('city', $teacher->city) }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">District *</label>
                <input type="text" name="district" class="form-control" value="{{ old('district', $teacher->district) }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Province *</label>
                <select name="province" class="form-select">
                    @foreach(['Punjab','Sindh','KPK','Balochistan','Gilgit-Baltistan','AJK','ICT'] as $p)
                        <option value="{{ $p }}" {{ old('province', $teacher->province) === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Photo</label>
                @if($teacher->photo)
                    <div style="margin-bottom:6px;"><img src="{{ Storage::url($teacher->photo) }}" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid var(--border);"></div>
                @endif
                <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
            </div>
        </div>
    </div>
</div>

<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-briefcase"></i> Professional Information</div>
        <div class="row">
            <div class="mb-form col-4">
                <label class="form-label">Qualification *</label>
                <select name="qualification" class="form-select">
                    @foreach(['Matric','Intermediate / F.A / F.Sc','B.A / B.Sc / B.Ed','M.A / M.Sc / M.Ed','MPhil','PhD','Other'] as $q)
                        <option value="{{ $q }}" {{ old('qualification', $teacher->qualification) === $q ? 'selected' : '' }}>{{ $q }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Specialization</label>
                <input type="text" name="specialization" class="form-control" value="{{ old('specialization', $teacher->specialization) }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Employment Type *</label>
                <select name="employment_type" class="form-select">
                    @foreach(['Permanent','Contract','Visiting','Part-time'] as $t)
                        <option value="{{ $t }}" {{ old('employment_type', $teacher->employment_type) === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Joining Date *</label>
                <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $teacher->joining_date->format('Y-m-d')) }}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Salary (PKR)</label>
                <input type="number" name="salary" class="form-control" value="{{ old('salary', $teacher->salary) }}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $teacher->bank_name) }}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Bank Account</label>
                <input type="text" name="bank_account" class="form-control" value="{{ old('bank_account', $teacher->bank_account) }}">
            </div>
        </div>
    </div>
</div>

<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-book"></i> Assign Subjects</div>
        <div class="row">
            @foreach($subjects as $subject)
            <div class="mb-form col-4" style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="subjects[]" id="sub_{{ $subject->id }}" value="{{ $subject->id }}"
                       {{ in_array($subject->id, old('subjects', $teacher->subjects->pluck('id')->toArray())) ? 'checked' : '' }}
                       style="width:16px; height:16px; accent-color:var(--primary); cursor:pointer;">
                <label for="sub_{{ $subject->id }}" style="cursor:pointer; font-size:0.88rem;">
                    <strong>{{ $subject->name }}</strong>
                    <span style="color:var(--text-muted); font-size:0.79rem;">({{ $subject->schoolClass->name ?? '' }})</span>
                </label>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div style="display:flex; gap:0.8rem; padding-top:0.5rem;">
    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Changes</button>
    <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn-outline-secondary btn btn-lg">Cancel</a>
</div>
</form>
@endsection