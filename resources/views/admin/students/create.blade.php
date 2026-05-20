@extends('layouts.app')
@section('title', 'Enroll Student')
@section('page-title', 'Enroll Student')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Enroll New Student</div>
        <div class="page-header-sub">Complete all sections to register the student</div>
    </div>
    <a href="{{ route('admin.students.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data" novalidate>
@csrf

<!-- ACCOUNT -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-user-circle"></i> Login Account</div>
        <div class="row">
            <div class="mb-form col-4">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       value="{{ old('email') }}" placeholder="student@school.com">
                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" placeholder="Min. 8 chars">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Confirm Password *</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
        </div>
    </div>
</div>

<!-- PERSONAL -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-id-card"></i> Student Personal Information</div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Full Name (as per B-Form / CNIC) *</label>
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
            <div class="mb-form col-6">
                <label class="form-label">Mother's Name *</label>
                <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name') }}">
            </div>
            <div class="mb-form col-6">
                <label class="form-label">B-Form / CNIC No.</label>
                <input type="text" name="cnic" class="form-control" value="{{ old('cnic') }}" placeholder="XXXXX-XXXXXXX-X">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Gender *</label>
                <select name="gender" class="form-select {{ $errors->has('gender') ? 'is-invalid' : '' }}">
                    <option value="">-- Select --</option>
                    @foreach(['Male','Female','Other'] as $g)
                        <option value="{{ $g }}" {{ old('gender') === $g ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                </select>
                @error('gender')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Date of Birth *</label>
                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Blood Group</label>
                <select name="blood_group" class="form-select">
                    <option value="">-- Select --</option>
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                        <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                    @endforeach
                </select>
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
            <div class="mb-form col-4">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="mb-form col-8">
                <label class="form-label">Full Address *</label>
                <input type="text" name="address" class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                       value="{{ old('address') }}" placeholder="House #, Street, Mohallah, Area">
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

<!-- ADMISSION -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-school"></i> Admission Details</div>
        <div class="row">
            <div class="mb-form col-4">
                <label class="form-label">Class *</label>
                <select name="class_id" id="class_id" class="form-select {{ $errors->has('class_id') ? 'is-invalid' : '' }}">
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                @error('class_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Section *</label>
                <select name="section_id" id="section_id" class="form-select {{ $errors->has('section_id') ? 'is-invalid' : '' }}">
                    <option value="">-- Select Section --</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}"
                                data-class="{{ $section->class_id }}"
                                {{ old('section_id') == $section->id ? 'selected' : '' }}>
                            {{ $section->schoolClass->name }} – {{ $section->name }}
                        </option>
                    @endforeach
                </select>
                @error('section_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Admission Date *</label>
                <input type="date" name="admission_date" class="form-control" value="{{ old('admission_date', date('Y-m-d')) }}">
            </div>
            <div class="mb-form col-12">
                <label class="form-label">Previous School (if any)</label>
                <input type="text" name="previous_school" class="form-control" value="{{ old('previous_school') }}"
                       placeholder="Name of last attended school">
            </div>
        </div>
    </div>
</div>

<!-- PARENTS -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-users"></i> Parent / Guardian Information</div>

        <div style="font-weight:700; color:var(--primary); font-size:0.88rem; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:1px;">
            Father's Details
        </div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Father's Full Name *</label>
                <input type="text" name="father_full_name" class="form-control {{ $errors->has('father_full_name') ? 'is-invalid' : '' }}"
                       value="{{ old('father_full_name') }}">
                @error('father_full_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Father's CNIC</label>
                <input type="text" name="father_cnic" class="form-control" value="{{ old('father_cnic') }}" placeholder="XXXXX-XXXXXXX-X">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Father's Phone *</label>
                <input type="text" name="father_phone" class="form-control {{ $errors->has('father_phone') ? 'is-invalid' : '' }}"
                       value="{{ old('father_phone') }}">
                @error('father_phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Occupation</label>
                <input type="text" name="father_occupation" class="form-control" value="{{ old('father_occupation') }}"
                       placeholder="e.g. Government Employee, Business">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Qualification</label>
                <select name="father_qualification" class="form-select">
                    <option value="">-- Select --</option>
                    @foreach(['Illiterate','Primary','Middle','Matric','Intermediate','Graduate','Post-Graduate','Other'] as $q)
                        <option value="{{ $q }}" {{ old('father_qualification') === $q ? 'selected' : '' }}>{{ $q }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Monthly Income (PKR)</label>
                <input type="number" name="father_income" class="form-control" value="{{ old('father_income') }}">
            </div>
        </div>

        <div style="font-weight:700; color:var(--primary); font-size:0.88rem; margin: 0.5rem 0 0.8rem; text-transform:uppercase; letter-spacing:1px; padding-top:0.8rem; border-top:1px dashed var(--border);">
            Mother's Details
        </div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Mother's Full Name *</label>
                <input type="text" name="mother_full_name" class="form-control {{ $errors->has('mother_full_name') ? 'is-invalid' : '' }}"
                       value="{{ old('mother_full_name') }}">
                @error('mother_full_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Mother's CNIC</label>
                <input type="text" name="mother_cnic" class="form-control" value="{{ old('mother_cnic') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Mother's Phone</label>
                <input type="text" name="mother_phone" class="form-control" value="{{ old('mother_phone') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Occupation</label>
                <input type="text" name="mother_occupation" class="form-control" value="{{ old('mother_occupation') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Qualification</label>
                <select name="mother_qualification" class="form-select">
                    <option value="">-- Select --</option>
                    @foreach(['Illiterate','Primary','Middle','Matric','Intermediate','Graduate','Post-Graduate','Other'] as $q)
                        <option value="{{ $q }}" {{ old('mother_qualification') === $q ? 'selected' : '' }}>{{ $q }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="font-weight:700; color:var(--primary); font-size:0.88rem; margin: 0.5rem 0 0.8rem; text-transform:uppercase; letter-spacing:1px; padding-top:0.8rem; border-top:1px dashed var(--border);">
            Guardian (if different from parents)
        </div>
        <div class="row">
            <div class="mb-form col-4">
                <label class="form-label">Guardian Name</label>
                <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Relation</label>
                <input type="text" name="guardian_relation" class="form-control" value="{{ old('guardian_relation') }}"
                       placeholder="e.g. Uncle, Brother">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Guardian Phone</label>
                <input type="text" name="guardian_phone" class="form-control" value="{{ old('guardian_phone') }}">
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Guardian CNIC</label>
                <input type="text" name="guardian_cnic" class="form-control" value="{{ old('guardian_cnic') }}">
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Guardian Address</label>
                <input type="text" name="guardian_address" class="form-control" value="{{ old('guardian_address') }}">
            </div>
        </div>
    </div>
</div>

<!-- EDUCATION RECORDS -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-graduation-cap"></i> Previous Education Records <span style="font-size:0.82rem; font-weight:400; color:var(--text-muted);">(optional)</span></div>

        <div id="education-container"></div>
        <button type="button" class="btn-add-repeater" id="add-education">
            <i class="fas fa-plus-circle"></i> Add Education Record
        </button>
    </div>
</div>

<div style="display:flex; gap:0.8rem; padding-top:0.5rem;">
    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-user-plus"></i> Enroll Student</button>
    <a href="{{ route('admin.students.index') }}" class="btn-outline-secondary btn btn-lg">Cancel</a>
</div>
</form>

<script>
// Section filter by class
document.getElementById('class_id').addEventListener('change', function () {
    const classId = this.value;
    const sectionSel = document.getElementById('section_id');
    Array.from(sectionSel.options).forEach(opt => {
        if (opt.value === '') return;
        opt.style.display = (!classId || opt.dataset.class === classId) ? '' : 'none';
    });
    sectionSel.value = '';
});

// Education repeater
let eduIdx = 0;
document.getElementById('add-education').addEventListener('click', function () {
    const idx = eduIdx++;
    const html = `
    <div class="repeater-item" id="edu-${idx}">
        <div class="repeater-item-header">
            <span class="repeater-item-label"><i class="fas fa-graduation-cap"></i> Record #${idx + 1}</span>
            <button type="button" class="btn-remove-repeater" onclick="document.getElementById('edu-${idx}').remove()">
                <i class="fas fa-trash-alt"></i> Remove
            </button>
        </div>
        <div class="row">
            <div class="mb-form col-4">
                <label class="form-label">Level *</label>
                <select name="education[${idx}][level]" class="form-select">
                    <option value="">-- Select --</option>
                    <option value="KG / Nursery">KG / Nursery</option>
                    <option value="Primary (1–5)">Primary (1–5)</option>
                    <option value="Middle (6–8)">Middle (6–8)</option>
                    <option value="Matric (SSC)">Matric (SSC)</option>
                    <option value="Intermediate (HSSC)">Intermediate (HSSC)</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Institution Name *</label>
                <input type="text" name="education[${idx}][institution_name]" class="form-control" placeholder="School / College Name">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Board / University</label>
                <input type="text" name="education[${idx}][board_university]" class="form-control" placeholder="e.g. BISE Lahore, FBISE">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Passing Year *</label>
                <input type="number" name="education[${idx}][passing_year]" class="form-control" placeholder="${new Date().getFullYear()}" min="1990" max="${new Date().getFullYear()}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Total Marks</label>
                <input type="text" name="education[${idx}][total_marks]" class="form-control" placeholder="e.g. 1100">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Obtained Marks</label>
                <input type="text" name="education[${idx}][obtained_marks]" class="form-control" placeholder="e.g. 980">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Grade / Division</label>
                <select name="education[${idx}][grade_division]" class="form-select">
                    <option value="">-- Select --</option>
                    <option value="A+">A+ (Distinction)</option>
                    <option value="A">A (First Division)</option>
                    <option value="B">B (Second Division)</option>
                    <option value="C">C (Third Division)</option>
                    <option value="Pass">Pass</option>
                    <option value="Fail">Fail</option>
                </select>
            </div>
        </div>
    </div>`;
    document.getElementById('education-container').insertAdjacentHTML('beforeend', html);
});
</script>
@endsection