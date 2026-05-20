@extends('layouts.app')
@section('title', 'Fee Labels')
@section('page-title', 'Fee Labels')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Fee Labels</div>
        <div class="page-header-sub">Define fee types: Tuition, Exam, Registration, Supplementary, etc.</div>
    </div>
</div>

<div class="row">
    <!-- Add Label -->
    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-plus-circle"></i> Add Fee Label</div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.fee.labels.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-form">
                        <label class="form-label">Label Name *</label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}" placeholder="e.g. Tuition Fee, Exam Fee">
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-form">
                        <label class="form-label">Frequency *</label>
                        <select name="frequency" class="form-select {{ $errors->has('frequency') ? 'is-invalid' : '' }}">
                            <option value="">-- Select --</option>
                            <option value="monthly"  {{ old('frequency') === 'monthly'  ? 'selected' : '' }}>Monthly (Recurring)</option>
                            <option value="yearly"   {{ old('frequency') === 'yearly'   ? 'selected' : '' }}>Yearly / Annual</option>
                            <option value="one_time" {{ old('frequency') === 'one_time' ? 'selected' : '' }}>One-Time</option>
                        </select>
                        @error('frequency')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        <small style="color:var(--text-muted); font-size:0.79rem; margin-top:4px; display:block;">
                            Monthly = billed each month &bull; Yearly = once a year &bull; One-Time = e.g. Admission
                        </small>
                    </div>
                    <button type="submit" class="btn-block btn btn-primary">
                        <i class="fas fa-save"></i> Add Label
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- List -->
    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-tags"></i> All Fee Labels</div>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr><th>#</th><th>Label Name</th><th>Frequency</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($labels as $label)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $label->name }}</strong></td>
                            <td>
                                <span class="badge {{ $label->frequency_badge_class }}">
                                    {{ $label->frequency_label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $label->is_active ? 'badge-approved' : 'badge-rejected' }}">
                                    {{ $label->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn-outline-primary btn btn-sm"
                                            onclick="openEditLabel({{ $label->id }}, '{{ addslashes($label->name) }}', '{{ $label->frequency }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.fee.labels.toggle', $label) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-outline-secondary btn btn-sm">
                                            <i class="fas fa-{{ $label->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.fee.labels.destroy', $label) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-outline-danger btn btn-sm"
                                                onclick="return confirm('Delete label {{ addslashes($label->name) }}?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center; color:var(--text-muted); padding:2rem;">
                            No labels yet. Add your first fee label.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius); padding:1.8rem; width:100%; max-width:420px; box-shadow:var(--shadow-md);">
        <h3 style="font-family:var(--font-display); color:var(--primary); margin-bottom:1.2rem;">Edit Fee Label</h3>
        <form id="edit-form" method="POST" novalidate>
            @csrf @method('PUT')
            <div class="mb-form">
                <label class="form-label">Label Name *</label>
                <input type="text" name="name" id="edit-name" class="form-control">
            </div>
            <div class="mb-form">
                <label class="form-label">Frequency *</label>
                <select name="frequency" id="edit-frequency" class="form-select">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly / Annual</option>
                    <option value="one_time">One-Time</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                <button type="button" class="btn-outline-secondary btn" onclick="document.getElementById('edit-modal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditLabel(id, name, freq) {
    document.getElementById('edit-form').action = '/admin/fee/labels/' + id;
    document.getElementById('edit-name').value  = name;
    document.getElementById('edit-frequency').value = freq;
    document.getElementById('edit-modal').style.display = 'flex';
}
</script>
@endsection