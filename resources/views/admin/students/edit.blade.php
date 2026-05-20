@extends('layouts.app')
@section('title', 'Edit Student')
@section('page-title', 'Edit Student')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Edit Student</div>
        <div class="page-header-sub">{{ $student->full_name }} &bull; <code>{{ $student->roll_number }}</code></div>
    </div>
    <a href="{{ route('admin.students.show', $student) }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('admin.students.update', $student) }}" method="POST" enctype="multipart/form-data" novalidate>
@csrf @method('PUT')

<!-- Personal -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-id-card"></i> Personal Information</div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $student->full_name) }}">
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Father's Name *</label>
                <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $student->father_name) }}">
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Mother's Name *</label>
                <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $student->mother_name) }}">
            </div>
            <div class="mb-form col-6">
                <label class="form-label">B-Form / CNIC</label>
                <input type="text" name="cnic" class="form-control" value="{{ old('cnic', $student->cnic) }}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Gender *</label>
                <select name="gender" class="form-select">
                    @foreach(['Male','Female','Other'] as $g)
                        <option value="{{ $g }}" {{ old('gender', $student->gender) === $g ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Date of Birth *</label>
                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $student->date_of_birth->format('Y-m-d')) }}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Blood Group</label>
                <select name="blood_group" class="form-select">
                    <option value="">-- None --</option>
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                        <option value="{{ $bg }}" {{ old('blood_group', $student->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}">
            </div>
            <div class="mb-form col-8">
                <label class="form-label">Address *</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $student->address) }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">City *</label>
                <input type="text" name="city" class="form-control" value="{{ old('city', $student->city) }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">District *</label>
                <input type="text" name="district" class="form-control" value="{{ old('district', $student->district) }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Province *</label>
                <select name="province" class="form-select">
                    @foreach(['Punjab','Sindh','KPK','Balochistan','Gilgit-Baltistan','AJK','ICT'] as $p)
                        <option value="{{ $p }}" {{ old('province', $student->province) === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Photo</label>
                @if($student->photo)
                    <div style="margin-bottom:6px;"><img src="{{ Storage::url($student->photo) }}" style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid var(--border);"></div>
                @endif
                <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
            </div>
        </div>
    </div>
</div>

<!-- Admission -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-school"></i> Admission Details</div>
        <div class="row">
            <div class="mb-form col-3">
                <label class="form-label">Class *</label>
                <select name="class_id" class="form-select">
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Section *</label>
                <select name="section_id" class="form-select">
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" {{ old('section_id', $student->section_id) == $section->id ? 'selected' : '' }}>
                            {{ $section->schoolClass->name ?? '' }} – {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Admission Date *</label>
                <input type="date" name="admission_date" class="form-control" value="{{ old('admission_date', $student->admission_date->format('Y-m-d')) }}">
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select">
                    @foreach(['active','inactive','transferred','expelled'] as $s)
                        <option value="{{ $s }}" {{ old('status', $student->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-form col-12">
                <label class="form-label">Previous School</label>
                <input type="text" name="previous_school" class="form-control" value="{{ old('previous_school', $student->previous_school) }}">
            </div>
        </div>
    </div>
</div>

<!-- Parents -->
<div class="mb-2 card">
    <div class="card-body">
        <div class="form-section-title"><i class="fas fa-users"></i> Parent / Guardian Information</div>
        @php $p = $student->parentRecord; @endphp

        <div style="font-weight:700; color:var(--primary); font-size:0.85rem; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:1px;">Father</div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Father's Full Name *</label>
                <input type="text" name="father_full_name" class="form-control" value="{{ old('father_full_name', $p->father_full_name ?? '') }}">
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Father's CNIC</label>
                <input type="text" name="father_cnic" class="form-control" value="{{ old('father_cnic', $p->father_cnic ?? '') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Father's Phone *</label>
                <input type="text" name="father_phone" class="form-control" value="{{ old('father_phone', $p->father_phone ?? '') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Occupation</label>
                <input type="text" name="father_occupation" class="form-control" value="{{ old('father_occupation', $p->father_occupation ?? '') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Monthly Income (PKR)</label>
                <input type="number" name="father_income" class="form-control" value="{{ old('father_income', $p->father_income ?? '') }}">
            </div>
        </div>

        <div style="font-weight:700; color:var(--primary); font-size:0.85rem; margin: 0.5rem 0 0.8rem; text-transform:uppercase; letter-spacing:1px; padding-top:0.8rem; border-top:1px dashed var(--border);">Mother</div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Mother's Full Name *</label>
                <input type="text" name="mother_full_name" class="form-control" value="{{ old('mother_full_name', $p->mother_full_name ?? '') }}">
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Mother's CNIC</label>
                <input type="text" name="mother_cnic" class="form-control" value="{{ old('mother_cnic', $p->mother_cnic ?? '') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Mother's Phone</label>
                <input type="text" name="mother_phone" class="form-control" value="{{ old('mother_phone', $p->mother_phone ?? '') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Occupation</label>
                <input type="text" name="mother_occupation" class="form-control" value="{{ old('mother_occupation', $p->mother_occupation ?? '') }}">
            </div>
        </div>

        <div style="font-weight:700; color:var(--primary); font-size:0.85rem; margin: 0.5rem 0 0.8rem; text-transform:uppercase; letter-spacing:1px; padding-top:0.8rem; border-top:1px dashed var(--border);">Guardian</div>
        <div class="row">
            <div class="mb-form col-4">
                <label class="form-label">Guardian Name</label>
                <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $p->guardian_name ?? '') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Relation</label>
                <input type="text" name="guardian_relation" class="form-control" value="{{ old('guardian_relation', $p->guardian_relation ?? '') }}">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Guardian Phone</label>
                <input type="text" name="guardian_phone" class="form-control" value="{{ old('guardian_phone', $p->guardian_phone ?? '') }}">
            </div>
        </div>
    </div>
</div>

<div style="display:flex; gap:0.8rem; padding-top:0.5rem;">
    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Changes</button>
    <a href="{{ route('admin.students.show', $student) }}" class="btn-outline-secondary btn btn-lg">Cancel</a>
</div>
</form>
@endsection