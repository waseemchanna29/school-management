@extends('layouts.app')
@section('title', 'New Admission')
@section('page-title', 'New Student Admission')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">New Student Admission</div>
        <div class="page-header-sub">
            Create a new student and enroll them in the current academic year
        </div>
    </div>
    <a href="{{ route('admin.enrollment.index') }}"
       class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('admin.enrollment.admission.store') }}"
      method="POST" novalidate>
@csrf

{{-- Personal Information --}}
<div class="mb-2 card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-user"></i> Personal Information
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name"
                       class="form-control {{ $errors->has('full_name')
                           ? 'is-invalid' : '' }}"
                       value="{{ old('full_name') }}"
                       placeholder="Student's full name">
                @error('full_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Gender *</label>
                <select name="gender"
                        class="form-select {{ $errors->has('gender')
                            ? 'is-invalid' : '' }}">
                    <option value="">-- Select --</option>
                    <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                </select>
                @error('gender')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-form col-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth"
                       class="form-control"
                       value="{{ old('date_of_birth') }}">
            </div>
        </div>

        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Father's Name *</label>
                <input type="text" name="father_name"
                       class="form-control {{ $errors->has('father_name')
                           ? 'is-invalid' : '' }}"
                       value="{{ old('father_name') }}"
                       placeholder="Father's full name">
                @error('father_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Mother's Name</label>
                <input type="text" name="mother_name"
                       class="form-control"
                       value="{{ old('mother_name') }}"
                       placeholder="Mother's full name">
            </div>
        </div>

        <div class="row">
            <div class="mb-form col-4">
                <label class="form-label">CNIC / B-Form</label>
                <input type="text" name="cnic"
                       class="form-control"
                       value="{{ old('cnic') }}"
                       placeholder="e.g. 42201-1234567-1">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Phone</label>
                <input type="text" name="phone"
                       class="form-control"
                       value="{{ old('phone') }}"
                       placeholder="Student or parent phone">
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Blood Group</label>
                <select name="blood_group" class="form-select">
                    <option value="">— Select —</option>
                    @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                        <option value="{{ $bg }}"
                                {{ old('blood_group') === $bg ? 'selected' : '' }}>
                            {{ $bg }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
  <div class="row">
        <div class="mb-form col-8">
            <label class="form-label">Address</label>
            <input type="text" name="address"
                   class="form-control"
                   value="{{ old('address') }}"
                   placeholder="Full address">
        </div>
        <div class="mb-form col-4">
                <label class="form-label">City</label>
                <input type="text" name="city"
                       class="form-control"
                       value="{{ old('city') }}"
                       placeholder="e.g. LARKANA">
            </div>
  </div>
        <div class="row">
            <div class="mb-form col-6">
                <label class="form-label">Previous School</label>
                <input type="text" name="previous_school"
                       class="form-control"
                       value="{{ old('previous_school') }}"
                       placeholder="Last school attended">
            </div>
            <div class="mb-form col-6">
                <label class="form-label">Admission Date *</label>
                <input type="date" name="admission_date"
                       class="form-control {{ $errors->has('admission_date')
                           ? 'is-invalid' : '' }}"
                       value="{{ old('admission_date', today()->toDateString()) }}">
                @error('admission_date')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- Enrollment Details --}}
<div class="mb-2 card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-user-graduate"></i>
            Enrollment Details
            @php $year = \App\Helpers\AcademicYearContext::current(); @endphp
            <span class="badge badge-approved" style="font-size:0.72rem; margin-left:6px;">
                {{ $year?->name ?? '' }}
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="mb-form col-4">
                <label class="form-label">Class *</label>
                <select name="class_id"
                        class="form-select {{ $errors->has('class_id')
                            ? 'is-invalid' : '' }}"
                        onchange="filterSections(this.value)">
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}"
                                {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                @error('class_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Section *</label>
                <select name="section_id"
                        class="form-select {{ $errors->has('section_id')
                            ? 'is-invalid' : '' }}"
                        id="section-select">
                    <option value="">-- Select Section --</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}"
                                data-class="{{ $section->class_id }}"
                                {{ old('section_id') == $section->id ? 'selected' : '' }}>
                            {{ $section->name }}
                        </option>
                    @endforeach
                </select>
                @error('section_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-form col-4">
                <label class="form-label">Roll Number</label>
                <input type="text" name="roll_number"
                       class="form-control"
                       value="{{ old('roll_number') }}"
                       placeholder="e.g. 01">
            </div>
             <div class="mb-form col-4">
                <label class="form-label">GR Number</label>
                <input type="text" name="gr_number"
                       class="form-control"
                       value="{{ old('gr_number') }}"
                       placeholder="e.g. 01">
            </div>
        </div>
    </div>
</div>

<div style="display:flex; gap:0.8rem;">
    <button type="submit" class="btn btn-primary btn-lg">
        <i class="fas fa-user-plus"></i> Admit & Enroll Student
    </button>
    <a href="{{ route('admin.enrollment.index') }}"
       class="btn-outline-secondary btn btn-lg">Cancel</a>
</div>
</form>

<script>
function filterSections(classId) {
    const sel = document.getElementById('section-select');
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!classId || opt.dataset.class === classId)
            ? '' : 'none';
    });
    sel.value = '';
}
</script>
@endsection