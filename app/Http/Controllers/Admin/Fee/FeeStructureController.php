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
        $campusId = CampusContext::id();

        $query = FeeStructure::where('campus_id', $campusId)
            ->with(['schoolClass', 'items.feeLabel']);

        if ($request->filled('class_id'))      $query->where('class_id', $request->class_id);
        if ($request->filled('type'))          $query->where('type', $request->type);
        if ($request->filled('academic_year')) $query->where('academic_year', $request->academic_year);

        $structures = $query->latest()->paginate(20);
        $classes    = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->orderBy('name')->get();
        $years      = $this->academicYears();

        return view('admin.fee.structures.index', compact('structures', 'classes', 'years'));
    }

    public function create()
    {
        $campusId = CampusContext::id();
        $classes  = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->orderBy('name')->get();
        $labels   = FeeLabel::where('campus_id', $campusId)->where('is_active', true)->orderBy('name')->get();
        $years    = $this->academicYears();

        return view('admin.fee.structures.create', compact('classes', 'labels', 'years'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'type'          => ['required', 'in:one_time,monthly,yearly'],
            'class_id'      => ['required', 'exists:classes,id'],
            'academic_year' => ['required', 'string'],
            'notes'         => ['nullable', 'string'],
            'items'         => ['required', 'array', 'min:1'],
            'items.*.fee_label_id' => ['required', 'exists:fee_labels,id'],
            'items.*.amount'       => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request) {
            $structure = FeeStructure::create([
                'campus_id'     => CampusContext::id(),
                'name'          => $request->name,
                'type'          => $request->type,
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

        return redirect()->route('admin.fee.structures.index')
            ->with('success', "Fee structure \"{$request->name}\" created successfully.");
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
        $labels   = FeeLabel::where('campus_id', $campusId)->where('is_active', true)->orderBy('name')->get();
        $years    = $this->academicYears();

        return view('admin.fee.structures.edit', compact('structure', 'classes', 'labels', 'years'));
    }

    public function update(Request $request, FeeStructure $structure)
    {
        $this->authorize($structure);

        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'type'          => ['required', 'in:one_time,monthly,yearly'],
            'class_id'      => ['required', 'exists:classes,id'],
            'academic_year' => ['required', 'string'],
            'notes'         => ['nullable', 'string'],
            'items'         => ['required', 'array', 'min:1'],
            'items.*.id'    => ['nullable', 'exists:fee_structure_items,id'],
            'items.*.fee_label_id' => ['required', 'exists:fee_labels,id'],
            'items.*.amount'       => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $structure) {
            $structure->update([
                'name'          => $request->name,
                'type'          => $request->type,
                'class_id'      => $request->class_id,
                'academic_year' => $request->academic_year,
                'notes'         => $request->notes,
            ]);

            $keptIds = [];

            foreach ($request->items as $item) {
                if (!empty($item['id'])) {
                    $existing = FeeStructureItem::find($item['id']);
                    if ($existing) {
                        $existing->update([
                            'fee_label_id' => $item['fee_label_id'],
                            'amount'       => $item['amount'],
                        ]);
                        $keptIds[] = $existing->id;
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

            // Remove items no longer in the list
            $structure->items()->whereNotIn('id', $keptIds)->delete();
        });

        return redirect()->route('admin.fee.structures.show', $structure)
            ->with('success', 'Fee structure updated.');
    }

    public function destroy(FeeStructure $structure)
    {
        $this->authorize($structure);
        $structure->items()->delete();
        $structure->delete();

        return redirect()->route('admin.fee.structures.index')
            ->with('success', 'Fee structure deleted.');
    }

    public function revise(FeeStructure $structure)
    {
        $this->authorize($structure);

        $parts   = explode('-', $structure->academic_year);
        $newYear = (intval($parts[0]) + 1) . '-' . (intval($parts[1]) + 1);

        DB::transaction(function () use ($structure, $newYear) {
            $new = FeeStructure::create([
                'campus_id'     => $structure->campus_id,
                'name'          => $structure->name,
                'type'          => $structure->type,
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

        return redirect()->route('admin.fee.structures.index')
            ->with('success', "New structure for {$newYear} created from \"{$structure->name}\". Edit amounts as needed.");
    }

    private function authorize(FeeStructure $s): void
    {
        if ($s->campus_id !== CampusContext::id()) abort(403);
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