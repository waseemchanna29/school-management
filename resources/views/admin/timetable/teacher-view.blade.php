@extends('layouts.app')
@section('title', 'Teacher Schedule')
@section('page-title', 'Teacher Schedule')

@section('content')

{{-- Print header --}}
<div class="print-only"
     style="display:none; text-align:center; margin-bottom:1.5rem;
            padding-bottom:1rem; border-bottom:2px solid #1a3c5e;">
    <h2 style="font-family:serif; color:#1a3c5e; margin:0;">
        Weekly Teaching Schedule
    </h2>
    <p style="color:#666; margin:5px 0 0; font-size:0.9rem;">
        {{ $teacher->full_name }}
        &bull; {{ $teacher->employee_code }}
        &bull; Printed: {{ now()->format('d M, Y') }}
    </p>
</div>
<style>@media print { .print-only { display:block !important; } }</style>

<div class="page-header no-print">
    <div class="d-flex align-items-center gap-3">
        <div class="profile-avatar-placeholder"
             style="width:52px; height:52px; font-size:1.3rem;">
            {{ strtoupper(substr($teacher->full_name, 0, 1)) }}
        </div>
        <div>
            <div class="page-header-title">{{ $teacher->full_name }}</div>
            <div class="page-header-sub">
                <code>{{ $teacher->employee_code }}</code>
                &bull; {{ $teacher->specialization ?? $teacher->qualification }}
                &bull; Weekly Teaching Schedule
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-outline-primary btn btn-sm">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('admin.teachers.show', $teacher) }}"
           class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back to Profile
        </a>
    </div>
</div>

@php $hasAny = collect($grid)->flatten()->filter()->isNotEmpty(); @endphp

@if(!$hasAny)
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        {{ $teacher->full_name }} has no lessons assigned in any active timetable.
    </div>
@else

<div class="mb-3 card">
    <div class="timetable-grid-wrapper">
        <table class="timetable-grid">
            <thead>
                <tr>
                    <th class="col-period"><i class="fas fa-clock"></i> Period</th>
                    @foreach($days as $dayKey)
                        <th>{{ \App\Models\Timetable::DAY_LABELS[$dayKey] ?? $dayKey }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($allPeriods as $period)
                <tr>
                    <td class="col-period">
                        <span class="tt-period-label">{{ $period->label }}</span>
                        <span class="tt-period-time">{{ $period->time_range }}</span>
                        <span class="tt-period-dur">{{ $period->duration }}</span>
                    </td>
                    @foreach($days as $dayKey)
                    @php
                        $key   = $period->start_time . '|' . $period->label;
                        $entry = $grid[$key][$dayKey] ?? null;
                    @endphp
                    <td class="tt-cell">
                        @if($entry && $entry->type === 'lesson')
                            <div class="tt-view-cell lesson">
                                <div class="tt-subject-name">
                                    {{ $entry->subject->name ?? '—' }}
                                </div>
                                <div class="tt-teacher-name"
                                     style="color:var(--primary-light);">
                                    <i class="fa-layer-group fas" style="font-size:0.68rem;"></i>
                                    {{ $entry->timetable->schoolClass->name ?? '' }}
                                    @if($entry->timetable->section)
                                        – {{ $entry->timetable->section->name }}
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="tt-view-cell free">—</div>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Summary table --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-book"></i> Assigned Subjects Summary
        </div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Timetable</th>
                    <th>Day(s)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $summary = $entries->groupBy(fn($e) => $e->timetable_id . '-' . $e->subject_id);
                @endphp
                @forelse($summary as $group)
                @php $first = $group->first(); @endphp
                <tr>
                    <td><strong>{{ $first->subject->name ?? '—' }}</strong></td>
                    <td>{{ $first->timetable->schoolClass->name ?? '—' }}</td>
                    <td>{{ $first->timetable->section->name ?? '—' }}</td>
                    <td>{{ $first->timetable->name ?? '—' }}</td>
                    <td>
                        <div style="display:flex; gap:3px; flex-wrap:wrap;">
                            @foreach($group->pluck('day')->unique() as $d)
                                <span class="badge badge-primary"
                                      style="font-size:0.7rem;">{{ $d }}</span>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5"
                        style="text-align:center; color:var(--text-muted); padding:1.5rem;">
                        No lessons.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif
@endsection