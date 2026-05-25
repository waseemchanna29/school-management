<?php

namespace App\Http\Controllers\Admin\Fee;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\FeeScheduler;
use App\Models\FeeSchedulerItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeSchedulerController extends Controller
{
    public function index()
    {
        $schedulers = FeeScheduler::where('campus_id', CampusContext::id())
            ->withCount('studentSchedulers')
            ->with('items')
            ->latest()
            ->get();

        return view('admin.fee.schedulers.index', compact('schedulers'));
    }

    public function create()
    {
        return view('admin.fee.schedulers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.label'     => ['required', 'string', 'max:150'],
            'items.*.amount'    => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request) {
            $scheduler = FeeScheduler::create([
                'campus_id'   => CampusContext::id(),
                'name'        => $request->name,
                'description' => $request->description,
                'is_active'   => true,
            ]);

            foreach ($request->items as $i => $item) {
                FeeSchedulerItem::create([
                    'fee_scheduler_id' => $scheduler->id,
                    'label'            => $item['label'],
                    'amount'           => $item['amount'],
                    'sort_order'       => $i,
                ]);
            }
        });

        return redirect()->route('admin.fee.schedulers.index')
            ->with('success', "Scheduler \"{$request->name}\" created.");
    }

    public function show(FeeScheduler $scheduler)
    {
        $this->gate($scheduler);
        $scheduler->load(['items', 'studentSchedulers.student']);
        return view('admin.fee.schedulers.show', compact('scheduler'));
    }

    public function edit(FeeScheduler $scheduler)
    {
        $this->gate($scheduler);
        $scheduler->load('items');
        return view('admin.fee.schedulers.edit', compact('scheduler'));
    }

    public function update(Request $request, FeeScheduler $scheduler)
    {
        $this->gate($scheduler);

        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.label'     => ['required', 'string', 'max:150'],
            'items.*.amount'    => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $scheduler) {
            $scheduler->update([
                'name'        => $request->name,
                'description' => $request->description,
            ]);

            $scheduler->items()->delete();

            foreach ($request->items as $i => $item) {
                FeeSchedulerItem::create([
                    'fee_scheduler_id' => $scheduler->id,
                    'label'            => $item['label'],
                    'amount'           => $item['amount'],
                    'sort_order'       => $i,
                ]);
            }
        });

        return redirect()->route('admin.fee.schedulers.index')
            ->with('success', "Scheduler updated.");
    }

    public function destroy(FeeScheduler $scheduler)
    {
        $this->gate($scheduler);

        if ($scheduler->studentSchedulers()->count() > 0) {
            return back()->with('error', 'Cannot delete — scheduler is assigned to students.');
        }

        $scheduler->items()->delete();
        $scheduler->delete();

        return redirect()->route('admin.fee.schedulers.index')
            ->with('success', 'Scheduler deleted.');
    }

    public function toggle(FeeScheduler $scheduler)
    {
        $this->gate($scheduler);
        $scheduler->update(['is_active' => !$scheduler->is_active]);
        return back()->with('success', 'Scheduler status updated.');
    }

    private function gate(FeeScheduler $s): void
    {
        if ($s->campus_id !== CampusContext::id()) abort(403);
    }
}