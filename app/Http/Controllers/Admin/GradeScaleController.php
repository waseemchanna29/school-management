<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\ExamTypeWeight;
use App\Models\GradeItem;
use App\Models\GradeScale;
use App\Services\PerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeScaleController extends Controller
{
    public function __construct(private PerformanceService $perf) {}

    public function index()
    {
        $campusId   = CampusContext::id();
        $campusScale = GradeScale::where('campus_id', $campusId)
            ->with('items')->first();

        $globalScale = GradeScale::whereNull('campus_id')
            ->where('is_default', true)
            ->with('items')->first();

        $campusWeights = ExamTypeWeight::where('campus_id', $campusId)
            ->orderBy('sort_order')->get();

        $globalWeights = ExamTypeWeight::whereNull('campus_id')
            ->orderBy('sort_order')->get();

        $activeScale   = $campusScale ?? $globalScale;
        $activeWeights = $campusWeights->isNotEmpty() ? $campusWeights : $globalWeights;

        return view('admin.grading.index', compact(
            'campusScale', 'globalScale', 'campusWeights',
            'globalWeights', 'activeScale', 'activeWeights'
        ));
    }

    public function copyGlobalScale()
    {
        $campusId = CampusContext::id();

        $exists = GradeScale::where('campus_id', $campusId)->exists();
        if ($exists) {
            return back()->with('error', 'Campus already has a grade scale. Edit it instead.');
        }

        $scale = $this->perf->copyGlobalToCampus(
            $campusId,
            'Campus Grade Scale (copied from global default)'
        );

        return redirect()->route('admin.grading.edit', $scale)
            ->with('success', 'Global scale copied. Now customize it for your campus.');
    }

    public function edit(GradeScale $gradeScale)
    {
        if ($gradeScale->campus_id !== CampusContext::id()) abort(403);
        $gradeScale->load('items');
        return view('admin.grading.edit', compact('gradeScale'));
    }

    public function update(Request $request, GradeScale $gradeScale)
    {
        if ($gradeScale->campus_id !== CampusContext::id()) abort(403);

        $request->validate([
            'name'               => ['required', 'string'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.grade'      => ['required', 'string'],
            'items.*.min_marks'  => ['required', 'integer', 'min:0', 'max:100'],
            'items.*.max_marks'  => ['required', 'integer', 'min:0', 'max:100'],
            'items.*.gpa'        => ['required', 'numeric', 'min:0', 'max:4'],
            'items.*.description'=> ['nullable', 'string'],
            'items.*.color'      => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $gradeScale) {
            $gradeScale->update(['name' => $request->name]);
            $gradeScale->items()->delete();

            foreach ($request->items as $i => $item) {
                GradeItem::create([
                    'grade_scale_id' => $gradeScale->id,
                    'grade'          => $item['grade'],
                    'min_marks'      => $item['min_marks'],
                    'max_marks'      => $item['max_marks'],
                    'gpa'            => $item['gpa'],
                    'description'    => $item['description'] ?? null,
                    'color'          => $item['color'] ?? '#6c7a8d',
                    'sort_order'     => $i,
                ]);
            }
        });

        return back()->with('success', 'Grade scale updated.');
    }

    public function copyGlobalWeights()
    {
        $campusId = CampusContext::id();
        $this->perf->copyGlobalWeightsToCampus($campusId);
        return redirect()->route('admin.grading.index')
            ->with('success', 'Global exam weights copied. You can now customize them.');
    }

    public function updateWeight(Request $request, ExamTypeWeight $weight)
    {
        if ($weight->campus_id !== CampusContext::id()) abort(403);

        $request->validate([
            'label'  => ['required', 'string'],
            'weight' => ['required', 'numeric', 'min:1', 'max:100'],
        ]);

        $weight->update(['label' => $request->label, 'weight' => $request->weight]);

        return back()->with('success', 'Weight updated.');
    }

    public function destroyScale(GradeScale $gradeScale)
    {
        if ($gradeScale->campus_id !== CampusContext::id()) abort(403);
        $gradeScale->items()->delete();
        $gradeScale->delete();
        return redirect()->route('admin.grading.index')
            ->with('success', 'Campus grade scale removed. Global default will now apply.');
    }
}