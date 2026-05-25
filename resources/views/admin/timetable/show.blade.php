@extends('layouts.app')
@section('title', 'Timetable')
@section('page-title', 'Timetable')

@section('content')

{{-- Print-only header --}}
<div class="print-only"
     style="display:none; text-align:center; margin-bottom:1.5rem; padding-bottom:1rem;
            border-bottom:2px solid #1a3c5e;">
    <h2 style="font-family:serif; color:#1a3c5e; margin:0;">
        {{ $timetable->name }}
    </h2>
    <p style="color:#666; margin:5px 0 0; font-size:0.9rem;">
        {{ $timetable->schoolClass->name ?? '' }}
        — Section {{ $timetable->section->name ?? '' }}
        &bull; {{ $timetable->academic_year }}
        &bull; Printed: {{ now()->format('d M, Y') }}
    </p>
</div>
<style>@media print { .print-only { display:block !important; } }</style>

<div class="page-header no-print">
    <div>
        <div class="page-header-title">{{ $timetable->name }}</div>
        <div class="page-header-sub">
            {{ $timetable->schoolClass->name ?? '' }}
            — Section {{ $timetable->section->name ?? '' }}
            &bull; {{ $timetable->academic_year }}
            &bull; {{ $timetable->days_label }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-outline-primary btn btn-sm">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('admin.timetable.edit', $timetable) }}"
           class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i> Edit Schedule
        </a>
        <a href="{{ route('admin.timetable.index') }}"
           class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

@if($periods->isEmpty())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        No time periods defined for this timetable yet.
        <a href="{{ route('admin.timetable.edit', $timetable) }}">Add periods.</a>
    </div>
@else

<div class="card">
    <div class="timetable-grid-wrapper">
        <table class="timetable-grid">
            <thead>
                <tr>
                    <th class="col-period">
                        <i class="fas fa-clock"></i> Period / Time
                    </th>
                    @foreach($days as $day)
                        <th>{{ \App\Models\Timetable::DAY_LABELS[$day] ?? $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $period)
                <tr class="{{ $period->is_break ? 'is-break-row' : '' }}">

                    {{-- Period label column --}}
                    <td class="col-period">
                        <span class="tt-period-label">{{ $period->label }}</span>
                        <span class="tt-period-time">{{ $period->time_range }}</span>
                        <span class="tt-period-dur">{{ $period->duration }}</span>
                        @if($period->is_break)
                            <span class="badge badge-pending"
                                  style="font-size:0.65rem; margin-top:3px; display:inline-block;">
                                Break
                            </span>
                        @endif
                    </td>

                    {{-- Day columns --}}
                    @foreach($days as $day)
                    @php
                        $entry = $grid[$period->id][$day] ?? null;
                        $type  = $entry?->type ?? ($period->is_break ? 'break' : 'free');
                    @endphp
                    <td class="tt-cell">
                        @if($type === 'lesson' && $entry?->subject)
                            <div class="tt-view-cell lesson">
                                <div class="tt-subject-name">
                                    {{ $entry->subject->name }}
                                </div>
                                @if($entry->teacher)
                                <div class="tt-teacher-name">
                                    <i class="fas fa-user-tie" style="font-size:0.68rem;"></i>
                                    {{ $entry->teacher->full_name }}
                                </div>
                                @endif
                            </div>

                        @elseif($type === 'break')
                            <div class="tt-view-cell break">
                                <div class="tt-break-label">
                                    <i class="fas fa-coffee"></i>
                                    {{ $entry?->custom_label ?? $period->label }}
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

@if($timetable->notes)
<div style="margin-top:1rem; padding:0.75rem 1.1rem; background:var(--light-bg);
            border-radius:var(--radius-sm); font-size:0.86rem; color:var(--text-muted);">
    <i class="fa-sticky-note fas" style="color:var(--accent);"></i>
    {{ $timetable->notes }}
</div>
@endif

@endif
@endsection