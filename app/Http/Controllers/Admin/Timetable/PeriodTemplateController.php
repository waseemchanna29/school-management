<?php

namespace App\Http\Controllers\Admin\Timetable;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\PeriodTemplate;
use Illuminate\Http\Request;

class PeriodTemplateController extends Controller
{
    public function index()
    {
        $periods = PeriodTemplate::where('campus_id', CampusContext::id())
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->get();

        return view('admin.timetable.periods.index', compact('periods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label'      => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'is_break'   => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        PeriodTemplate::create([
            'campus_id'  => CampusContext::id(),
            'label'      => $request->label,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'is_break'   => $request->boolean('is_break'),
            'sort_order' => $request->sort_order ?? PeriodTemplate::where('campus_id', CampusContext::id())->max('sort_order') + 1,
            'is_active'  => true,
        ]);

        return back()->with('success', "Period \"{$request->label}\" added.");
    }

    public function update(Request $request, PeriodTemplate $periodTemplate)
    {
        $this->authorize($periodTemplate);

        $request->validate([
            'label'      => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'is_break'   => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $periodTemplate->update([
            'label'      => $request->label,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'is_break'   => $request->boolean('is_break'),
            'sort_order' => $request->sort_order ?? $periodTemplate->sort_order,
        ]);

        return back()->with('success', 'Period updated.');
    }

    public function destroy(PeriodTemplate $periodTemplate)
    {
        $this->authorize($periodTemplate);

        if ($periodTemplate->entries()->count() > 0) {
            return back()->with('error', 'Cannot delete — period is used in timetables.');
        }

        $periodTemplate->delete();
        return back()->with('success', 'Period deleted.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer', 'exists:period_templates,id'],
        ]);

        foreach ($request->order as $sortOrder => $id) {
            PeriodTemplate::where('id', $id)
                ->where('campus_id', CampusContext::id())
                ->update(['sort_order' => $sortOrder]);
        }

        return response()->json(['success' => true]);
    }

    private function authorize(PeriodTemplate $p): void
    {
        if ($p->campus_id !== CampusContext::id()) abort(403);
    }
}