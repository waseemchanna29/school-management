@extends('layouts.app')
@section('title', 'Add Teacher')
@section('page-title', 'Add Teacher')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Add New Teacher</div>
        <div class="page-header-sub">Fill in complete staff information</div>
    </div>
    <a href="{{ route('admin.teachers.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('admin.teachers.store') }}" method="POST" enctype="multipart/form-data" novalidate>
@csrf

<!-- ACCOUNT -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-user-circle"></i> Login Account</div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       value="{{ old('email') }}" placeholder="teacher@school.com">
                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                       placeholder="Min. 8 chars">
                @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Confirm Password *</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat">
            </div>
        </div>
    </div>
</div>

<!-- PERSONAL -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-id-card"></i> Personal Information</div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Full Name (as per CNIC) *</label>
                <input type="text" name="full_name" class="form-control {{ $errors->has('full_name') ? 'is-invalid' : '' }}"
                       value="{{ old('full_name') }}">
                @error('full_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Father's Name *</label>
                <input type="text" name="father_name" class="form-control {{ $errors->has('father_name') ? 'is-invalid' : '' }}"
                       value="{{ old('father_name') }}">
                @error('father_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">CNIC *</label>
                <input type="text" name="cnic" class="form-control {{ $errors->has('cnic') ? 'is-invalid' : '' }}"
                       value="{{ old('cnic') }}" placeholder="XXXXX-XXXXXXX-X">
                @error('cnic')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Phone *</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="03XX-XXXXXXX">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Emergency Phone</label>
                <input type="text" name="emergency_phone" class="form-control" value="{{ old('emergency_phone') }}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Gender *</label>
                <select name="gender" class="form-select {{ $errors->has('gender') ? 'is-invalid' : '' }}">
                    <option value="">-- Select --</option>
                    <option value="Male"   {{ old('gender') === 'Male'   ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other"  {{ old('gender') === 'Other'  ? 'selected' : '' }}>Other</option>
                </select>
                @error('gender')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Date of Birth *</label>
                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Religion</label>
                <select name="religion" class="form-select">
                    <option value="">-- Select --</option>
                    @foreach(['Islam','Christianity','Hinduism','Sikhism','Other'] as $r)
                        <option value="{{ $r }}" {{ old('religion') === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Nationality</label>
                <input type="text" name="nationality" class="form-control" value="{{ old('nationality', 'Pakistani') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Domicile (Province)</label>
                <select name="domicile" class="form-select">
                    <option value="">-- Select --</option>
                    @foreach(['Punjab','Sindh','KPK','Balochistan','Gilgit-Baltistan','AJK','ICT'] as $p)
                        <option value="{{ $p }}" {{ old('domicile') === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-8">
                <label class="form-label">Full Address *</label>
                <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="House #, Street, Area">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">City *</label>
                <input type="text" name="city" class="form-control" value="{{ old('city') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">District *</label>
                <input type="text" name="district" class="form-control" value="{{ old('district') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Province *</label>
                <select name="province" class="form-select">
                    <option value="">-- Select --</option>
                    @foreach(['Punjab','Sindh','KPK','Balochistan','Gilgit-Baltistan','AJK','ICT'] as $p)
                        <option value="{{ $p }}" {{ old('province') === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Photo</label>
                <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
                <small style="color:var(--text-muted); font-size:0.79rem;">JPG/PNG, max 1MB</small>
            </div>
        </div>
    </div>
</div>

<!-- PROFESSIONAL -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-briefcase"></i> Professional Information</div>
        <div class="row">
            <div class="mb-form col-4">
                <label class="form-label">Highest Qualification *</label>
                <select name="qualification" class="form-select {{ $errors->has('qualification') ? 'is-invalid' : '' }}">
                    <option value="">-- Select --</option>
                    @foreach(['Matric','Intermediate / F.A / F.Sc','B.A / B.Sc / B.Ed','M.A / M.Sc / M.Ed','MPhil','PhD','Other'] as $q)
                        <option value="{{ $q }}" {{ old('qualification') === $q ? 'selected' : '' }}>{{ $q }}</option>
                    @endforeach
                </select>
                @error('qualification')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Specialization / Subject</label>
                <input type="text" name="specialization" class="form-control" value="{{ old('specialization') }}"
                       placeholder="e.g. Mathematics, English">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Employment Type *</label>
                <select name="employment_type" class="form-select {{ $errors->has('employment_type') ? 'is-invalid' : '' }}">
                    @foreach(['Permanent','Contract','Visiting','Part-time'] as $t)
                        <option value="{{ $t }}" {{ old('employment_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Joining Date *</label>
                <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date') }}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Monthly Salary (PKR)</label>
                <input type="number" name="salary" class="form-control" value="{{ old('salary') }}" min="0">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}"
                       placeholder="e.g. HBL, UBL, MCB">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Bank Account No.</label>
                <input type="text" name="bank_account" class="form-control" value="{{ old('bank_account') }}">
            </div>
        </div>
    </div>
</div>

<!-- SUBJECTS -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-book"></i> Assign Subjects (optional)</div>
        <div class="row">
            @foreach($subjects as $subject)
            <div class="mb-form col-4" style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="subjects[]" id="sub_{{ $subject->id }}"
                       value="{{ $subject->id }}"
                       {{ in_array($subject->id, old('subjects', [])) ? 'checked' : '' }}
                       style="width:16px; height:16px; accent-color:var(--primary); cursor:pointer;">
                <label for="sub_{{ $subject->id }}" style="cursor:pointer; font-size:0.88rem;">
                    <strong>{{ $subject->name }}</strong>
                    <span style="color:var(--text-muted); font-size:0.8rem;">
                        ({{ $subject->schoolClass->name ?? '' }})
                    </span>
                </label>
            </div>
            @endforeach
            @if($subjects->isEmpty())
                <div class="col-12">
                    <p style="color:var(--text-muted); font-size:0.88rem;">
                        No subjects created yet. <a href="{{ route('admin.subjects.index') }}">Add subjects first.</a>
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<div style="display:flex; gap:0.8rem; padding-top:0.5rem;">
    <button type="submit" class="btn btn-primary btn-lg">
        <i class="fas fa-save"></i> Save Teacher
    </button>
    <a href="{{ route('admin.teachers.index') }}" class="btn-outline-secondary btn btn-lg">Cancel</a>
</div>
</form>
@endsection