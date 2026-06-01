@extends('layouts.app')
@section('title', 'Fee Settings')
@section('page-title', 'Fee Settings')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Campus Fee Settings</div>
        <div class="page-header-sub">Logo and contact info shown on fee slips</div>
    </div>
</div>

<div style="max-width:600px;">
    <div class="card">
        <div class="card-body">
            <div class="form-section-title">
                <i class="fas fa-cog"></i> Settings for {{ $campus->name }}
            </div>

            <form action="{{ route('admin.fee.settings.update') }}" method="POST"
                  enctype="multipart/form-data" novalidate>
                @csrf

                <!-- Current Logo -->
                @if($setting->logo)
                <div class="mb-form">
                    <label class="form-label">Current Logo</label>
                    <div style="display:flex; align-items:center; gap:1rem;">
                        <img src="{{ $setting->logo_url }}" alt="Logo"
                             style="max-height:60px; border:1px solid var(--border);
                                    border-radius:var(--radius-sm); padding:4px;">
                        <form action="{{ route('admin.fee.settings.remove-logo') }}"
                              method="POST" style="display:inline;" data-confirm="Remove Logo?" data-type="danger" data-title="Delete">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-outline-danger btn btn-sm"
                                   >
                                <i class="fas fa-trash-alt"></i> Remove
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <div class="mb-form">
                    <label class="form-label">Upload Logo</label>
                    <input type="file" name="logo" class="form-control"
                           accept=".jpg,.jpeg,.png">
                    <small style="color:var(--text-muted); font-size:0.79rem;">
                        JPG or PNG, max 1MB. Shown on fee slips.
                    </small>
                </div>

                <div class="mb-form">
                    <label class="form-label">Tagline / Motto</label>
                    <input type="text" name="tagline" class="form-control"
                           value="{{ old('tagline', $setting->tagline) }}"
                           placeholder="e.g. Education for Excellence">
                </div>

                <div class="row">
                    <div class="mb-form col-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $setting->phone) }}">
                    </div>
                    <div class="mb-form col-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $setting->email) }}">
                    </div>
                </div>

                <div class="mb-form">
                    <label class="form-label">Address (for slip)</label>
                    <input type="text" name="address" class="form-control"
                           value="{{ old('address', $setting->address) }}"
                           placeholder="Full campus address">
                </div>

                <div style="padding-top:1rem; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection