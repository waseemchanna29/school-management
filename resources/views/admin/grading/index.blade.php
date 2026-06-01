@extends('layouts.app')
@section('title', 'Grading System')
@section('page-title', 'Grading System')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Campus Grading System</div>
        <div class="page-header-sub">
            Manage grade scale and exam weights for this campus.
            If no custom scale exists, the global default applies.
        </div>
    </div>
</div>

<div class="row">

    {{-- Grade Scale --}}
    <div class="col-7">
        <div class="mb-3 card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-star-half-alt"></i>
                    @if($campusScale)
                        Campus Grade Scale
                        <span class="badge badge-approved" style="font-size:0.7rem;">Custom</span>
                    @else
                        Using Global Default
                        <span class="badge badge-info" style="font-size:0.7rem;">Global</span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    @if($campusScale)
                        <a href="{{ route('admin.grading.edit', $campusScale) }}"
                           class="btn-outline-primary btn btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('admin.grading.destroy', $campusScale) }}"
                              method="POST"
                              data-confirm="Remove custom scale? Global default will apply."
                              data-type="warning" data-title="Remove Custom Scale">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-outline-danger btn btn-sm">
                                <i class="fas fa-undo"></i> Reset to Global
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.grading.copy-scale') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-copy"></i> Copy & Customize
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if($activeScale)
            <div>
                @foreach($activeScale->items as $item)
                <div class="grade-row">
                    <div class="grade-color-dot"
                         style="background:{{ $item->color ?? '#6c7a8d' }};"></div>
                    <div style="flex:1;">
                        <span class="grade-badge" style="{{ $item->color_style }}">
                            {{ $item->grade }}
                        </span>
                    </div>
                    <div style="font-size:0.85rem; color:var(--text-muted); min-width:120px;">
                        {{ $item->min_marks }}% – {{ $item->max_marks }}%
                    </div>
                    <div style="font-size:0.85rem; font-weight:700; min-width:60px;">
                        GPA {{ number_format($item->gpa, 2) }}
                    </div>
                    <div style="font-size:0.82rem; color:var(--text-muted);">
                        {{ $item->description }}
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div style="padding:2rem; text-align:center; color:var(--text-muted);">
                No grade scale found. Contact super admin to create a global default.
            </div>
            @endif
        </div>
    </div>

    {{-- Exam Weights --}}
    <div class="col-5">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-balance-scale"></i>
                    @if($campusWeights->isNotEmpty())
                        Campus Exam Weights
                        <span class="badge badge-approved" style="font-size:0.7rem;">Custom</span>
                    @else
                        Using Global Weights
                        <span class="badge badge-info" style="font-size:0.7rem;">Global</span>
                    @endif
                </div>
                @if($campusWeights->isEmpty())
                <form action="{{ route('admin.grading.copy-weights') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-copy"></i> Copy & Customize
                    </button>
                </form>
                @endif
            </div>

            @php $displayWeights = $activeWeights; @endphp
            @php $totalW = $displayWeights->sum('weight'); @endphp

            <div style="padding:0.8rem 1.2rem; background:var(--light-bg); border-bottom:1px solid var(--border);
                        display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:0.85rem; color:var(--text-muted);">Total Weight:</span>
                <strong style="color:{{ $totalW == 100 ? 'var(--success)' : 'var(--danger)' }};">
                    {{ $totalW }}%
                </strong>
            </div>

            @foreach($displayWeights as $weight)
            <div class="grade-row">
                <div style="flex:1;">
                    <div style="font-weight:700; font-size:0.88rem;">{{ $weight->label }}</div>
                    <div style="font-size:0.75rem; color:var(--text-muted);">{{ $weight->exam_type }}</div>
                </div>
                <div style="width:100px;">
                    <div class="perf-bar-wrap">
                        <div class="perf-bar-fill"
                             style="width:{{ $weight->weight }}%; background:var(--primary);"></div>
                    </div>
                </div>
                <span style="font-weight:700; font-size:0.9rem; min-width:45px; text-align:right;">
                    {{ $weight->weight }}%
                </span>
                @if($campusWeights->isNotEmpty())
                <button class="btn-outline-primary btn btn-sm"
                        onclick="openEditWeight(
                            {{ $weight->id }},
                            '{{ addslashes($weight->label) }}',
                            {{ $weight->weight }}
                        )">
                    <i class="fas fa-edit"></i>
                </button>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Edit Weight Modal (campus) --}}
@if($campusWeights->isNotEmpty())
<div id="edit-weight-modal"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
            z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius); padding:1.8rem;
                width:100%; max-width:360px; box-shadow:var(--shadow-md);">
        <h3 style="font-family:var(--font-display); color:var(--primary); margin-bottom:1.2rem;">
            Edit Exam Weight
        </h3>
        <form id="edit-weight-form" method="POST">
            @csrf @method('PUT')
            <div class="mb-form">
                <label class="form-label">Label</label>
                <input type="text" name="label" id="ew-label" class="form-control">
            </div>
            <div class="mb-form">
                <label class="form-label">Weight (%)</label>
                <input type="number" name="weight" id="ew-weight" class="form-control" min="1" max="100">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                <button type="button" class="btn-outline-secondary btn"
                        onclick="document.getElementById('edit-weight-modal').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
<script>
function openEditWeight(id, label, weight) {
    const base = '{{ route("admin.grading.weights.update", ["weight" => "__ID__"]) }}';
    document.getElementById('edit-weight-form').action = base.replace('__ID__', id);
    document.getElementById('ew-label').value  = label;
    document.getElementById('ew-weight').value = weight;
    document.getElementById('edit-weight-modal').style.display = 'flex';
}
</script>
@endif
@endsection