@extends('layouts.app')
@section('title', 'Time Periods')
@section('page-title', 'Time Periods')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Time Period Templates</div>
        <div class="page-header-sub">
            Define your campus time slots once — reused across all timetables.
            Drag to reorder.
        </div>
    </div>
    <a href="{{ route('admin.timetable.index') }}" class="btn-outline-secondary btn btn-sm">
        <i class="fa-arrow-left fas"></i> Timetables
    </a>
</div>

<div class="row">
    <!-- Add Period -->
    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-plus-circle"></i> Add Period / Break</div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.timetable.periods.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-form">
                        <label class="form-label">Label *</label>
                        <input type="text" name="label"
                               class="form-control {{ $errors->has('label') ? 'is-invalid' : '' }}"
                               value="{{ old('label') }}"
                               placeholder="e.g. Period 1, Lunch Break, Jummah">
                        @error('label')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        <div class="mb-form col-6">
                            <label class="form-label">Start Time *</label>
                            <input type="time" name="start_time"
                                   class="form-control {{ $errors->has('start_time') ? 'is-invalid' : '' }}"
                                   value="{{ old('start_time') }}">
                            @error('start_time')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-form col-6">
                            <label class="form-label">End Time *</label>
                            <input type="time" name="end_time"
                                   class="form-control {{ $errors->has('end_time') ? 'is-invalid' : '' }}"
                                   value="{{ old('end_time') }}">
                            @error('end_time')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control"
                               value="{{ old('sort_order', $periods->count() + 1) }}"
                               min="0" placeholder="Lower = appears first">
                    </div>
                    <div class="mb-form"
                         style="display:flex; align-items:center; gap:10px; padding:0.7rem;
                                background:var(--light-bg); border-radius:var(--radius-sm);">
                        <input type="checkbox" name="is_break" id="is_break" value="1"
                               {{ old('is_break') ? 'checked' : '' }}
                               style="width:17px; height:17px; accent-color:var(--accent); cursor:pointer;">
                        <label for="is_break" style="cursor:pointer; margin:0;">
                            <strong>This is a Break / Prayer / Assembly</strong>
                            <span style="color:var(--text-muted); font-size:0.82rem; display:block;">
                                Break slots show differently in the timetable grid
                            </span>
                        </label>
                    </div>
                    <button type="submit" class="btn-block btn btn-primary">
                        <i class="fas fa-save"></i> Add Period
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- List -->
    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-clock"></i> All Periods
                </div>
                <span style="font-size:0.82rem; color:var(--text-muted);">
                    {{ $periods->count() }} defined &bull; Drag to reorder
                </span>
            </div>
            <div class="card-body" style="padding:1rem;">
                @if($periods->isEmpty())
                    <div style="text-align:center; padding:2.5rem; color:var(--text-muted);">
                        <i class="fas fa-clock" style="font-size:3rem; display:block; margin-bottom:0.8rem;"></i>
                        No periods defined yet. Add your first period above.
                    </div>
                @else
                    <div id="sortable-periods" style="display:flex; flex-direction:column; gap:0.5rem;">
                        @foreach($periods as $period)
                        <div class="period-chip {{ $period->is_break ? 'is-break' : 'is-lesson' }}"
                             data-id="{{ $period->id }}">
                            <i class="fas fa-grip-vertical" style="color:var(--border); cursor:grab;"></i>

                            <div style="flex:1;">
                                <div class="period-chip-label">{{ $period->label }}</div>
                                <div class="period-chip-time">{{ $period->time_range }}</div>
                            </div>

                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <span class="badge {{ $period->is_break ? 'badge-pending' : 'badge-primary' }}">
                                    {{ $period->is_break ? 'Break' : 'Lesson' }}
                                </span>
                                <span class="period-chip-duration">{{ $period->duration }}</span>

                                <button class="btn-outline-primary btn btn-sm"
                                        onclick="openEditPeriod({{ $period->id }}, '{{ addslashes($period->label) }}', '{{ $period->start_time }}', '{{ $period->end_time }}', {{ $period->is_break ? 'true' : 'false' }}, {{ $period->sort_order }})">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form action="{{ route('admin.timetable.periods.destroy', $period) }}"
                                      method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-outline-danger btn btn-sm"
                                            onclick="return confirm('Delete period \'{{ addslashes($period->label) }}\'?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-period-modal"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
            z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius); padding:1.8rem;
                width:100%; max-width:440px; box-shadow:var(--shadow-md);">
        <h3 style="font-family:var(--font-display); color:var(--primary); margin-bottom:1.2rem;">
            Edit Period
        </h3>
        <form id="edit-period-form" method="POST" novalidate>
            @csrf @method('PUT')
            <div class="mb-form">
                <label class="form-label">Label *</label>
                <input type="text" name="label" id="ep-label" class="form-control">
            </div>
            <div class="row">
                <div class="mb-form col-6">
                    <label class="form-label">Start Time *</label>
                    <input type="time" name="start_time" id="ep-start" class="form-control">
                </div>
                <div class="mb-form col-6">
                    <label class="form-label">End Time *</label>
                    <input type="time" name="end_time" id="ep-end" class="form-control">
                </div>
            </div>
            <div class="mb-form">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" id="ep-sort" class="form-control" min="0">
            </div>
            <div class="mb-form"
                 style="display:flex; align-items:center; gap:10px; padding:0.7rem;
                        background:var(--light-bg); border-radius:var(--radius-sm);">
                <input type="checkbox" name="is_break" id="ep-break" value="1"
                       style="width:17px; height:17px; accent-color:var(--accent); cursor:pointer;">
                <label for="ep-break" style="cursor:pointer; margin:0; font-weight:600;">
                    This is a Break / Prayer / Assembly
                </label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                <button type="button" class="btn-outline-secondary btn"
                        onclick="document.getElementById('edit-period-modal').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditPeriod(id, label, start, end, isBreak, sortOrder) {
    document.getElementById('edit-period-form').action = '/admin/timetable/periods/' + id;
    document.getElementById('ep-label').value  = label;
    document.getElementById('ep-start').value  = start;
    document.getElementById('ep-end').value    = end;
    document.getElementById('ep-break').checked = isBreak;
    document.getElementById('ep-sort').value   = sortOrder;
    document.getElementById('edit-period-modal').style.display = 'flex';
}

// Drag-to-reorder using SortableJS from CDN
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('sortable-periods');
    if (!el) return;

    // Load SortableJS dynamically
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js';
    script.onload = function () {
        Sortable.create(el, {
            handle: '.fa-grip-vertical',
            animation: 150,
            onEnd: function () {
                const order = Array.from(el.querySelectorAll('[data-id]')).map(el => el.dataset.id);
                fetch('{{ route("admin.timetable.periods.reorder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order })
                });
            }
        });
    };
    document.head.appendChild(script);
});
</script>
@endsection