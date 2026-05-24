@extends('layouts.app')
@section('title', 'Teacher Schedule')
@section('page-title', 'Teacher Schedule')

@section('content')
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
        <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back to Profile
        </a>
    </div>
</div>

@php
    $hasAnyEntry = collect($grid)->flatten()->filter()->isNotEmpty();
@endphp

@if(!$hasAnyEntry)
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        {{ $teacher->full_name }} has no lessons assigned in any active timetable.
    </div>
@else

<div class="card">
    <div class="timetable-grid-wrapper">
        <table class="timetable-grid">
            <thead>
                <tr>
                    <th class="period-col"><i class="fas fa-clock"></i> Period</th>
                    @foreach($allDays as $key => $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $period)
                @php
                    $rowHasLesson = collect($grid[$period->id] ?? [])->filter(fn($e) => $e !== null)->isNotEmpty();
                @endphp
                <tr>
                    <td class="period-info">
                        <span class="period-info-label">{{ $period->label }}</span>
                        <span class="period-info-time">{{ $period->time_range }}</span>
                        <span class="period-info-duration">{{ $period->duration }}</span>
                    </td>

                    @foreach(array_keys($allDays) as $dayKey)
                    @php
                        $entry = $grid[$period->id][$dayKey] ?? null;
                    @endphp
                    <td class="tt-cell">
                        @if($entry && $entry->type === 'lesson')
                            <div class="tt-cell-content lesson">
                                <div class="tt-subject">
                                    {{ $entry->subject->name ?? '—' }}
                                </div>
                                <div class="tt-teacher" style="color:var(--primary-light);">
                                    <i class="fa-layer-group fas" style="font-size:0.7rem;"></i>
                                    {{ $entry->timetable->schoolClass->name ?? '' }}
                                    @if($entry->timetable->section)
                                        — {{ $entry->timetable->section->name }}
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="tt-cell-content free">—</div>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endif

<!-- Subject Summary -->
<div class="card" style="margin-top:1.4rem;">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-book"></i> Assigned Subjects
        </div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Subject</th><th>Class</th><th>Section</th><th>Timetable</th></tr>
            </thead>
            <tbody>
                @php
                    $assignedEntries = collect($grid)
                        ->flatten()
                        ->filter(fn($e) => $e && $e->type === 'lesson')
                        ->unique(fn($e) => $e->timetable_id . '-' . $e->subject_id);
                @endphp
                @forelse($assignedEntries as $e)
                <tr>
                    <td><strong>{{ $e->subject->name ?? '—' }}</strong></td>
                    <td>{{ $e->timetable->schoolClass->name ?? '—' }}</td>
                    <td>{{ $e->timetable->section->name ?? '—' }}</td>
                    <td>{{ $e->timetable->name ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:var(--text-muted); padding:1.5rem;">
                        No lessons assigned.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection