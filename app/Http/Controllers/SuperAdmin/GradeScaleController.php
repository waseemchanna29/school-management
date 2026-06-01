<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\GradeItem;
use App\Models\GradeScale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeScaleController extends Controller
{
    public function index()
    {
        $scales = GradeScale::with(['items', 'campus'])
            ->latest()->get();

        return view('super.grading.index', compact('scales'));
    }

    public function create()
    {
        return view('super.grading.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'is_default'         => ['nullable', 'boolean'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.grade'      => ['required', 'string', 'max:10'],
            'items.*.min_marks'  => ['required', 'integer', 'min:0', 'max:100'],
            'items.*.max_marks'  => ['required', 'integer', 'min:0', 'max:100'],
            'items.*.gpa'        => ['required', 'numeric', 'min:0', 'max:4'],
            'items.*.description'=> ['nullable', 'string'],
            'items.*.color'      => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request) {
            // If setting as default, remove default from others
            if ($request->boolean('is_default')) {
                GradeScale::whereNull('campus_id')
                    ->update(['is_default' => false]);
            }

            $scale = GradeScale::create([
                'campus_id'  => null,
                'name'       => $request->name,
                'is_default' => $request->boolean('is_default'),
                'is_active'  => true,
            ]);

            foreach ($request->items as $i => $item) {
                GradeItem::create([
                    'grade_scale_id' => $scale->id,
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

        return redirect()->route('super.grading.index')
            ->with('success', 'Grade scale created.');
    }

    public function edit(GradeScale $gradeScale)
    {
        $gradeScale->load('items');
        return view('super.grading.edit', compact('gradeScale'));
    }

    public function update(Request $request, GradeScale $gradeScale)
    {
        $request->validate([
            'name'               => ['required', 'string'],
            'is_default'         => ['nullable', 'boolean'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.grade'      => ['required', 'string'],
            'items.*.min_marks'  => ['required', 'integer', 'min:0', 'max:100'],
            'items.*.max_marks'  => ['required', 'integer', 'min:0', 'max:100'],
            'items.*.gpa'        => ['required', 'numeric', 'min:0', 'max:4'],
            'items.*.description'=> ['nullable', 'string'],
            'items.*.color'      => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $gradeScale) {
            if ($request->boolean('is_default')) {
                GradeScale::whereNull('campus_id')
                    ->where('id', '!=', $gradeScale->id)
                    ->update(['is_default' => false]);
            }

            $gradeScale->update([
                'name'       => $request->name,
                'is_default' => $request->boolean('is_default'),
            ]);

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

        return redirect()->route('super.grading.index')
            ->with('success', 'Grade scale updated.');
    }

    public function destroy(GradeScale $gradeScale)
    {
        $gradeScale->items()->delete();
        $gradeScale->delete();
        return back()->with('success', 'Grade scale deleted.');
    }
}