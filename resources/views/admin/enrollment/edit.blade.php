@extends('layouts.app')
@section('title', 'Edit Enrollment')
@section('page-title', 'Edit Enrollment')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">
            Edit Enrollment — {{ $enrollment->student->full_name }}
        </div>
        <div class="page-header-sub">
            @php $year = \App\Helpers\AcademicYearContext::current(); @endphp
            Academic Year: <strong>{{ $year?->name }}</strong>
        </div>
    </div>
    <a href="{{ route('admin.enrollment.index') }}"
       class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div style="max-width:640px;">
    {{-- Student Info (read-only) --}}
    <div class="mb-2 card"
         style="border-left:4px solid var(--primary);">
        <div class="card-body">
            <div style="display:flex; align-items:center; gap:1rem;">
                <div class="sidebar-user-avatar"
                     style="width:48px; height:48px; font-size:1.2rem;">
                    {{ strtoupper(substr($enrollment->student->full_name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:700; color:var(--primary); font-size:1rem;">
                        {{ $enrollment->student->full_name }}
                    </div>
                    <div style="font-size:0.82rem; color:var(--text-muted);">
                        Father: {{ $enrollment->student->father_name }}
                        &bull; CNIC: {{ $enrollment->student->cnic ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="form-section-title">
                <i class="fas fa-user-graduate"></i> Enrollment Details
            </div>

            <form action="{{ route('admin.enrollment.update', $enrollment) }}"
                  method="POST" novalidate>
                @csrf @method('PUT')

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
                                        {{ old('class_id', $enrollment->class_id)
                                            == $class->id ? 'selected' : '' }}>
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
                                        {{ old('section_id', $enrollment->section_id)
                                            == $section->id ? 'selected' : '' }}>
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
                               value="{{ old('roll_number', $enrollment->roll_number) }}"
                               placeholder="e.g. 01">
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Status *</label>
                        <select name="status"
                                class="form-select {{ $errors->has('status')
                                    ? 'is-invalid' : '' }}">
                            @foreach([
                                'active'      => 'Active',
                                'passed'      => 'Passed',
                                'detained'    => 'Detained',
                                'left'        => 'Left School',
                                'transferred' => 'Transferred',
                            ] as $val => $label)
                                <option value="{{ $val }}"
                                        {{ old('status', $enrollment->status)
                                            === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mb-form">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes"
                           class="form-control"
                           value="{{ old('notes', $enrollment->notes) }}"
                           placeholder="Optional notes">
                </div>

                <div style="display:flex; gap:0.8rem; padding-top:1rem;
                            border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.enrollment.index') }}"
                       class="btn-outline-secondary btn">Cancel</a>
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