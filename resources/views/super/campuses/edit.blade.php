@extends('layouts.app')
@section('title', 'Edit Campus')
@section('page-title', 'Edit Campus')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Edit Campus</div>
        <div class="page-header-sub">{{ $campus->name }} — <code>{{ $campus->code }}</code></div>
    </div>
    <a href="{{ route('super.campuses.show', $campus) }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div style="max-width:720px;">
    <div class="card">
        <div class="card-body">
            <div class="form-section-title"><i class="fas fa-edit"></i> Campus Details</div>
            <form action="{{ route('super.campuses.update', $campus) }}" method="POST" novalidate>
                @csrf @method('PUT')
                <div class="row">
                    <div class="mb-form col-8">
                        <label class="form-label">Campus Name *</label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name', $campus->name) }}">
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $campus->phone) }}">
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $campus->email) }}">
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Principal Name</label>
                        <input type="text" name="principal_name" class="form-control"
                               value="{{ old('principal_name', $campus->principal_name) }}">
                    </div>
                    <div class="mb-form col-12">
                        <label class="form-label">Full Address *</label>
                        <input type="text" name="address" class="form-control"
                               value="{{ old('address', $campus->address) }}">
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">City *</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $campus->city) }}">
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">District *</label>
                        <input type="text" name="district" class="form-control" value="{{ old('district', $campus->district) }}">
                    </div>
                    <div class="mb-form col-4">
                        <label class="form-label">Province *</label>
                        <select name="province" class="form-select">
                            @foreach(['Punjab','Sindh','KPK','Balochistan','Gilgit-Baltistan','AJK','ICT'] as $p)
                                <option value="{{ $p }}" {{ old('province', $campus->province) === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-form col-12">
                        <div style="display:flex; align-items:center; gap:10px; padding:0.8rem; background:var(--light-bg); border-radius:var(--radius-sm);">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', $campus->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                                   style="width:17px; height:17px; accent-color:var(--primary); cursor:pointer;">
                            <label for="is_active" style="cursor:pointer; margin:0; font-weight:600;">
                                Active
                                <span style="font-weight:400; color:var(--text-muted); font-size:0.85rem; display:block;">
                                    Inactive campuses are hidden from admin login selection
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div style="display:flex; gap:0.8rem; padding-top:1rem; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    <a href="{{ route('super.campuses.show', $campus) }}" class="btn-outline-secondary btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection