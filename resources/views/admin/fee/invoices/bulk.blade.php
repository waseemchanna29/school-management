@extends('layouts.app')
@section('title', 'Bulk Generate Invoices')
@section('page-title', 'Bulk Generate Invoices')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Bulk Generate Monthly Invoices</div>
        <div class="page-header-sub">
            Generates invoices for all active students who have a scheduler assigned
        </div>
    </div>
    <a href="{{ route('admin.fee.invoices.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div style="max-width:560px;">
    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Students who already have an invoice for the selected month will be
                <strong>skipped automatically</strong>.
            </div>

            <form action="{{ route('admin.fee.invoices.bulk-store') }}" method="POST" novalidate>
                @csrf

                <div class="row">
                    <div class="mb-form col-6">
                        <label class="form-label">Billing Month *</label>
                        <select name="billing_month" class="form-select">
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}"
                                        {{ date('n') == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0,0,0,$m,1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Billing Year *</label>
                        <input type="number" name="billing_year" class="form-control"
                               value="{{ date('Y') }}" min="2020" max="2099">
                    </div>
                </div>

                <div class="mb-form">
                    <label class="form-label">Due Date *</label>
                    <input type="date" name="due_date" class="form-control"
                           value="{{ date('Y-m-t') }}">
                </div>

                <div style="background:var(--light-bg); border-radius:var(--radius-sm);
                            padding:1rem 1.2rem; margin-bottom:1rem;">
                    <div style="font-weight:700; color:var(--primary); margin-bottom:0.6rem; font-size:0.88rem;">
                        <i class="fas fa-plus-circle"></i> Apply to All Invoices (optional)
                    </div>
                    <div class="row">
                        <div class="mb-form col-4">
                            <label class="form-label">Outstanding (PKR)</label>
                            <input type="number" name="outstanding" class="form-control"
                                   value="0" min="0" step="0.01"
                                   placeholder="Same for all">
                        </div>
                        <div class="mb-form col-4">
                            <label class="form-label">Fine (PKR)</label>
                            <input type="number" name="fine" class="form-control"
                                   value="0" min="0" step="0.01">
                        </div>
                        <div class="mb-form col-4">
                            <label class="form-label">Discount (PKR)</label>
                            <input type="number" name="discount" class="form-control"
                                   value="0" min="0" step="0.01">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-block btn btn-warning btn-lg"
                        onclick="return confirm('Generate invoices for all active students with schedulers for this month?')">
                    <i class="fas fa-bolt"></i> Generate All Invoices Now
                </button>
            </form>
        </div>
    </div>
</div>
@endsection