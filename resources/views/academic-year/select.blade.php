<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Academic Year — SchoolMS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

{{-- Top bar --}}
<div style="position:fixed; top:0; right:0; left:0;
            background:var(--white); border-bottom:1px solid var(--border);
            padding:0.9rem 2rem;
            display:flex; align-items:center; justify-content:space-between;
            box-shadow:var(--shadow); z-index:100;">
    <span style="font-family:var(--font-display); font-size:1.2rem; color:var(--primary);">
        School<span style="color:var(--accent);">MS</span>
    </span>
    <div style="display:flex; align-items:center; gap:0.8rem;">
        {{-- Show campus badge for admin --}}
        @php
            use App\Helpers\CampusContext;
            $activeCampus = Auth::user()->isAdmin() ? CampusContext::current() : Auth::user()->teacher?->campus;
        @endphp
        @if($activeCampus)
        <span class="campus-badge-topbar">
            <i class="fas fa-building"></i> {{ $activeCampus->name }}
        </span>
        @endif
        <span style="font-size:0.85rem; color:var(--text-muted);">
            {{ Auth::user()->name }}
        </span>
        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="topbar-logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </div>
</div>

<div style="min-height:100vh; background:var(--light-bg);
            display:flex; flex-direction:column;
            align-items:center; justify-content:center;
            padding:5rem 1rem 2rem;">

    {{-- Alerts --}}
    @if(session('error'))
    <div class="alert alert-danger" style="width:100%; max-width:780px; margin-bottom:1.5rem;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif
    @if(session('info'))
    <div class="alert alert-info" style="width:100%; max-width:780px; margin-bottom:1.5rem;">
        <i class="fas fa-info-circle"></i> {{ session('info') }}
    </div>
    @endif

    {{-- Header --}}
    <div style="text-align:center; margin-bottom:2.5rem; width:100%; max-width:780px;">
        <div style="width:64px; height:64px; background:var(--primary);
                    border-radius:var(--radius); display:flex; align-items:center;
                    justify-content:center; margin:0 auto 1.2rem;
                    font-size:1.8rem; color:var(--white);">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <h1 style="font-family:var(--font-display); font-size:2rem;
                   color:var(--primary); margin-bottom:0.4rem;">
            Select Academic Year
        </h1>
        <p style="color:var(--text-muted); font-size:0.93rem;">
            All data — attendance, fees, and performance — will be filtered by the selected year.
        </p>
    </div>

    {{-- Year Grid --}}
    <form action="{{ route('academic-year.select.post') }}" method="POST"
          style="width:100%; max-width:780px;">
        @csrf

        @if($errors->has('academic_year_id'))
        <div class="alert alert-danger" style="margin-bottom:1rem;">
            <i class="fas fa-exclamation-triangle"></i>
            {{ $errors->first('academic_year_id') }}
        </div>
        @endif

        <div style="display:grid;
                    grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));
                    gap:1rem; margin-bottom:2rem;">
            @foreach($years as $year)
            <div style="position:relative;">
                <input type="radio" name="academic_year_id"
                       id="year_{{ $year->id }}"
                       value="{{ $year->id }}"
                       {{ $year->is_current ? 'checked' : '' }}
                       style="position:absolute; opacity:0; width:0; height:0;">

                <label for="year_{{ $year->id }}"
                       style="display:block; background:var(--white);
                              border:2px solid var(--border); border-radius:var(--radius);
                              padding:1.3rem 1.5rem; cursor:pointer;
                              transition:all var(--transition); box-shadow:var(--shadow);"
                       id="lbl_{{ $year->id }}"
                       onclick="selectYear({{ $year->id }})">

                    <div style="display:flex; align-items:flex-start;
                                justify-content:space-between; margin-bottom:0.7rem;">
                        <div style="width:22px; height:22px; border-radius:50%;
                                    border:2px solid var(--border); flex-shrink:0;
                                    display:flex; align-items:center; justify-content:center;
                                    transition:all var(--transition);"
                             id="radio_{{ $year->id }}">
                        </div>
                        <span class="badge {{ $year->status_badge_class }}"
                              style="font-size:0.72rem;">
                            {{ $year->status_label }}
                        </span>
                    </div>

                    <div style="font-family:var(--font-display);
                                font-size:1.4rem; font-weight:700;
                                color:var(--primary); margin-bottom:4px;">
                        {{ $year->name }}
                    </div>
                    <div style="font-size:0.81rem; color:var(--text-muted);">
                        {{ $year->duration }}
                    </div>
                    @if($year->notes)
                    <div style="font-size:0.78rem; color:var(--text-muted);
                                margin-top:4px; font-style:italic;">
                        {{ $year->notes }}
                    </div>
                    @endif
                    @if($year->is_locked)
                    <div style="margin-top:6px; font-size:0.77rem;
                                color:var(--danger); font-weight:600;">
                        <i class="fas fa-lock"></i> Read-only
                    </div>
                    @endif
                </label>
            </div>
            @endforeach
        </div>

        <div style="text-align:center;">
            <button type="submit" class="btn btn-primary btn-lg"
                    style="min-width:240px;">
                <i class="fa-arrow-right fas"></i> Continue to Dashboard
            </button>

            {{-- Admin can also go manage years --}}
            @if(Auth::user()->isAdmin())
            <div style="margin-top:1rem;">
                <a href="{{ route('admin.academic-years.index') }}"
                   style="font-size:0.87rem; color:var(--text-muted);">
                    <i class="fas fa-cog"></i> Manage Academic Years
                </a>
            </div>
            @endif
        </div>
    </form>
</div>

<script>
// Highlight selected radio card
function selectYear(id) {
    // Reset all
    document.querySelectorAll('[id^="lbl_"]').forEach(lbl => {
        lbl.style.borderColor    = 'var(--border)';
        lbl.style.background     = 'var(--white)';
        lbl.style.boxShadow      = 'var(--shadow)';
        lbl.style.transform      = '';
    });
    document.querySelectorAll('[id^="radio_"]').forEach(r => {
        r.style.background  = '';
        r.style.borderColor = 'var(--border)';
        r.innerHTML         = '';
    });

    // Activate selected
    const lbl   = document.getElementById('lbl_' + id);
    const radio = document.getElementById('radio_' + id);

    lbl.style.borderColor = 'var(--primary)';
    lbl.style.background  = 'rgba(37,99,168,0.04)';
    lbl.style.boxShadow   = '0 0 0 3px rgba(37,99,168,0.15), var(--shadow-md)';
    lbl.style.transform   = 'translateY(-2px)';

    radio.style.background  = 'var(--primary)';
    radio.style.borderColor = 'var(--primary)';
    radio.innerHTML = '<i class="fas fa-check" style="color:white; font-size:0.65rem;"></i>';
}

// Init — highlight current/pre-selected year on load
document.addEventListener('DOMContentLoaded', function () {
    const checked = document.querySelector('input[name="academic_year_id"]:checked');
    if (checked) selectYear(checked.value);
});
</script>
</body>
</html>