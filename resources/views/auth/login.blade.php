@extends('layouts.auth')
@section('title', 'Login — SchoolMS')

@section('content')
<div class="auth-form-header">
    <h2>Welcome Back</h2>
    <p>Sign in to the School Management System</p>
</div>

<form action="{{ route('login.post') }}" method="POST" novalidate>
    @csrf
    <div class="mb-form">
        <label class="form-label">Email Address</label>
        <input type="email" name="email"
               class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
               value="{{ old('email') }}" placeholder="admin@school.com" autocomplete="email">
        @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="mb-form">
        <label class="form-label">Password</label>
        <input type="password" name="password"
               class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
               placeholder="Your password" autocomplete="current-password">
        @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="mb-form" style="display:flex; align-items:center; gap:8px;">
        <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}
               style="width:16px; height:16px; cursor:pointer; accent-color:var(--primary);">
        <label for="remember" style="cursor:pointer; color:var(--text-muted); font-size:0.88rem;">Remember me</label>
    </div>
    <button type="submit" class="btn-block btn btn-primary btn-lg" style="margin-top:0.5rem;">
        <i class="fas fa-sign-in-alt"></i> Sign In
    </button>
</form>
<div class="card" style="margin-top: 10px; padding:10px">
    <h3>Login Credentials</h3>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Password</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Super </td>
                    <td>super@school.com</td>
                    <td>super@1234</td>
                </tr>
                <tr>
                    <td>Admin</td>
                    <td>admin@school.com</td>
                    <td>admin@1234</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection