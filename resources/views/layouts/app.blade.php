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
                    <a href="{{ route('super.grading.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('super.grading.*') ? 'active' : '' }}">
                        <i class="fas fa-star-half-alt"></i> Grading System
                    </a>
                    <a href="{{ route('super.grading.weights') }}"
                        class="sidebar-nav-link {{ request()->routeIs('super.grading.weights') ? 'active' : '' }}">
                        <i class="fas fa-balance-scale"></i> Exam Weights
                    </a>
                @else
                    <span class="sidebar-nav-label">Main</span>
                    <a href="{{ route('admin.dashboard') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>

                    <a href="{{ route('admin.academic-years.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt"></i> Academic Years
                    </a>
                    <span class="sidebar-nav-label">Enrollment</span>
                    <a href="{{ route('admin.enrollment.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.enrollment.index') ? 'active' : '' }}">
                        <i class="fas fa-user-graduate"></i> Enrollments
                    </a>
                    <a href="{{ route('admin.enrollment.carry-forward') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.enrollment.carry-forward') ? 'active' : '' }}">
                        <i class="fas fa-forward"></i> Carry Forward
                    </a>
                    <a href="{{ route('admin.enrollment.admission') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.enrollment.admission') ? 'active' : '' }}">
                        <i class="fas fa-user-plus"></i> New Admission
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
                    <a href="{{ route('admin.timetable.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.timetable.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt"></i> Timetables
                    </a>

                    <span class="sidebar-nav-label">Performance</span>
                    <a href="{{ route('admin.performance.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.performance.index') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i> Marks & Grades
                    </a>
                    <a href="{{ route('admin.performance.class-report') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.performance.class-report') ? 'active' : '' }}">
                        <i class="fa-table fas"></i> Class Report
                    </a>
                    <a href="{{ route('admin.grading.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.grading.*') ? 'active' : '' }}">
                        <i class="fas fa-star-half-alt"></i> Grading System
                    </a>
                    <span class="sidebar-nav-label">Attendance</span>
                    <a href="{{ route('admin.attendance.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.attendance.index') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check"></i> Attendance
                    </a>
                    <a href="{{ route('admin.attendance.report') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.attendance.report') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> Att. Report
                    </a>
                    {{-- Add this inside the admin (non-super-admin) nav section, after Academics --}}
                    <span class="sidebar-nav-label">Finance</span>
                    <a href="{{ route('admin.fee.schedulers.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.fee.schedulers.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i> Fee Schedulers
                    </a>
                    <a href="{{ route('admin.fee.invoices.index') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.fee.invoices.*') ? 'active' : '' }}">
                        <i class="fas fa-receipt"></i> Invoices
                    </a>
                    <a href="{{ route('admin.fee.invoices.bulk') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.fee.invoices.bulk') ? 'active' : '' }}">
                        <i class="fas fa-bolt"></i> Bulk Generate
                    </a>
                    <a href="{{ route('admin.fee.settings') }}"
                        class="sidebar-nav-link {{ request()->routeIs('admin.fee.settings*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i> Fee Settings
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
                    @php
                        use App\Helpers\AcademicYearContext;
                        $activeYear = !$isSuperAdmin ? AcademicYearContext::current() : null;
                    @endphp
                    @if ($activeYear)
                        <span
                            style="display:inline-flex; align-items:center; gap:6px;
                 background:rgba(232,160,32,0.12); color:#7a5800;
                 padding:0.32rem 0.85rem; border-radius:20px;
                 font-size:0.8rem; font-weight:700;
                 border:1.5px solid rgba(232,160,32,0.3);">
                            <i class="fas fa-calendar-alt" style="color:var(--accent);"></i>
                            {{ $activeYear->name }}
                            @if ($activeYear->is_locked)
                                <i class="fas fa-lock" style="font-size:0.65rem;"></i>
                            @endif
                        </span>
                        <a href="{{ route('academic-year.switch') }}" class="campus-switch-btn">
                            <i class="fas fa-exchange-alt"></i> Year
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
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
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
    {{-- ═══════════════════════════════════════════════════════════════
     GLOBAL ALERT / CONFIRM BOX
     Usage:
       smsAlert('Message', 'success|danger|warning|info', 'Title')
       smsConfirm('Are you sure?', callback, 'Title', 'danger')
════════════════════════════════════════════════════════════════ --}}

    <div id="sms-overlay" class="sms-overlay" role="dialog" aria-modal="true" style="display:none;">
        <div class="sms-box" id="sms-box">
            <div class="sms-icon-wrap" id="sms-icon">
                <i id="sms-icon-i" class="fas fa-info-circle"></i>
            </div>
            <div class="sms-title" id="sms-title">Notice</div>
            <div class="sms-message" id="sms-message"></div>
            <div class="sms-actions" id="sms-actions"></div>
        </div>
    </div>

    <script>
        // ── Core show/hide ────────────────────────────────────────────────────────────
        function _smsShow() {
            const ov = document.getElementById('sms-overlay');
            ov.style.display = 'flex';
            requestAnimationFrame(() => requestAnimationFrame(() => ov.classList.add('active')));
            document.addEventListener('keydown', _smsEscHandler);
        }

        function _smsHide() {
            const ov = document.getElementById('sms-overlay');
            ov.classList.remove('active');
            setTimeout(() => {
                ov.style.display = 'none';
            }, 230);
            document.removeEventListener('keydown', _smsEscHandler);
        }

        function _smsEscHandler(e) {
            if (e.key === 'Escape') _smsHide();
        }
        _smsHide();

        function _smsSetType(type) {
            const icons = {
                success: 'fa-check-circle',
                danger: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle',
            };

            const wrap = document.getElementById('sms-icon');
            const icon = document.getElementById('sms-icon-i');

            wrap.className = 'sms-icon-wrap ' + type;
            icon.className = 'fas ' + (icons[type] || icons.info);
        }

        // ── Alert (one button) ────────────────────────────────────────────────────────
        function smsAlert(message, type = 'info', title = null) {
            const defaultTitles = {
                success: 'Success',
                danger: 'Error',
                warning: 'Warning',
                info: 'Notice',
            };

            document.getElementById('sms-title').textContent = title || defaultTitles[type] || 'Notice';
            document.getElementById('sms-message').textContent = message;
            _smsSetType(type);

            const actions = document.getElementById('sms-actions');
            actions.innerHTML = `
        <button class="btn btn-primary" onclick="_smsHide()">
            <i class="fas fa-check"></i> OK
        </button>`;

            _smsShow();
        }

        // ── Confirm (two buttons) ─────────────────────────────────────────────────────
        function smsConfirm(message, onConfirm, title = 'Confirm Action', type = 'warning') {
            document.getElementById('sms-title').textContent = title;
            document.getElementById('sms-message').textContent = message;
            _smsSetType(type);

            const btnLabels = {
                danger: {
                    confirm: 'Yes, Delete',
                    confirmClass: 'btn-danger'
                },
                warning: {
                    confirm: 'Yes, Continue',
                    confirmClass: 'btn-warning'
                },
                info: {
                    confirm: 'Yes, Proceed',
                    confirmClass: 'btn-primary'
                },
                success: {
                    confirm: 'Yes',
                    confirmClass: 'btn-success'
                },
            };

            const labels = btnLabels[type] || btnLabels.warning;
            const actions = document.getElementById('sms-actions');

            actions.innerHTML = `
        <button class="btn-outline-secondary btn" onclick="_smsHide()">
            <i class="fas fa-times"></i> Cancel
        </button>
        <button class="btn ${labels.confirmClass}" id="sms-confirm-btn">
            <i class="fas fa-check"></i> ${labels.confirm}
        </button>`;

            document.getElementById('sms-confirm-btn').addEventListener('click', function() {
                _smsHide();
                if (typeof onConfirm === 'function') onConfirm();
                else if (typeof onConfirm === 'string') eval(onConfirm);
            });

            _smsShow();
        }

        // ── Replace all onclick="return confirm(...)" forms ───────────────────────────
        document.addEventListener('DOMContentLoaded', function() {
            // Intercept all forms with data-confirm attribute
            document.querySelectorAll('form[data-confirm]').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const msg = form.dataset.confirm || 'Are you sure?';
                    const type = form.dataset.type || 'warning';
                    const title = form.dataset.title || 'Confirm Action';
                    smsConfirm(msg, () => form.submit(), title, type);
                });
            });

            // Flash messages from Laravel session — show as smsAlert
            @if (session('success'))
                smsAlert(@json(session('success')), 'success');
            @endif
            @if (session('error'))
                smsAlert(@json(session('error')), 'danger');
            @endif
            @if (session('warning'))
                smsAlert(@json(session('warning')), 'warning');
            @endif
            @if (session('info'))
                smsAlert(@json(session('info')), 'info');
            @endif
        });
    </script>
</body>

</html>
