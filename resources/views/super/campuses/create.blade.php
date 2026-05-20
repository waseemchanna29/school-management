@extends('layouts.app')
@section('title', 'Add Campus')
@section('page-title', 'Add Campus')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Add New Campus</div>
        <div class="page-header-sub">Register a new campus in the school network</div>
    </div>
    <a href="{{ route('super.campuses.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div style="max-width:720px;">
    <div class="card">
        <div class="card-body">
            <div class="form-section-title"><i class="fas fa-building"></i> Campus Details</div>
            <form action="{{ route('super.campuses.store') }}" method="POST" novalidate>
                @csrf
                <div class="row">
                    <div class="mb-form col-8">
                        <label class="form-label">Campus Name *</label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}" placeholder="e.g. Main Campus Lahore">
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}"
                               placeholder="042-XXXXXXXX">
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                               placeholder="campus@school.edu.pk">
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Principal Name</label>
                        <input type="text" name="principal_name" class="form-control"
                               value="{{ old('principal_name') }}" placeholder="Full name">
                    </div>
                    <div class="mb-form col-12">
                        <label class="form-label">Full Address *</label>
                        <input type="text" name="address" class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                               value="{{ old('address') }}" placeholder="Street, Area, City">
                        @error('address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">City *</label>
                        <input type="text" name="city" class="form-control {{ $errors->has('city') ? 'is-invalid' : '' }}"
                               value="{{ old('city') }}">
                        @error('city')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">District *</label>
                        <input type="text" name="district" class="form-control" value="{{ old('district') }}">
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Province *</label>
                        <select name="province" class="form-select {{ $errors->has('province') ? 'is-invalid' : '' }}">
                            <option value="">-- Select --</option>
                            @foreach(['Punjab','Sindh','KPK','Balochistan','Gilgit-Baltistan','AJK','ICT'] as $p)
                                <option value="{{ $p }}" {{ old('province') === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                        @error('province')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div style="display:flex; gap:0.8rem; padding-top:1rem; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Campus
                    </button>
                    <a href="{{ route('super.campuses.index') }}" class="btn-outline-secondary btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection