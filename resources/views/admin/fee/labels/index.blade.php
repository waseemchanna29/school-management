@extends('layouts.app')
@section('title', 'Fee Labels')
@section('page-title', 'Fee Labels')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Fee Labels</div>
        <div class="page-header-sub">
            Define reusable fee names — e.g. Tuition Fee, Exam Fee, Transport Fee.
            Labels are assigned when building a fee structure.
        </div>
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
                        <input type="text" name="name"
                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}"
                               placeholder="e.g. Tuition Fee, Exam Fee, Transport Fee">
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <button type="submit" class="btn-block btn btn-primary">
                        <i class="fas fa-save"></i> Add Label
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-2 card" style="border-left:4px solid var(--accent);">
            <div class="card-body">
                <div style="font-weight:700; color:var(--primary); font-size:0.88rem; margin-bottom:0.5rem;">
                    <i class="fas fa-lightbulb" style="color:var(--accent);"></i> How Labels Work
                </div>
                <ul style="color:var(--text-muted); font-size:0.84rem; padding-left:1.2rem; line-height:1.8;">
                    <li>Labels are just names — no frequency</li>
                    <li>Add them to a <strong>Fee Structure</strong> with an amount</li>
                    <li>The structure type (Monthly/Yearly/One-Time) controls billing</li>
                    <li>You can reuse the same label in multiple structures</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- List -->
    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-tags"></i> All Labels</div>
                <span style="font-size:0.83rem; color:var(--text-muted);">{{ $labels->count() }} total</span>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Label Name</th>
                            <th>Used In Structures</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($labels as $label)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $label->name }}</strong></td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $label->structure_items_count }} structure(s)
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
                                            onclick="openEditLabel({{ $label->id }}, '{{ addslashes($label->name) }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.fee.labels.toggle', $label) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-outline-secondary btn btn-sm"
                                                title="{{ $label->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $label->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.fee.labels.destroy', $label) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-outline-danger btn btn-sm"
                                                onclick="return confirm('Delete label \'{{ addslashes($label->name) }}\'?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                                <i class="fas fa-tags" style="font-size:2.5rem; display:block; margin-bottom:0.8rem;"></i>
                                No labels yet. Add your first fee label above.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
            z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius); padding:1.8rem;
                width:100%; max-width:400px; box-shadow:var(--shadow-md);">
        <h3 style="font-family:var(--font-display); color:var(--primary); margin-bottom:1.2rem;">
            Edit Fee Label
        </h3>
        <form id="edit-form" method="POST" novalidate>
            @csrf @method('PUT')
            <div class="mb-form">
                <label class="form-label">Label Name *</label>
                <input type="text" name="name" id="edit-name" class="form-control">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                <button type="button" class="btn-outline-secondary btn"
                        onclick="document.getElementById('edit-modal').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditLabel(id, name) {
    document.getElementById('edit-form').action = '/admin/fee/labels/' + id;
    document.getElementById('edit-name').value  = name;
    document.getElementById('edit-modal').style.display = 'flex';
}
</script>
@endsection