@extends('layouts.app')
@section('title', 'Enroll Student')
@section('page-title', 'Enroll Existing Student')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Enroll Existing Student</div>
        <div class="page-header-sub">
            Enroll a student who already exists in the system
            into the current academic year
        </div>
    </div>
    <a href="{{ route('admin.enrollment.index') }}"
       class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div style="max-width:640px;">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.enrollment.store') }}"
                  method="POST" novalidate>
                @csrf

                <div class="mb-form">
                    <label class="form-label">Student *</label>
                    <select name="student_id"
                            class="form-select {{ $errors->has('student_id')
                                ? 'is-invalid' : '' }}">
                        <option value="">-- Select Student --</option>
                        @foreach($unenrolledStudents as $student)
                            <option value="{{ $student->id }}"
                                    {{ old('student_id') == $student->id
                                        ? 'selected' : '' }}>
                                {{ $student->full_name }}
                                ({{ $student->cnic ?? 'No CNIC' }})
                                — Campus: {{ $student->campus->name ?? '—' }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    @if($unenrolledStudents->isEmpty())
                        <div class="alert alert-info" style="margin-top:0.7rem;">
                            <i class="fas fa-info-circle"></i>
                            All existing students are already enrolled this year.
                            Use <a href="{{ route('admin.enrollment.admission') }}">
                            New Admission</a> to add a new student.
                        </div>
                    @endif
                </div>

                <div class="row">
                    <div class="mb-form col-6">
                        <label class="form-label">Class *</label>
                        <select name="class_id"
                                class="form-select {{ $errors->has('class_id')
                                    ? 'is-invalid' : '' }}"
                                onchange="filterSections(this.value)">
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}"
                                        {{ old('class_id') == $class->id
                                            ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Section *</label>
                        <select name="section_id"
                                class="form-select {{ $errors->has('section_id')
                                    ? 'is-invalid' : '' }}"
                                id="section-select">
                            <option value="">-- Select Section --</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}"
                                        data-class="{{ $section->class_id }}"
                                        {{ old('section_id') == $section->id
                                            ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('section_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mb-form col-6">
                        <label class="form-label">Roll Number</label>
                        <input type="text" name="roll_number"
                               class="form-control"
                               value="{{ old('roll_number') }}"
                               placeholder="e.g. 01, A-12">
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Enrolled On</label>
                        <input type="date" name="enrolled_at"
                               class="form-control"
                               value="{{ old('enrolled_at', today()->toDateString()) }}">
                    </div>
                </div>

                <div class="mb-form">
                    <label class="form-label">Notes (optional)</label>
                    <input type="text" name="notes"
                           class="form-control"
                           value="{{ old('notes') }}"
                           placeholder="Any notes about this enrollment">
                </div>

                <div style="display:flex; gap:0.8rem; padding-top:1rem;
                            border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-check"></i> Enroll Student
                    </button>
                    <a href="{{ route('admin.enrollment.index') }}"
                       class="btn-outline-secondary btn btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

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