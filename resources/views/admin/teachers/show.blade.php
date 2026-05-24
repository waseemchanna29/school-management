@extends('layouts.app')
@section('title', 'Teacher Profile')
@section('page-title', 'Teacher Profile')

@section('content')
    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            @if ($teacher->photo)
                <img src="{{ Storage::url($teacher->photo) }}" class="profile-avatar">
            @else
                <div class="profile-avatar-placeholder">{{ strtoupper(substr($teacher->full_name, 0, 1)) }}</div>
            @endif
            <div>
                <div class="page-header-title">{{ $teacher->full_name }}</div>
                <div class="page-header-sub">
                    <code>{{ $teacher->employee_code }}</code> &bull;
                    {{ $teacher->employment_type }} &bull;
                    <span class="badge {{ $teacher->is_active ? 'badge-approved' : 'badge-rejected' }}">
                        {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            {{-- Add inside the action buttons div at the top --}}
            <a href="{{ route('admin.timetable.teacher-view', $teacher) }}" class="btn-outline-primary btn btn-sm">
                <i class="fas fa-calendar-alt"></i> Schedule
            </a>
            <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.teachers.index') }}" class="btn-outline-secondary btn btn-sm">
                <i class="fa-arrow-left fas"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-8">
            <!-- Personal -->
            <div class="mb-2 card">
                <div class="card-header">
                    <div class="card-header-title"><i class="fas fa-id-card"></i> Personal Information</div>
                </div>
                <div class="card-body">
                    <div class="profile-meta-grid">
                        <div><span class="profile-meta-label">Full Name</span><span
                                class="profile-meta-value">{{ $teacher->full_name }}</span></div>
                        <div><span class="profile-meta-label">Father's Name</span><span
                                class="profile-meta-value">{{ $teacher->father_name }}</span></div>
                        <div><span class="profile-meta-label">CNIC</span><span
                                class="profile-meta-value">{{ $teacher->cnic }}</span></div>
                        <div><span class="profile-meta-label">Gender</span><span
                                class="profile-meta-value">{{ $teacher->gender }}</span></div>
                        <div><span class="profile-meta-label">Date of Birth</span><span
                                class="profile-meta-value">{{ $teacher->date_of_birth->format('d M, Y') }} (Age:
                                {{ $teacher->age }})</span></div>
                        <div><span class="profile-meta-label">Religion</span><span
                                class="profile-meta-value">{{ $teacher->religion ?? '—' }}</span></div>
                        <div><span class="profile-meta-label">Nationality</span><span
                                class="profile-meta-value">{{ $teacher->nationality }}</span></div>
                        <div><span class="profile-meta-label">Domicile</span><span
                                class="profile-meta-value">{{ $teacher->domicile ?? '—' }}</span></div>
                        <div><span class="profile-meta-label">Phone</span><span
                                class="profile-meta-value">{{ $teacher->phone }}</span></div>
                        <div><span class="profile-meta-label">Emergency Phone</span><span
                                class="profile-meta-value">{{ $teacher->emergency_phone ?? '—' }}</span></div>
                        <div><span class="profile-meta-label">Email</span><span
                                class="profile-meta-value">{{ $teacher->user->email }}</span></div>
                        <div><span class="profile-meta-label">City</span><span
                                class="profile-meta-value">{{ $teacher->city }}, {{ $teacher->district }},
                                {{ $teacher->province }}</span></div>
                        <div style="grid-column: 1 / -1;"><span class="profile-meta-label">Address</span><span
                                class="profile-meta-value">{{ $teacher->address }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Professional -->
            <div class="mb-2 card">
                <div class="card-header">
                    <div class="card-header-title"><i class="fas fa-briefcase"></i> Professional Details</div>
                </div>
                <div class="card-body">
                    <div class="profile-meta-grid">
                        <div><span class="profile-meta-label">Qualification</span><span
                                class="profile-meta-value">{{ $teacher->qualification }}</span></div>
                        <div><span class="profile-meta-label">Specialization</span><span
                                class="profile-meta-value">{{ $teacher->specialization ?? '—' }}</span></div>
                        <div><span class="profile-meta-label">Employment Type</span><span
                                class="profile-meta-value">{{ $teacher->employment_type }}</span></div>
                        <div><span class="profile-meta-label">Joining Date</span><span
                                class="profile-meta-value">{{ $teacher->joining_date->format('d M, Y') }}</span></div>
                        @if ($teacher->salary)
                            <div><span class="profile-meta-label">Monthly Salary</span><span class="profile-meta-value">PKR
                                    {{ number_format($teacher->salary, 2) }}</span></div>
                        @endif
                        @if ($teacher->bank_name)
                            <div><span class="profile-meta-label">Bank</span><span
                                    class="profile-meta-value">{{ $teacher->bank_name }}</span></div>
                            <div><span class="profile-meta-label">Account No.</span><span
                                    class="profile-meta-value">{{ $teacher->bank_account ?? '—' }}</span></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-4">
            <!-- Subjects -->
            <div class="mb-2 card">
                <div class="card-header">
                    <div class="card-header-title"><i class="fas fa-book"></i> Assigned Subjects</div>
                </div>
                <div class="card-body">
                    @forelse($teacher->subjects as $subject)
                        <div
                            style="padding:0.5rem 0; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                            <strong style="font-size:0.9rem;">{{ $subject->name }}</strong>
                            <span class="badge badge-info">{{ $subject->schoolClass->name ?? '' }}</span>
                        </div>
                    @empty
                        <p style="color:var(--text-muted); font-size:0.88rem;">No subjects assigned.</p>
                    @endforelse
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card" style="border-color:rgba(220,53,69,0.25);">
                <div class="card-header" style="background:rgba(220,53,69,0.04);">
                    <div class="card-header-title" style="color:var(--danger);"><i class="fas fa-exclamation-triangle"></i>
                        Danger Zone</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST">
                        @csrf @method('DELETE')
                        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.8rem;">
                            This will permanently delete the teacher and their login account.
                        </p>
                        <button type="submit" class="btn-block btn btn-danger"
                            onclick="return confirm('Permanently remove {{ addslashes($teacher->full_name) }}?')">
                            <i class="fas fa-trash-alt"></i> Remove Teacher
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
