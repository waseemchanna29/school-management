@extends('layouts.app')
@section('title', 'Academic Years')
@section('page-title', 'Academic Years')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Academic Year Management</div>
        <div class="page-header-sub">
            Create and manage academic years for this campus
        </div>
    </div>
</div>

<div class="row">

    {{-- Add Form --}}
    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-plus-circle"></i> Add Academic Year
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.academic-years.store') }}"
                      method="POST" novalidate>
                    @csrf

                    <div class="mb-form">
                        <label class="form-label">Year Name *</label>
                        <input type="text" name="name"
                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}"
                               placeholder="e.g. 2024-2025">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small style="color:var(--text-muted); font-size:0.79rem; display:block; margin-top:3px;">
                            Standard format: YYYY-YYYY (e.g. 2024-2025)
                        </small>
                    </div>

                    <div class="row">
                        <div class="mb-form col-6">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date"
                                   class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}"
                                   value="{{ old('start_date') }}">
                            @error('start_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-form col-6">
                            <label class="form-label">End Date *</label>
                            <input type="date" name="end_date"
                                   class="form-control {{ $errors->has('end_date') ? 'is-invalid' : '' }}"
                                   value="{{ old('end_date') }}">
                            @error('end_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-form">
                        <label class="form-label">Notes (optional)</label>
                        <input type="text" name="notes" class="form-control"
                               value="{{ old('notes') }}"
                               placeholder="e.g. Post-COVID adjusted schedule">
                    </div>

                    <button type="submit" class="btn-block btn btn-primary">
                        <i class="fas fa-save"></i> Create Year
                    </button>
                </form>
            </div>
        </div>

        {{-- Info card --}}
        <div class="mt-2 card"
             style="border-left:4px solid var(--accent);">
            <div class="card-body">
                <div style="font-weight:700; color:var(--primary);
                            font-size:0.87rem; margin-bottom:0.6rem;">
                    <i class="fas fa-lightbulb" style="color:var(--accent);"></i>
                    How It Works
                </div>
                <ul style="color:var(--text-muted); font-size:0.82rem;
                           padding-left:1.1rem; line-height:1.8; margin:0;">
                    <li>Only one year can be <strong>Current</strong> at a time</li>
                    <li><strong>Locked</strong> years are read-only</li>
                    <li>All data (fees, attendance, marks) is filtered by selected year</li>
                    <li>Year switch button appears in the top bar</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- List --}}
    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-calendar-alt"></i> All Academic Years
                </div>
                <span style="font-size:0.83rem; color:var(--text-muted);">
                    {{ $years->count() }} year(s)
                </span>
            </div>

            @forelse($years as $year)
            <div style="padding:1.1rem 1.4rem; border-bottom:1px solid var(--border);
                        display:flex; align-items:center; gap:1rem; flex-wrap:wrap;
                        {{ $year->is_current ? 'background:rgba(37,99,168,0.03);' : '' }}">

                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:0.6rem;
                                margin-bottom:3px; flex-wrap:wrap;">
                        <strong style="font-size:1.05rem; color:var(--primary);">
                            {{ $year->name }}
                        </strong>
                        <span class="badge {{ $year->status_badge_class }}">
                            {{ $year->status_label }}
                        </span>
                        @if($year->is_locked)
                        <span class="badge badge-rejected" style="font-size:0.7rem;">
                            <i class="fas fa-lock"></i> Locked
                        </span>
                        @endif
                    </div>
                    <div style="font-size:0.82rem; color:var(--text-muted);">
                        {{ $year->duration }}
                    </div>
                    @if($year->notes)
                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px; font-style:italic;">
                        {{ $year->notes }}
                    </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div style="display:flex; gap:0.4rem; flex-wrap:wrap; flex-shrink:0;">

                    {{-- Set Current --}}
                    @if(!$year->is_current)
                    <form action="{{ route('admin.academic-years.set-current', $year) }}"
                          method="POST"
                          data-confirm="Set '{{ addslashes($year->name) }}' as the current academic year?"
                          data-type="info"
                          data-title="Set Current Year">
                        @csrf
                        <button type="submit" class="btn-outline-primary btn btn-sm"
                                title="Set as Current">
                            <i class="fas fa-star"></i>
                        </button>
                    </form>
                    @else
                    <span class="btn btn-primary btn-sm" style="cursor:default;">
                        <i class="fas fa-star"></i> Current
                    </span>
                    @endif

                    {{-- Lock / Unlock --}}
                    <form action="{{ route('admin.academic-years.toggle-lock', $year) }}"
                          method="POST"
                          data-confirm="{{ $year->is_locked ? 'Unlock' : 'Lock' }} year '{{ addslashes($year->name) }}'?"
                          data-type="warning"
                          data-title="{{ $year->is_locked ? 'Unlock' : 'Lock' }} Year">
                        @csrf
                        <button type="submit"
                                class="btn {{ $year->is_locked ? 'btn-warning' : 'btn-outline-secondary' }} btn-sm"
                                title="{{ $year->is_locked ? 'Unlock' : 'Lock' }}">
                            <i class="fas fa-{{ $year->is_locked ? 'unlock' : 'lock' }}"></i>
                        </button>
                    </form>

                    {{-- Edit (only if not locked) --}}
                    @if(!$year->is_locked)
                    <button class="btn-outline-primary btn btn-sm"
                            onclick="openEditYear(
                                {{ $year->id }},
                                '{{ addslashes($year->name) }}',
                                '{{ $year->start_date->format('Y-m-d') }}',
                                '{{ $year->end_date->format('Y-m-d') }}',
                                '{{ addslashes($year->notes ?? '') }}'
                            )">
                        <i class="fas fa-edit"></i>
                    </button>
                    @endif

                    {{-- Delete --}}
                    @if(!$year->is_current)
                    <form action="{{ route('admin.academic-years.destroy', $year) }}"
                          method="POST"
                          data-confirm="Delete academic year '{{ addslashes($year->name) }}'? All associated data will be lost."
                          data-type="danger"
                          data-title="Delete Academic Year">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-outline-danger btn btn-sm">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                    @endif

                </div>
            </div>
            @empty
            <div style="padding:3rem; text-align:center; color:var(--text-muted);">
                <i class="fas fa-calendar-alt"
                   style="font-size:3rem; display:block;
                          margin-bottom:1rem; color:var(--border);"></i>
                <h3 style="margin-bottom:0.5rem;">No Academic Years Yet</h3>
                <p style="font-size:0.88rem;">
                    Create your first academic year using the form on the left.
                </p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div id="edit-year-modal"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
            z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius);
                padding:1.8rem; width:100%; max-width:460px; box-shadow:var(--shadow-md);">
        <h3 style="font-family:var(--font-display); color:var(--primary); margin-bottom:1.2rem;">
            Edit Academic Year
        </h3>
        <form id="edit-year-form" method="POST" novalidate>
            @csrf @method('PUT')

            <div class="mb-form">
                <label class="form-label">Year Name *</label>
                <input type="text" name="name" id="ey-name" class="form-control">
            </div>
            <div class="row">
                <div class="mb-form col-6">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="start_date" id="ey-start" class="form-control">
                </div>
                <div class="mb-form col-6">
                    <label class="form-label">End Date *</label>
                    <input type="date" name="end_date" id="ey-end" class="form-control">
                </div>
            </div>
            <div class="mb-form">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" id="ey-notes" class="form-control">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save
                </button>
                <button type="button" class="btn-outline-secondary btn"
                        onclick="document.getElementById('edit-year-modal').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditYear(id, name, start, end, notes) {
    const base = '{{ route("admin.academic-years.update", ["academicYear" => "__ID__"]) }}';
    document.getElementById('edit-year-form').action = base.replace('__ID__', id);
    document.getElementById('ey-name').value  = name;
    document.getElementById('ey-start').value = start;
    document.getElementById('ey-end').value   = end;
    document.getElementById('ey-notes').value = notes;
    document.getElementById('edit-year-modal').style.display = 'flex';
}
</script>
@endsection