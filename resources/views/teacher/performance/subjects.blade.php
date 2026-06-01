@extends('layouts.teacher')
@section('title', 'Enter Marks')
@section('page-title', 'Enter Marks')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Enter Student Marks</div>
        <div class="page-header-sub">
            Select a subject to enter marks for your assigned students
        </div>
    </div>
    <a href="{{ route('teacher.performance.history') }}" class="btn-outline-primary btn btn-sm">
        <i class="fas fa-history"></i> View History
    </a>
</div>

{{-- Exam Weights Summary --}}
<div class="mb-3 card" style="border-left:4px solid var(--accent);">
    <div class="card-body">
        <div style="font-weight:700; color:var(--primary); margin-bottom:0.8rem; font-size:0.88rem;">
            <i class="fas fa-balance-scale" style="color:var(--accent);"></i>
            Exam Type Weights Applied
        </div>
        <div style="display:flex; gap:1.2rem; flex-wrap:wrap;">
            @foreach($weights as $weight)
            <div style="text-align:center;">
                <div style="font-weight:700; font-size:1.1rem; color:var(--primary);">
                    {{ $weight->weight }}%
                </div>
                <div style="font-size:0.77rem; color:var(--text-muted);">
                    {{ $weight->label }}
                </div>
            </div>
            @endforeach
            <div style="text-align:center; padding-left:1rem; border-left:1px solid var(--border);">
                <div style="font-weight:700; font-size:1.1rem;
                            color:{{ $weights->sum('weight') == 100 ? 'var(--success)' : 'var(--danger)' }};">
                    {{ $weights->sum('weight') }}%
                </div>
                <div style="font-size:0.77rem; color:var(--text-muted);">Total</div>
            </div>
        </div>
    </div>
</div>

@if($subjects->isEmpty())
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    No subjects assigned to you yet. Contact admin to assign subjects.
</div>
@else

<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:1.2rem;">
    @foreach($subjects as $subject)
    <div class="subject-perf-card">
        <div class="subject-perf-card-top">
            <div style="font-weight:700; color:var(--primary); font-size:1rem; margin-bottom:3px;">
                {{ $subject->name }}
            </div>
            <div style="font-size:0.82rem; color:var(--text-muted);">
                <i class="fa-layer-group fas"></i>
                {{ $subject->schoolClass->name ?? '—' }}
                &bull; Code: {{ $subject->code }}
            </div>
        </div>
        <div class="subject-perf-card-body">
            <div style="font-size:0.82rem; color:var(--text-muted); margin-bottom:0.8rem;">
                Select term and exam type to enter marks:
            </div>

            <form method="GET" action="{{ route('teacher.performance.enter-marks', $subject) }}">
                <div class="mb-form">
                    <label class="form-label" style="font-size:0.79rem;">Term</label>
                    <select name="term" class="form-select">
                        @foreach($terms as $num => $label)
                            <option value="{{ $num }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-form">
                    <label class="form-label" style="font-size:0.79rem;">Exam Type</label>
                    <select name="exam_type" class="form-select">
                        @foreach($weights as $weight)
                            <option value="{{ $weight->exam_type }}">
                                {{ $weight->label }} ({{ $weight->weight }}%)
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-block btn btn-primary">
                    <i class="fas fa-pen-ruler"></i> Enter Marks
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection