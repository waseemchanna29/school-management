@extends('layouts.app')
@section('title', 'Grading System')
@section('page-title', 'Grading System')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Global Grading Scales</div>
        <div class="page-header-sub">Define grade scales that campuses can use or copy</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('super.grading.weights') }}" class="btn-outline-primary btn">
            <i class="fas fa-balance-scale"></i> Exam Weights
        </a>
        <a href="{{ route('super.grading.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> New Scale
        </a>
    </div>
</div>

@forelse($scales as $scale)
<div class="mb-3 card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-star-half-alt" style="color:var(--accent);"></i>
            {{ $scale->name }}
            @if($scale->is_default)
                <span class="badge badge-approved" style="font-size:0.72rem; margin-left:6px;">
                    Global Default
                </span>
            @endif
            @if($scale->campus_id)
                <span class="badge badge-info" style="font-size:0.72rem;">
                    Campus: {{ $scale->campus->name ?? '' }}
                </span>
            @else
                <span class="badge badge-primary" style="font-size:0.72rem;">Global</span>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('super.grading.edit', $scale) }}"
               class="btn-outline-primary btn btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('super.grading.destroy', $scale) }}"
                  method="POST"
                  data-confirm="Delete grade scale '{{ addslashes($scale->name) }}'?"
                  data-type="danger" data-title="Delete Scale">
                @csrf @method('DELETE')
                <button type="submit" class="btn-outline-danger btn btn-sm">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </form>
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.87rem;">
            <thead>
                <tr style="background:var(--light-bg);">
                    <th style="padding:0.6rem 1rem; text-align:left; color:var(--primary); font-weight:700; font-size:0.78rem; text-transform:uppercase;">Grade</th>
                    <th style="padding:0.6rem 1rem; text-align:center; color:var(--primary); font-weight:700; font-size:0.78rem; text-transform:uppercase;">Min %</th>
                    <th style="padding:0.6rem 1rem; text-align:center; color:var(--primary); font-weight:700; font-size:0.78rem; text-transform:uppercase;">Max %</th>
                    <th style="padding:0.6rem 1rem; text-align:center; color:var(--primary); font-weight:700; font-size:0.78rem; text-transform:uppercase;">GPA</th>
                    <th style="padding:0.6rem 1rem; color:var(--primary); font-weight:700; font-size:0.78rem; text-transform:uppercase;">Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scale->items as $item)
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:0.6rem 1rem;">
                        <span class="grade-badge" style="{{ $item->color_style }}">
                            {{ $item->grade }}
                        </span>
                    </td>
                    <td style="padding:0.6rem 1rem; text-align:center; font-weight:600;">
                        {{ $item->min_marks }}%
                    </td>
                    <td style="padding:0.6rem 1rem; text-align:center; font-weight:600;">
                        {{ $item->max_marks }}%
                    </td>
                    <td style="padding:0.6rem 1rem; text-align:center;">
                        <strong>{{ number_format($item->gpa, 2) }}</strong>
                    </td>
                    <td style="padding:0.6rem 1rem; color:var(--text-muted);">
                        {{ $item->description ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@empty
<div class="card">
    <div class="card-body" style="text-align:center; padding:4rem;">
        <i class="fas fa-star-half-alt" style="font-size:3rem; color:var(--border); display:block; margin-bottom:1rem;"></i>
        <h3 style="color:var(--text-muted);">No grade scales yet</h3>
        <p style="color:var(--text-muted); margin-bottom:1.5rem;">Create the global default grade scale first.</p>
        <a href="{{ route('super.grading.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Create Grade Scale
        </a>
    </div>
</div>
@endforelse
@endsection