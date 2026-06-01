<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Teacher Panel') — SchoolMS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="portal-layout">

        <!-- Sidebar -->
        <aside class="portal-sidebar">
            <div class="sidebar-brand">
                <span class="sidebar-brand-name">School<span>MS</span></span>
                <span class="sidebar-brand-sub">Teacher Panel</span>
            </div>

            <nav class="sidebar-nav">
                @php $teacher = Auth::user()->teacher; @endphp
                @php $section = $teacher?->classTeacherOf; @endphp

                <span class="sidebar-nav-label">My Panel</span>
                <a href="{{ route('teacher.dashboard') }}"
                    class="sidebar-nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>

                @if ($section)
                    <span class="sidebar-nav-label">Attendance</span>
                    <a href="{{ route('teacher.attendance.take') }}"
                        class="sidebar-nav-link {{ request()->routeIs('teacher.attendance.take') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check"></i> Take Attendance
                    </a>
                    <a href="{{ route('teacher.attendance.history') }}"
                        class="sidebar-nav-link {{ request()->routeIs('teacher.attendance.history') ? 'active' : '' }}">
                        <i class="fas fa-history"></i> History
                    </a>
                    <a href="{{ route('teacher.attendance.student-report') }}"
                        class="sidebar-nav-link {{ request()->routeIs('teacher.attendance.student-report') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> Student Report
                    </a>
                @else
                    <span class="sidebar-nav-label">Attendance</span>
                    <div class="sidebar-nav-link" style="opacity:0.4; cursor:not-allowed;">
                        <i class="fas fa-lock"></i> No Section Assigned
                    </div>
                @endif
                <span class="sidebar-nav-label">Performance</span>
                <a href="{{ route('teacher.performance.subjects') }}"
                    class="sidebar-nav-link {{ request()->routeIs('teacher.performance.subjects') ? 'active' : '' }}">
                    <i class="fas fa-pen-ruler"></i> Enter Marks
                </a>
                <a href="{{ route('teacher.performance.history') }}"
                    class="sidebar-nav-link {{ request()->routeIs('teacher.performance.history') ? 'active' : '' }}">
                    <i class="fas fa-history"></i> Marks History
                </a>
            </nav>

            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="sidebar-user-info">
                    <span class="sidebar-user-name">{{ Auth::user()->name }}</span>
                    <span class="sidebar-user-role">
                        {{ $section ? 'Class Teacher — ' . $section->name : 'Teacher' }}
                    </span>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <main class="portal-main">
            <header class="portal-topbar">
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                <div class="topbar-actions">
                    @php $campusName = Auth::user()->teacher?->campus?->name; @endphp
                    @if ($campusName)
                        <span class="campus-badge-topbar">
                            <i class="fas fa-building"></i> {{ $campusName }}
                        </span>
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
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Global Alert Box --}}
    <div id="sms-overlay" class="sms-overlay" role="dialog" aria-modal="true">
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

        function _smsSetType(type) {
            const icons = {
                success: 'fa-check-circle',
                danger: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            document.getElementById('sms-icon').className = 'sms-icon-wrap ' + type;
            document.getElementById('sms-icon-i').className = 'fas ' + (icons[type] || icons.info);
        }

        function smsAlert(message, type = 'info', title = null) {
            const defaultTitles = {
                success: 'Success',
                danger: 'Error',
                warning: 'Warning',
                info: 'Notice'
            };
            document.getElementById('sms-title').textContent = title || defaultTitles[type] || 'Notice';
            document.getElementById('sms-message').textContent = message;
            _smsSetType(type);
            document.getElementById('sms-actions').innerHTML =
                `<button class="btn btn-primary" onclick="_smsHide()"><i class="fas fa-check"></i> OK</button>`;
            _smsShow();
        }

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
                }
            };
            const labels = btnLabels[type] || btnLabels.warning;
            document.getElementById('sms-actions').innerHTML =
                `
        <button class="btn-outline-secondary btn" onclick="_smsHide()"><i class="fas fa-times"></i> Cancel</button>
        <button class="btn ${labels.confirmClass}" id="sms-confirm-btn"><i class="fas fa-check"></i> ${labels.confirm}</button>`;
            document.getElementById('sms-confirm-btn').addEventListener('click', function() {
                _smsHide();
                if (typeof onConfirm === 'function') onConfirm();
                else if (typeof onConfirm === 'string') eval(onConfirm);
            });
            _smsShow();
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form[data-confirm]').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    smsConfirm(form.dataset.confirm || 'Are you sure?', () => form.submit(), form
                        .dataset.title || 'Confirm Action', form.dataset.type || 'warning');
                });
            });
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
