<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — SchoolMS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    @php
        use App\Helpers\CampusContext;
        $activeCampus = CampusContext::current();
        $isSuperAdmin = Auth::user()->isSuperAdmin();
    @endphp
    <div class="portal-layout">

        <!-- Sidebar -->
        <aside class="portal-sidebar">
            <div class="sidebar-brand">
                <span class="sidebar-brand-name">School<span>MS</span></span>
                <span class="sidebar-brand-sub">
                    @if ($isSuperAdmin)
                        Super Admin Panel
                    @elseif($activeCampus)
                        {{ Str::limit($activeCampus->name, 22) }}
                    @else
                        Management System
                    @endif
                </span>
            </div>

            <nav class="sidebar-nav">
                @if ($isSuperAdmin)
                    <span class="sidebar-nav-label">Super Admin</span>
                    <a href="{{ route('super.dashboard') }}"
                        class="sidebar-nav-link {{ request()->routeIs('super.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                        <span class="sidebar-badge super">SA</span>
                    </a>

                    <span class="sidebar-nav-label">Management</span>
                    <a href="{{ route('super.campuses.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('super.campuses.*') ? 'active' : '' }}">
                        <i class="fas fa-building"></i> Campuses
                    </a>
                    <a href="{{ route('super.admins.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('super.admins.*') ? 'active' : '' }}">
                        <i class="fas fa-user-shield"></i> Admin Users
                    </a>
                @else
                    <span class="sidebar-nav-label">Main</span>
                    <a href="{{ route('admin.dashboard') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>

                    <span class="sidebar-nav-label">People</span>
                    <a href="{{ route('admin.students.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                        <i class="fas fa-user-graduate"></i> Students
                    </a>
                    <a href="{{ route('admin.teachers.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                        <i class="fas fa-chalkboard-teacher"></i> Teachers
                    </a>

                    <span class="sidebar-nav-label">Academics</span>
                    <a href="{{ route('admin.classes.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                        <i class="fa-layer-group fas"></i> Classes
                    </a>
                    <a href="{{ route('admin.sections.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.sections.*') ? 'active' : '' }}">
                        <i class="fas fa-sitemap"></i> Sections
                    </a>
                    <a href="{{ route('admin.subjects.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                        <i class="fas fa-book"></i> Subjects
                    </a>
                    <a href="{{ route('admin.timetable.periods.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.timetable.periods.*') ? 'active' : '' }}">
                        <i class="fas fa-clock"></i> Time Periods
                    </a>
                    <a href="{{ route('admin.timetable.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.timetable.*') && !request()->routeIs('admin.timetable.periods.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt"></i> Timetables
                    </a>
                    {{-- Add this inside the admin (non-super-admin) nav section, after Academics --}}
                    <span class="sidebar-nav-label">Finance</span>
                    <a href="{{ route('admin.fee.labels.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.fee.labels.*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i> Fee Labels
                    </a>
                    <a href="{{ route('admin.fee.structures.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.fee.structures.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i> Fee Structures
                    </a>
                    <a href="{{ route('admin.fee.invoices.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.fee.invoices.*') ? 'active' : '' }}">
                        <i class="fas fa-receipt"></i> Invoices
                    </a>
                @endif
            </nav>

            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="sidebar-user-info">
                    <span class="sidebar-user-name">{{ Auth::user()->name }}</span>
                    <span class="sidebar-user-role">
                        {{ $isSuperAdmin ? 'Super Admin' : 'Admin' }}
                    </span>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <main class="portal-main">
            <header class="portal-topbar">
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>

                <div class="topbar-actions">
                    {{-- Campus badge for admins --}}
                    @if (!$isSuperAdmin && $activeCampus)
                        <span class="campus-badge-topbar">
                            <i class="fas fa-building"></i>
                            {{ $activeCampus->name }}
                        </span>
                        <a href="{{ route('campus.switch') }}" class="campus-switch-btn">
                            <i class="fas fa-exchange-alt"></i> Switch
                        </a>
                    @endif

                    <span style="font-size:0.83rem; color:var(--text-muted);">
                        {{ Auth::user()->email }}
                    </span>

                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="topbar-logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </header>

            <div class="portal-content">
                @if (session('success'))
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif
                @if (session('info'))
                    <div class="alert alert-info"><i class="fas fa-info-circle"></i> {{ session('info') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Please fix the following errors:</strong>
                            <ul style="margin:0.4rem 0 0 1rem; padding:0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>

    </div>
</body>

</html>
