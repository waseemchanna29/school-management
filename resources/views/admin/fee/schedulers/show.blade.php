@extends('layouts.app')
@section('title', 'Scheduler Details')
@section('page-title', 'Scheduler Details')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">{{ $scheduler->name }}</div>
        <div class="page-header-sub">
            {{ $scheduler->student_schedulers_count }} student(s) assigned &bull;
            <span class="badge {{ $scheduler->is_active ? 'badge-approved' : 'badge-rejected' }}">
                {{ $scheduler->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.fee.schedulers.edit', $scheduler) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('admin.fee.schedulers.index') }}" class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-4">
        <div class="mb-2 card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-list"></i> Fee Items</div>
            </div>
            <div>
                @foreach($scheduler->items as $item)
                <div class="fee-item-line">
                    <div class="fee-item-line-label">{{ $item->label }}</div>
                    <div class="fee-item-line-amount">
                        PKR {{ number_format($item->amount, 0) }}
                    </div>
                </div>
                @endforeach
                <div class="fee-item-line"
                     style="background:var(--primary); color:var(--white); font-weight:700;">
                    <div style="flex:1; font-weight:700;">Total</div>
                    <div style="font-weight:700; color:var(--accent-light); font-size:1rem;">
                        PKR {{ number_format($scheduler->total, 0) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-users"></i> Assigned Students
                </div>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Assigned On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($scheduler->studentSchedulers as $ss)
                        <tr>
                            <td>
                                <strong>{{ $ss->student->full_name }}</strong>
                                <div style="font-size:0.77rem; color:var(--text-muted);">
                                    {{ $ss->student->roll_number }}
                                </div>
                            </td>
                            <td>{{ $ss->student->schoolClass->name ?? '—' }}</td>
                            <td>{{ $ss->assigned_date->format('d M, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.fee.student.show', $ss->student) }}"
                                   class="btn-outline-primary btn btn-sm">
                                    <i class="fas fa-eye"></i> Fee Profile
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4"
                                style="text-align:center; color:var(--text-muted); padding:2rem;">
                                No students assigned yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection