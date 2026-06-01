<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ExamTypeWeight;
use Illuminate\Http\Request;

class ExamWeightController extends Controller
{
    public function index()
    {
        $weights = ExamTypeWeight::whereNull('campus_id')
            ->orderBy('sort_order')
            ->get();

        $totalWeight = $weights->sum('weight');

        return view('super.grading.weights', compact('weights', 'totalWeight'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'exam_type'  => ['required', 'string', 'max:50'],
            'label'      => ['required', 'string', 'max:100'],
            'weight'     => ['required', 'numeric', 'min:1', 'max:100'],
        ]);

        $exists = ExamTypeWeight::whereNull('campus_id')
            ->where('exam_type', $request->exam_type)->exists();

        if ($exists) {
            return back()->with('error', "Exam type '{$request->exam_type}' already exists.");
        }

        $maxOrder = ExamTypeWeight::whereNull('campus_id')->max('sort_order') ?? -1;

        ExamTypeWeight::create([
            'campus_id'  => null,
            'exam_type'  => $request->exam_type,
            'label'      => $request->label,
            'weight'     => $request->weight,
            'is_active'  => true,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', "Exam type added.");
    }

    public function update(Request $request, ExamTypeWeight $examTypeWeight)
    {
        $request->validate([
            'label'  => ['required', 'string', 'max:100'],
            'weight' => ['required', 'numeric', 'min:1', 'max:100'],
        ]);

        $examTypeWeight->update([
            'label'  => $request->label,
            'weight' => $request->weight,
        ]);

        return back()->with('success', 'Weight updated.');
    }

    public function destroy(ExamTypeWeight $examTypeWeight)
    {
        $examTypeWeight->delete();
        return back()->with('success', 'Exam type deleted.');
    }
}