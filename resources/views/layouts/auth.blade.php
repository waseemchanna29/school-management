<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'School Management')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-brand-panel">
        <div class="auth-brand-logo">School<span>MS</span></div>
        <p class="auth-brand-tagline">Complete school management system for Pakistani educational institutions</p>
        <ul class="auth-brand-features">
            <li><i class="fas fa-user-graduate"></i> Student Enrollment & Records</li>
            <li><i class="fas fa-chalkboard-teacher"></i> Teacher Management</li>
            <li><i class="fas fa-book"></i> Classes, Sections & Subjects</li>
            <li><i class="fas fa-users"></i> Parent & Guardian Info</li>
            <li><i class="fas fa-shield-alt"></i> Secure Role-based Access</li>
        </ul>
    </div>

    <div class="auth-form-panel">
        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</div>
</body>
</html>