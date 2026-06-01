@extends('layouts.app')
@section('title', 'Student Profile')
@section('page-title', 'Student Profile')

@section('content')
    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            @if ($student->photo)
                <img src="{{ Storage::url($student->photo) }}" class="profile-avatar">
            @else
                <div class="profile-avatar-placeholder">{{ strtoupper(substr($student->full_name, 0, 1)) }}</div>
            @endif
            <div>
                <div class="page-header-title">{{ $student->full_name }}</div>
                <div class="d-flex align-items-center gap-2 page-header-sub" style="flex-wrap:wrap;">
                    <code>{{ $student->roll_number }}</code>
                    <span style="color:var(--border);">|</span>
                    <code>{{ $student->gr_number }}</code>
                    <span style="color:var(--border);">|</span>
                    @if ($student->schoolClass) {{ $student->schoolClass->name }}
                        @if ($student->section)
                            – Section {{ $student->section->name }}
                        @endif
                    @endif
                    <span style="color:var(--border);">|</span>
                    <span class="badge {{ $student->status_badge_class }}">{{ ucfirst($student->status) }}</span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            {{-- Add to the action buttons at top of student show page --}}
            <a href="{{ route('admin.fee.student.show', $student) }}" class="btn-outline-primary btn btn-sm">
                <i class="fas fa-file-invoice-dollar"></i> Manage Fees
            </a>
            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.students.index') }}" class="btn-outline-secondary btn btn-sm">
                <i class="fa-arrow-left fas"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-8">
            <!-- Personal Info -->
            <div class="mb-2 card">
                <div class="card-header">
                    <div class="card-header-title"><i class="fas fa-id-card"></i> Personal Information</div>
                </div>
                <div class="card-body">
                    <div class="profile-meta-grid">
                        <div><span class="profile-meta-label">Full Name</span><span
                                class="profile-meta-value">{{ $student->full_name }}</span></div>
                        <div><span class="profile-meta-label">Father's Name</span><span
                                class="profile-meta-value">{{ $student->father_name }}</span></div>
                        <div><span class="profile-meta-label">Mother's Name</span><span
                                class="profile-meta-value">{{ $student->mother_name }}</span></div>
                        <div><span class="profile-meta-label">B-Form / CNIC</span><span
                                class="profile-meta-value">{{ $student->cnic ?? '—' }}</span></div>
                        <div><span class="profile-meta-label">Gender</span><span
                                class="profile-meta-value">{{ $student->gender }}</span></div>
                        <div><span class="profile-meta-label">Date of Birth</span><span
                                class="profile-meta-value">{{ $student->date_of_birth->format('d M, Y') }} (Age:
                                {{ $student->age }})</span></div>
                        <div><span class="profile-meta-label">Blood Group</span><span
                                class="profile-meta-value">{{ $student->blood_group ?? '—' }}</span></div>
                        <div><span class="profile-meta-label">Religion</span><span
                                class="profile-meta-value">{{ $student->religion ?? '—' }}</span></div>
                        <div><span class="profile-meta-label">Nationality</span><span
                                class="profile-meta-value">{{ $student->nationality }}</span></div>
                        <div><span class="profile-meta-label">Phone</span><span
                                class="profile-meta-value">{{ $student->phone ?? '—' }}</span></div>
                        <div><span class="profile-meta-label">Email (Login)</span><span
                                class="profile-meta-value">{{ $student->user->email }}</span></div>
                        <div><span class="profile-meta-label">City / District</span><span
                                class="profile-meta-value">{{ $student->city }}, {{ $student->district }},
                                {{ $student->province }}</span></div>
                        <div style="grid-column:1/-1;"><span class="profile-meta-label">Address</span><span
                                class="profile-meta-value">{{ $student->address }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Admission -->
            <div class="mb-2 card">
                <div class="card-header">
                    <div class="card-header-title"><i class="fas fa-school"></i> Admission Details</div>
                </div>
                <div class="card-body">
                    <div class="profile-meta-grid">
                        <div><span class="profile-meta-label">Roll Number</span><span
                                class="profile-meta-value"><code>{{ $student->roll_number }}</code></span></div>
                        <div><span class="profile-meta-label">GR Number</span><span
                                class="profile-meta-value"><code>{{ $student->gr_number }}</code></span></div>
                        <div><span class="profile-meta-label">Class</span><span
                                class="profile-meta-value">{{ $student->schoolClass->name ?? '—' }}</span></div>
                        <div><span class="profile-meta-label">Section</span><span
                                class="profile-meta-value">{{ $student->section->name ?? '—' }}</span></div>
                        <div><span class="profile-meta-label">Admission Date</span><span
                                class="profile-meta-value">{{ $student->admission_date->format('d M, Y') }}</span></div>
                        <div><span class="profile-meta-label">Previous School</span><span
                                class="profile-meta-value">{{ $student->previous_school ?? '—' }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Education Records -->
            @if ($student->educationRecords->count())
                <div class="mb-2 card">
                    <div class="card-header">
                        <div class="card-header-title"><i class="fas fa-graduation-cap"></i> Previous Education</div>
                    </div>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>Institution</th>
                                    <th>Board</th>
                                    <th>Year</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($student->educationRecords as $edu)
                                    <tr>
                                        <td><strong>{{ $edu->level }}</strong></td>
                                        <td>{{ $edu->institution_name }}</td>
                                        <td>{{ $edu->board_university ?? '—' }}</td>
                                        <td>{{ $edu->passing_year }}</td>
                                        <td>{{ $edu->obtained_marks ?? '—' }} / {{ $edu->total_marks ?? '—' }}</td>
                                        <td>
                                            @if ($edu->grade_division)
                                                <span class="badge badge-approved">{{ $edu->grade_division }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-4">
            <!-- Parent Info -->
            @if ($student->parentRecord)
                <div class="mb-2 card">
                    <div class="card-header">
                        <div class="card-header-title"><i class="fas fa-users"></i> Parent Info</div>
                    </div>
                    <div class="card-body">
                        <div style="margin-bottom:1rem;">
                            <div
                                style="font-weight:700; font-size:0.8rem; color:var(--accent); text-transform:uppercase; letter-spacing:1px; margin-bottom:0.5rem;">
                                Father</div>
                            <div><span class="profile-meta-label">Name</span><span
                                    class="profile-meta-value">{{ $student->parentRecord->father_full_name }}</span></div>
                            <div style="margin-top:4px;"><span class="profile-meta-label">CNIC</span><span
                                    class="profile-meta-value">{{ $student->parentRecord->father_cnic ?? '—' }}</span>
                            </div>
                            <div style="margin-top:4px;"><span class="profile-meta-label">Phone</span><span
                                    class="profile-meta-value">{{ $student->parentRecord->father_phone }}</span></div>
                            <div style="margin-top:4px;"><span class="profile-meta-label">Occupation</span><span
                                    class="profile-meta-value">{{ $student->parentRecord->father_occupation ?? '—' }}</span>
                            </div>
                        </div>
                        <div style="border-top:1px dashed var(--border); padding-top:1rem; margin-bottom:1rem;">
                            <div
                                style="font-weight:700; font-size:0.8rem; color:var(--primary-light); text-transform:uppercase; letter-spacing:1px; margin-bottom:0.5rem;">
                                Mother</div>
                            <div><span class="profile-meta-label">Name</span><span
                                    class="profile-meta-value">{{ $student->parentRecord->mother_full_name }}</span></div>
                            <div style="margin-top:4px;"><span class="profile-meta-label">Phone</span><span
                                    class="profile-meta-value">{{ $student->parentRecord->mother_phone ?? '—' }}</span>
                            </div>
                            <div style="margin-top:4px;"><span class="profile-meta-label">Occupation</span><span
                                    class="profile-meta-value">{{ $student->parentRecord->mother_occupation ?? '—' }}</span>
                            </div>
                        </div>
                        @if ($student->parentRecord->guardian_name)
                            <div style="border-top:1px dashed var(--border); padding-top:1rem;">
                                <div
                                    style="font-weight:700; font-size:0.8rem; color:var(--success); text-transform:uppercase; letter-spacing:1px; margin-bottom:0.5rem;">
                                    Guardian</div>
                                <div><span class="profile-meta-label">Name</span><span
                                        class="profile-meta-value">{{ $student->parentRecord->guardian_name }}</span>
                                </div>
                                <div style="margin-top:4px;"><span class="profile-meta-label">Relation</span><span
                                        class="profile-meta-value">{{ $student->parentRecord->guardian_relation ?? '—' }}</span>
                                </div>
                                <div style="margin-top:4px;"><span class="profile-meta-label">Phone</span><span
                                        class="profile-meta-value">{{ $student->parentRecord->guardian_phone ?? '—' }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Danger Zone -->
            <div class="card" style="border-color:rgba(220,53,69,0.25);">
                <div class="card-header" style="background:rgba(220,53,69,0.04);">
                    <div class="card-header-title" style="color:var(--danger);"><i
                            class="fas fa-exclamation-triangle"></i> Danger Zone</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.students.destroy', $student) }}" method="POST"  data-confirm="Permanently remove {{ addslashes($student->full_name) }}?" data-type="danger" data-title="Delete">
                        @csrf @method('DELETE')
                        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.8rem;">
                            Permanently removes student and login account.
                        </p>
                        <button type="submit" class="btn-block btn btn-danger"
                          >
                            <i class="fas fa-trash-alt"></i> Remove Student
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
