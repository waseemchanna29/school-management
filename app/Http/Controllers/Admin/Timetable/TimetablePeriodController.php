<?php

namespace App\Http\Controllers\Admin\Timetable;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\TimetablePeriod;
use Illuminate\Http\Request;

class TimetablePeriodController extends Controller
{
    public function store(Request $request, Timetable $timetable)
    {
        $this->authorizeTimetable($timetable);

        $request->validate([
            'label'      => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'is_break'   => ['nullable', 'boolean'],
        ]);

        $maxOrder = TimetablePeriod::where('timetable_id', $timetable->id)->max('sort_order') ?? -1;

        TimetablePeriod::create([
            'timetable_id' => $timetable->id,
            'label'        => $request->label,
            'start_time'   => $request->start_time,
            'end_time'     => $request->end_time,
            'is_break'     => $request->boolean('is_break'),
            'sort_order'   => $maxOrder + 1,
        ]);

        return back()->with('success', "Period \"{$request->label}\" added.");
    }

    public function update(Request $request, Timetable $timetable, TimetablePeriod $period)
    {
        $this->authorizeTimetable($timetable);
        $this->authorizePeriod($period, $timetable);

        $request->validate([
            'label'      => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'is_break'   => ['nullable', 'boolean'],
        ]);

        $period->update([
            'label'      => $request->label,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'is_break'   => $request->boolean('is_break'),
        ]);

        return back()->with('success', 'Period updated.');
    }

    public function destroy(Timetable $timetable, TimetablePeriod $period)
    {
        $this->authorizeTimetable($timetable);
        $this->authorizePeriod($period, $timetable);

        $period->entries()->delete();
        $period->delete();

        return back()->with('success', 'Period removed.');
    }

    public function reorder(Request $request, Timetable $timetable)
    {
        $this->authorizeTimetable($timetable);

        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer', 'exists:timetable_periods,id'],
        ]);

        foreach ($request->order as $sortOrder => $id) {
            TimetablePeriod::where('id', $id)
                ->where('timetable_id', $timetable->id)
                ->update(['sort_order' => $sortOrder]);
        }

        return response()->json(['success' => true]);
    }

    private function authorizeTimetable(Timetable $t): void
    {
        if ($t->campus_id !== CampusContext::id()) abort(403);
    }

    private function authorizePeriod(TimetablePeriod $p, Timetable $t): void
    {
        if ($p->timetable_id !== $t->id) abort(403);
    }
}