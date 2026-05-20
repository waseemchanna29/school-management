@extends('layouts.app')
@section('title', 'Edit Admin')
@section('page-title', 'Edit Admin')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Edit Admin</div>
        <div class="page-header-sub">{{ $user->name }}</div>
    </div>
    <a href="{{ route('super.admins.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div style="max-width:680px;">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('super.admins.update', $user) }}" method="POST" novalidate>
                @csrf @method('PUT')

                <div class="form-section-title"><i class="fas fa-user-shield"></i> Account Details</div>

                <div class="mb-form">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name', $user->name) }}">
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="mb-form">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           value="{{ old('email', $user->email) }}">
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="row">
                    <div class="mb-form col-6">
                        <label class="form-label">New Password <span style="color:var(--text-muted); font-weight:400;">(leave blank to keep)</span></label>
                        <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Min. 8 characters">
                        @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>

                <div class="form-section-title" style="margin-top:1rem;"><i class="fas fa-building"></i> Assigned Campuses</div>

                <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:0.6rem; margin-bottom:1rem;">
                    @foreach($campuses as $campus)
                    <div style="display:flex; align-items:center; gap:10px; padding:0.7rem 1rem; background:var(--light-bg); border-radius:var(--radius-sm); border:1.5px solid var(--border);">
                        <input type="checkbox" name="campuses[]" id="c_{{ $campus->id }}"
                               value="{{ $campus->id }}"
                               {{ in_array($campus->id, old('campuses', $user->campuses->pluck('id')->toArray())) ? 'checked' : '' }}
                               style="width:17px; height:17px; accent-color:var(--primary); cursor:pointer;">
                        <label for="c_{{ $campus->id }}" style="cursor:pointer; flex:1; margin:0;">
                            <strong style="font-size:0.9rem;">{{ $campus->name }}</strong>
                            <div style="font-size:0.77rem; color:var(--text-muted);">{{ $campus->city }}, {{ $campus->province }}</div>
                        </label>
                    </div>
                    @endforeach
                </div>

                <div style="display:flex; gap:0.8rem; padding-top:1rem; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    <a href="{{ route('super.admins.index') }}" class="btn-outline-secondary btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection