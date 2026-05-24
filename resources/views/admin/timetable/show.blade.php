@extends('layouts.app')
@section('title', 'Timetable')
@section('page-title', 'Timetable')

@section('content')
<div class="page-header no-print">
    <div>
        <div class="page-header-title">{{ $timetable->name }}</div>
        <div class="page-header-sub">
            {{ $timetable->schoolClass->name ?? '' }}
            — Section {{ $timetable->section->name ?? '' }}
            &bull; {{ $timetable->academic_year }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-outline-primary btn btn-sm">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('admin.timetable.edit', $timetable) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-th"></i> Edit Schedule
        </a>
        <a href="{{ route('admin.timetable.index') }}" class="btn-outline-secondary btn btn-sm">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

<!-- Print Header (only shows on print) -->
<div style="display:none;" class="print-header">
    <div style="text-align:center; margin-bottom:1rem;">
        <h2 style="font-family:var(--font-display); color:var(--primary); margin:0;">
            {{ $timetable->name }}
        </h2>
        <p style="color:var(--text-muted); margin:4px 0 0;">
            {{ $timetable->schoolClass->name ?? '' }} —
            Section {{ $timetable->section->name ?? '' }} &bull;
            Academic Year: {{ $timetable->academic_year }}
        </p>
        <p style="color:var(--text-muted); font-size:0.85rem; margin:2px 0 0;">
            Generated: {{ now()->format('d M, Y') }}
        </p>
    </div>
</div>

<style>
@media print {
    .print-header { display:block !important; }
}
</style>

<div class="card">
    <div class="timetable-grid-wrapper">
        <table class="timetable-grid">
            <thead>
                <tr>
                    <th class="period-col">
                        <i class="fas fa-clock"></i> Period
                    </th>
                    @foreach($timetable->days as $dayKey)
                        <th>{{ $allDays[$dayKey] ?? $dayKey }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $period)
                <tr class="{{ $period->is_break ? 'break-row' : '' }}">

                    <td class="period-info">
                        <span class="period-info-label">{{ $period->label }}</span>
                        <span class="period-info-time">{{ $period->time_range }}</span>
                        <span class="period-info-duration">{{ $period->duration }}</span>
                    </td>

                    @foreach($timetable->days as $dayKey)
                    @php
                        $entry = $grid[$period->id][$dayKey] ?? null;
                        $type  = $entry?->type ?? ($period->is_break ? 'break' : 'free');
                    @endphp

                    <td class="tt-cell">
                        @if($type === 'lesson' && $entry?->subject)
                            <div class="tt-cell-content lesson">
                                <div class="tt-subject">{{ $entry->subject->name }}</div>
                                @if($entry->teacher)
                                    <div class="tt-teacher">
                                        <i class="fas fa-user-tie" style="font-size:0.7rem;"></i>
                                        {{ $entry->teacher->full_name }}
                                    </div>
                                @endif
                            </div>

                        @elseif($type === 'break')
                            <div class="tt-cell-content break">
                                <div class="tt-break-label">
                                    <i class="fas fa-coffee"></i>
                                    {{ $entry?->custom_label ?? $period->label }}
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

@if($timetable->notes)
<div style="margin-top:1rem; padding:0.8rem 1.2rem; background:var(--light-bg);
            border-radius:var(--radius-sm); font-size:0.87rem; color:var(--text-muted);">
    <i class="fa-sticky-note fas"></i> {{ $timetable->notes }}
</div>
@endif
@endsection