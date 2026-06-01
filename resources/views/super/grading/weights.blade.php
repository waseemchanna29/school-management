@extends('layouts.app')
@section('title', 'Exam Weights')
@section('page-title', 'Exam Type Weights')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Global Exam Type Weights</div>
        <div class="page-header-sub">
            Define how each exam type contributes to the final grade.
            Total should equal 100%.
        </div>
    </div>
    <a href="{{ route('super.grading.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Grading Scales
    </a>
</div>

<div class="row">
    <div class="col-5">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-plus-circle"></i> Add Exam Type</div>
            </div>
            <div class="card-body">
                <form action="{{ route('super.grading.weights.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-form">
                        <label class="form-label">Exam Type Key *</label>
                        <input type="text" name="exam_type" class="form-control"
                               placeholder="e.g. class_test, quiz, final"
                               value="{{ old('exam_type') }}">
                        <small style="color:var(--text-muted); font-size:0.78rem;">
                            Lowercase, underscores only. Used internally.
                        </small>
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Display Label *</label>
                        <input type="text" name="label" class="form-control"
                               placeholder="e.g. Class Test, Final Exam"
                               value="{{ old('label') }}">
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Weight (%) *</label>
                        <input type="number" name="weight" class="form-control"
                               placeholder="e.g. 40" min="1" max="100"
                               value="{{ old('weight') }}">
                    </div>
                    <button type="submit" class="btn-block btn btn-primary">
                        <i class="fas fa-save"></i> Add
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-7">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-balance-scale"></i> Defined Weights</div>
                <div style="font-size:0.88rem; font-weight:700;
                            color:{{ $totalWeight == 100 ? 'var(--success)' : 'var(--danger)' }};">
                    Total: {{ $totalWeight }}%
                    @if($totalWeight != 100)
                        <span style="font-size:0.77rem; font-weight:400;">(should be 100%)</span>
                    @endif
                </div>
            </div>

            @forelse($weights as $weight)
            <div class="grade-row">
                <div style="flex:1;">
                    <div style="font-weight:700; font-size:0.9rem; color:var(--primary);">
                        {{ $weight->label }}
                    </div>
                    <div style="font-size:0.77rem; color:var(--text-muted);">
                        Key: <code>{{ $weight->exam_type }}</code>
                    </div>
                </div>

                {{-- Weight bar --}}
                <div style="width:120px;">
                    <div class="perf-bar-wrap">
                        <div class="perf-bar-fill"
                             style="width:{{ $weight->weight }}%; background:var(--primary-light);"></div>
                    </div>
                    <div style="font-size:0.78rem; color:var(--text-muted); text-align:center; margin-top:2px;">
                        {{ $weight->weight }}%
                    </div>
                </div>

                <button class="btn-outline-primary btn btn-sm"
                        onclick="openEditWeight(
                            {{ $weight->id }},
                            '{{ addslashes($weight->label) }}',
                            {{ $weight->weight }}
                        )">
                    <i class="fas fa-edit"></i>
                </button>

                <form action="{{ route('super.grading.weights.destroy', $weight) }}"
                      method="POST"
                      data-confirm="Delete exam type '{{ addslashes($weight->label) }}'?"
                      data-type="danger" data-title="Delete Exam Type">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-outline-danger btn btn-sm">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </div>
            @empty
            <div style="padding:2rem; text-align:center; color:var(--text-muted);">
                No exam types yet.
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div id="edit-weight-modal"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
            z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius); padding:1.8rem;
                width:100%; max-width:380px; box-shadow:var(--shadow-md);">
        <h3 style="font-family:var(--font-display); color:var(--primary); margin-bottom:1.2rem;">
            Edit Weight
        </h3>
        <form id="edit-weight-form" method="POST">
            @csrf @method('PUT')
            <div class="mb-form">
                <label class="form-label">Label *</label>
                <input type="text" name="label" id="ew-label" class="form-control">
            </div>
            <div class="mb-form">
                <label class="form-label">Weight (%) *</label>
                <input type="number" name="weight" id="ew-weight" class="form-control"
                       min="1" max="100">
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
    const base = '{{ route("super.grading.weights.update", ["examTypeWeight" => "__ID__"]) }}';
    document.getElementById('edit-weight-form').action = base.replace('__ID__', id);
    document.getElementById('ew-label').value  = label;
    document.getElementById('ew-weight').value = weight;
    document.getElementById('edit-weight-modal').style.display = 'flex';
}
</script>
@endsection