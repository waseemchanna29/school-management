@extends('layouts.app')
@section('title', 'Generate Invoice')
@section('page-title', 'Generate Invoice')

@section('content')
    <div class="page-header">
        <div>
            <div class="page-header-title">Generate Fee Invoice</div>
            <div class="page-header-sub">Create a monthly invoice for one student</div>
        </div>
        <a href="{{ route('admin.fee.invoices.index') }}" class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>

    <div style="max-width:640px;">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.fee.invoices.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="mb-form">
                        <label class="form-label">Student *</label>
                        <select name="student_id" class="form-select {{ $errors->has('student_id') ? 'is-invalid' : '' }}">
                            <option value="">-- Select Student --</option>
                            @foreach ($students as $s)
                                <option value="{{ $s->id }}"
                                    {{ old('student_id', $student?->id) == $s->id ? 'selected' : '' }}>
                                    {{ $s->full_name }}
                                    ({{ $s->enrollment?->roll_number ?? 'No Roll' }})
                                    — {{ $s->enrollment?->schoolClass?->name ?? '' }}
                                    {{ $s->enrollment?->section?->name ? '/ ' . $s->enrollment->section->name : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="mb-form col-4">
                            <label class="form-label">Billing Month *</label>
                            <select name="billing_month" class="form-select">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}"
                                        {{ old('billing_month', date('n')) == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="mb-form col-4">
                                <label class="form-label">Billing Month *</label>
                                <select name="billing_month" class="form-select">
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ $m }}"
                                            {{ old('billing_month', date('n')) == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-form col-4">
                                <label class="form-label">Academic Year *</label>
                                <select name="academic_year_id" class="form-select">
                                    @foreach ($academicYears as $ay)
                                        <option value="{{ $ay->id }}"
                                            {{ old('academic_year_id', $yearId) == $ay->id ? 'selected' : '' }}>
                                            {{ $ay->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-form col-4">
                                <label class="form-label">Due Date *</label>
                                <input type="date" name="due_date" class="form-control"
                                    value="{{ old('due_date', date('Y-m-t')) }}">
                            </div>
                        </div>

                        <div
                            style="background:var(--light-bg); border-radius:var(--radius-sm);
                            padding:1rem 1.2rem; margin-bottom:1rem;">
                            <div style="font-weight:700; color:var(--primary); margin-bottom:0.6rem; font-size:0.88rem;">
                                <i class="fas fa-plus-circle"></i> Additional Charges (optional)
                            </div>
                            <div class="row">
                                <div class="mb-form col-4">
                                    <label class="form-label">Outstanding (PKR)</label>
                                    <input type="number" name="outstanding" class="form-control"
                                        value="{{ old('outstanding', 0) }}" min="0" step="0.01"
                                        placeholder="Previous balance">
                                </div>
                                <div class="mb-form col-4">
                                    <label class="form-label">Fine / Penalty (PKR)</label>
                                    <input type="number" name="fine" class="form-control" value="{{ old('fine', 0) }}"
                                        min="0" step="0.01">
                                </div>
                                <div class="mb-form col-4">
                                    <label class="form-label">Discount (PKR)</label>
                                    <input type="number" name="discount" class="form-control"
                                        value="{{ old('discount', 0) }}" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="mb-form">
                                <label class="form-label">Remarks</label>
                                <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}"
                                    placeholder="Optional note on invoice">
                            </div>
                        </div>

                        <div
                            style="display:flex; gap:0.8rem; padding-top:1rem;
                            border-top:1px solid var(--border);">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-file-invoice-dollar"></i> Generate Invoice
                            </button>
                            <a href="{{ route('admin.fee.invoices.index') }}"
                                class="btn-outline-secondary btn btn-lg">Cancel</a>
                        </div>
                </form>
            </div>
        </div>
    </div>
@endsection
