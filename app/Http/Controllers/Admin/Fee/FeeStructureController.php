<?php

namespace App\Http\Controllers\Admin\Fee;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\FeeLabel;
use App\Models\FeeStructure;
use App\Models\FeeStructureItem;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeStructureController extends Controller
{
    public function index(Request $request)
    {
        $campusId   = CampusContext::id();
        $query      = FeeStructure::where('campus_id', $campusId)
            ->with(['schoolClass', 'items.feeLabel']);

        if ($request->filled('class_id'))      $query->where('class_id', $request->class_id);
        if ($request->filled('academic_year')) $query->where('academic_year', $request->academic_year);

        $structures   = $query->latest()->paginate(20);
        $classes      = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->orderBy('name')->get();
        $years        = $this->academicYears();

        return view('admin.fee.structures.index', compact('structures', 'classes', 'years'));
    }

    public function create()
    {
        $campusId = CampusContext::id();
        $classes  = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->orderBy('name')->get();
        $labels   = FeeLabel::where('campus_id', $campusId)->where('is_active', true)->get();
        $years    = $this->academicYears();
        return view('admin.fee.structures.create', compact('classes', 'labels', 'years'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id'      => ['required', 'exists:classes,id'],
            'academic_year' => ['required', 'string'],
            'notes'         => ['nullable', 'string'],
            'items'         => ['required', 'array', 'min:1'],
            'items.*.fee_label_id' => ['required', 'exists:fee_labels,id'],
            'items.*.amount'       => ['required', 'numeric', 'min:0'],
        ]);

        $campusId = CampusContext::id();

        $exists = FeeStructure::where('campus_id', $campusId)
            ->where('class_id', $request->class_id)
            ->where('academic_year', $request->academic_year)
            ->exists();

        if ($exists) {
            return back()->withErrors(['academic_year' => 'A fee structure for this class and academic year already exists.'])->withInput();
        }

        DB::transaction(function () use ($request, $campusId) {
            $structure = FeeStructure::create([
                'campus_id'     => $campusId,
                'class_id'      => $request->class_id,
                'academic_year' => $request->academic_year,
                'is_active'     => true,
                'notes'         => $request->notes,
            ]);

            foreach ($request->items as $item) {
                FeeStructureItem::create([
                    'fee_structure_id' => $structure->id,
                    'fee_label_id'     => $item['fee_label_id'],
                    'amount'           => $item['amount'],
                    'is_active'        => true,
                ]);
            }
        });

        return redirect()->route('admin.fee.structures.index')->with('success', 'Fee structure created successfully.');
    }

    public function show(FeeStructure $structure)
    {
        $this->authorize($structure);
        $structure->load(['schoolClass', 'items.feeLabel']);
        return view('admin.fee.structures.show', compact('structure'));
    }

    public function edit(FeeStructure $structure)
    {
        $this->authorize($structure);
        $structure->load('items.feeLabel');
        $campusId = CampusContext::id();
        $classes  = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->orderBy('name')->get();
        $labels   = FeeLabel::where('campus_id', $campusId)->where('is_active', true)->get();
        $years    = $this->academicYears();
        return view('admin.fee.structures.edit', compact('structure', 'classes', 'labels', 'years'));
    }

    public function update(Request $request, FeeStructure $structure)
    {
        $this->authorize($structure);
        $request->validate([
            'notes'                => ['nullable', 'string'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.id'           => ['nullable', 'exists:fee_structure_items,id'],
            'items.*.fee_label_id' => ['required', 'exists:fee_labels,id'],
            'items.*.amount'       => ['required', 'numeric', 'min:0'],
            'items.*.is_active'    => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $structure) {
            $structure->update(['notes' => $request->notes]);

            $keptIds = [];
            foreach ($request->items as $item) {
                if (!empty($item['id'])) {
                    $sItem = FeeStructureItem::find($item['id']);
                    if ($sItem) {
                        $sItem->update([
                            'fee_label_id' => $item['fee_label_id'],
                            'amount'       => $item['amount'],
                            'is_active'    => isset($item['is_active']) ? (bool)$item['is_active'] : true,
                        ]);
                        $keptIds[] = $sItem->id;
                    }
                } else {
                    $new = FeeStructureItem::create([
                        'fee_structure_id' => $structure->id,
                        'fee_label_id'     => $item['fee_label_id'],
                        'amount'           => $item['amount'],
                        'is_active'        => true,
                    ]);
                    $keptIds[] = $new->id;
                }
            }

            // Remove deleted items
            $structure->items()->whereNotIn('id', $keptIds)->delete();
        });

        return redirect()->route('admin.fee.structures.show', $structure)->with('success', 'Fee structure updated.');
    }

    public function destroy(FeeStructure $structure)
    {
        $this->authorize($structure);
        $structure->items()->delete();
        $structure->delete();
        return redirect()->route('admin.fee.structures.index')->with('success', 'Fee structure deleted.');
    }

    public function revise(FeeStructure $structure)
    {
        $this->authorize($structure);
        // Create new academic year copy
        $parts   = explode('-', $structure->academic_year);
        $newYear = (intval($parts[0]) + 1) . '-' . (intval($parts[1]) + 1);

        $exists = FeeStructure::where('campus_id', $structure->campus_id)
            ->where('class_id', $structure->class_id)
            ->where('academic_year', $newYear)->exists();

        if ($exists) {
            return back()->with('error', "A structure for {$newYear} already exists for this class.");
        }

        DB::transaction(function () use ($structure, $newYear) {
            $new = FeeStructure::create([
                'campus_id'     => $structure->campus_id,
                'class_id'      => $structure->class_id,
                'academic_year' => $newYear,
                'is_active'     => true,
                'notes'         => "Revised from {$structure->academic_year}",
            ]);

            foreach ($structure->items as $item) {
                FeeStructureItem::create([
                    'fee_structure_id' => $new->id,
                    'fee_label_id'     => $item->fee_label_id,
                    'amount'           => $item->amount,
                    'is_active'        => $item->is_active,
                ]);
            }
        });

        return redirect()->route('admin.fee.structures.index')->with('success', "New structure for {$newYear} created. You can now edit the amounts.");
    }

    private function authorize(FeeStructure $structure): void
    {
        if ($structure->campus_id !== CampusContext::id()) abort(403);
    }

    private function academicYears(): array
    {
        $years = [];
        $start = (int) date('Y') - 1;
        for ($i = $start; $i <= $start + 4; $i++) {
            $years[] = $i . '-' . ($i + 1);
        }
        return $years;
    }
}