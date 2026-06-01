@extends('layouts.app')
@section('title', 'Fee Schedulers')
@section('page-title', 'Fee Schedulers')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Fee Schedulers</div>
        <div class="page-header-sub">
            Create fee templates with labels and prices — assign to students
        </div>
    </div>
    <a href="{{ route('admin.fee.schedulers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> New Scheduler
    </a>
</div>

@if($schedulers->isEmpty())
<div class="card">
    <div class="card-body" style="text-align:center; padding:4rem;">
        <i class="fas fa-file-invoice-dollar"
           style="font-size:3rem; color:var(--border); margin-bottom:1rem; display:block;"></i>
        <h3 style="color:var(--text-muted);">No schedulers yet</h3>
        <p style="color:var(--text-muted); margin-bottom:1.5rem;">
            Create your first fee scheduler to start assigning fees to students.
        </p>
        <a href="{{ route('admin.fee.schedulers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Create First Scheduler
        </a>
    </div>
</div>
@else

<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1.2rem;">
    @foreach($schedulers as $scheduler)
    <div class="card" style="overflow:hidden;">
        <div style="height:4px; background:{{ $scheduler->is_active ? 'var(--primary)' : 'var(--border)' }};"></div>
        <div class="card-body">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.5rem; margin-bottom:0.8rem;">
                <div>
                    <strong style="font-size:1rem; color:var(--primary);">{{ $scheduler->name }}</strong>
                    @if($scheduler->description)
                        <div style="font-size:0.82rem; color:var(--text-muted); margin-top:2px;">
                            {{ Str::limit($scheduler->description, 60) }}
                        </div>
                    @endif
                </div>
                <span class="badge {{ $scheduler->is_active ? 'badge-approved' : 'badge-rejected' }}">
                    {{ $scheduler->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <!-- Items preview -->
            <div style="background:var(--light-bg); border-radius:var(--radius-sm);
                        padding:0.6rem 0.9rem; margin-bottom:0.9rem;">
                @foreach($scheduler->items->take(4) as $item)
                <div style="display:flex; justify-content:space-between; font-size:0.82rem;
                            padding:0.2rem 0; border-bottom:1px dotted var(--border);">
                    <span>{{ $item->label }}</span>
                    <strong>PKR {{ number_format($item->amount, 0) }}</strong>
                </div>
                @endforeach
                @if($scheduler->items->count() > 4)
                    <div style="font-size:0.76rem; color:var(--text-muted); margin-top:3px;">
                        + {{ $scheduler->items->count() - 4 }} more items
                    </div>
                @endif
                <div style="display:flex; justify-content:space-between; font-size:0.88rem;
                            font-weight:700; margin-top:0.4rem; color:var(--primary);">
                    <span>Total</span>
                    <span>PKR {{ number_format($scheduler->total, 0) }}</span>
                </div>
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between;
                        font-size:0.82rem; color:var(--text-muted); margin-bottom:0.8rem;">
                <span>
                    <i class="fas fa-users"></i>
                    {{ $scheduler->student_schedulers_count }} student(s) assigned
                </span>
            </div>

            <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                <a href="{{ route('admin.fee.schedulers.show', $scheduler) }}"
                   class="btn-outline-primary btn btn-sm">
                    <i class="fas fa-eye"></i> View
                </a>
                <a href="{{ route('admin.fee.schedulers.edit', $scheduler) }}"
                   class="btn-outline-primary btn btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.fee.schedulers.toggle', $scheduler) }}"
                      method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-outline-secondary btn btn-sm">
                        <i class="fas fa-{{ $scheduler->is_active ? 'pause' : 'play' }}"></i>
                    </button>
                </form>
                <form action="{{ route('admin.fee.schedulers.destroy', $scheduler) }}"
                      method="POST" style="display:inline;" data-confirm="Delete \'{{ addslashes($scheduler->name) }}\" data-type="danger" data-title="Delete">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-outline-danger btn btn-sm"
                           >
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection