<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Campus — SchoolMS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="campus-select-wrapper">

    {{-- Top bar --}}
    <div style="position:fixed; top:0; right:0; left:0; background:var(--white);
                border-bottom:1px solid var(--border); padding:0.9rem 2rem;
                display:flex; align-items:center; justify-content:space-between;
                box-shadow:var(--shadow); z-index:100;">
        <span style="font-family:var(--font-display); font-size:1.2rem; color:var(--primary);">
            School<span style="color:var(--accent);">MS</span>
        </span>
        <div style="display:flex; align-items:center; gap:0.8rem;">
            <span style="font-size:0.85rem; color:var(--text-muted);">{{ Auth::user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="topbar-logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div style="margin-top:4rem; width:100%; max-width:900px;">
        {{-- Alerts --}}
        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom:1.5rem;">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info" style="margin-bottom:1.5rem;">
                <i class="fas fa-info-circle"></i> {{ session('info') }}
            </div>
        @endif

        <div class="campus-select-header">
            <div style="width:64px; height:64px; background:var(--primary); border-radius:var(--radius);
                        display:flex; align-items:center; justify-content:center;
                        margin:0 auto 1.2rem; font-size:1.8rem; color:var(--white);">
                <i class="fas fa-building"></i>
            </div>
            <h1>Select Your Campus</h1>
            <p>Choose the campus you want to manage for this session</p>
        </div>

        <form action="{{ route('campus.select.post') }}" method="POST">
            @csrf

            @if($errors->has('campus_id'))
                <div class="alert alert-danger" style="margin-bottom:1rem;">
                    <i class="fas fa-exclamation-triangle"></i> {{ $errors->first('campus_id') }}
                </div>
            @endif

            <div class="campus-select-grid">
                @foreach($campuses as $campus)
                <div class="campus-option">
                    <input type="radio" name="campus_id" id="campus_{{ $campus->id }}"
                           value="{{ $campus->id }}"
                           {{ old('campus_id') == $campus->id || ($campuses->count() === 1) ? 'checked' : '' }}>
                    <label class="campus-option-label" for="campus_{{ $campus->id }}">
                        <div class="campus-option-check"><i class="fas fa-check"></i></div>
                        <div class="campus-option-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <div class="campus-option-name">{{ $campus->name }}</div>
                        <div class="campus-option-meta">
                            <i class="fas fa-map-marker-alt" style="color:var(--accent); margin-right:4px;"></i>
                            {{ $campus->city }}, {{ $campus->district }}, {{ $campus->province }}
                        </div>
                        @if($campus->principal_name)
                        <div class="campus-option-meta" style="margin-top:3px;">
                            <i class="fas fa-user-tie" style="color:var(--primary-light); margin-right:4px;"></i>
                            {{ $campus->principal_name }}
                        </div>
                        @endif
                        <div style="margin-top:8px;">
                            <code style="font-size:0.75rem; background:var(--light-bg); padding:2px 8px; border-radius:4px; color:var(--text-muted);">
                                {{ $campus->code }}
                            </code>
                        </div>
                    </label>
                </div>
                @endforeach
            </div>

            <div style="text-align:center; margin-top:2rem;">
                <button type="submit" class="btn btn-primary btn-lg" style="min-width:220px;">
                    <i class="fa-arrow-right fas"></i> Continue to Dashboard
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>