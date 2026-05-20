@extends('layouts.app')
@section('title', 'Generate Invoice')
@section('page-title', 'Generate Invoice')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Generate Fee Invoice</div>
        <div class="page-header-sub">Create an invoice for a student based on their fee lines</div>
    </div>
    <a href="{{ route('admin.fee.invoices.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div style="max-width:680px;">
    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                The invoice will be generated from the student's active fee lines for the selected period type and academic year.
            </div>

            <form action="{{ route('admin.fee.invoices.store') }}" method="POST" novalidate>
                @csrf

                <div class="mb-form">
                    <label class="form-label">Student *</label>
                    <select name="student_id" class="form-select {{ $errors->has('student_id') ? 'is-invalid' : '' }}">
                        <option value="">-- Select Student --</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}"
                                    {{ (old('student_id', $student?->id)) == $s->id ? 'selected' : '' }}>
                                {{ $s->full_name }} ({{ $s->roll_number }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="row">
                    <div class="mb-form col-6">
                        <label class="form-label">Academic Year *</label>
                        <select name="academic_year" class="form-select">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ old('academic_year') === $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Period Type *</label>
                        <select name="period_type" id="period_type" class="form-select" onchange="toggleMonthField()">
                            <option value="monthly"  {{ old('period_type') === 'monthly'  ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly"   {{ old('period_type') === 'yearly'   ? 'selected' : '' }}>Yearly / Annual</option>
                            <option value="one_time" {{ old('period_type') === 'one_time' ? 'selected' : '' }}>One-Time</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="mb-form col-4" id="month-field">
                        <label class="form-label">Month *</label>
                        <select name="month" class="form-select">
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ old('month', date('n')) == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0,0,0,$m,1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Year *</label>
                        <input type="number" name="year" class="form-control"
                               value="{{ old('year', date('Y')) }}" min="2020" max="2099">
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Due Date *</label>
                        <input type="date" name="due_date" class="form-control"
                               value="{{ old('due_date', date('Y-m-t')) }}">
                    </div>
                </div>

                <div class="mb-form">
                    <label class="form-label">Custom Period Label <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                    <input type="text" name="period_label" class="form-control"
                           value="{{ old('period_label') }}"
                           placeholder="e.g. January 2025, Exam Term 1 (auto-generated if empty)">
                </div>

                <div style="display:flex; gap:0.8rem; padding-top:1rem; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-file-invoice-dollar"></i> Generate Invoice
                    </button>
                    <a href="{{ route('admin.fee.invoices.index') }}" class="btn-outline-secondary btn btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleMonthField() {
    const type = document.getElementById('period_type').value;
    document.getElementById('month-field').style.display = type === 'monthly' ? '' : 'none';
}
toggleMonthField();
</script>
@endsection