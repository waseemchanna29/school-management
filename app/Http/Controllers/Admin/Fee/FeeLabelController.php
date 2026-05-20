<?php

namespace App\Http\Controllers\Admin\Fee;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\FeeLabel;
use Illuminate\Http\Request;

class FeeLabelController extends Controller
{
    public function index()
    {
        $labels = FeeLabel::where('campus_id', CampusContext::id())->latest()->get();
        return view('admin.fee.labels.index', compact('labels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'frequency' => ['required', 'in:one_time,monthly,yearly'],
        ]);

        FeeLabel::create([
            'campus_id' => CampusContext::id(),
            'name'      => $request->name,
            'frequency' => $request->frequency,
            'is_active' => true,
        ]);

        return back()->with('success', "Fee label \"{$request->name}\" added.");
    }

    public function update(Request $request, FeeLabel $feeLabel)
    {
        $this->authorize($feeLabel);
        $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'frequency' => ['required', 'in:one_time,monthly,yearly'],
        ]);
        $feeLabel->update($request->only('name', 'frequency'));
        return back()->with('success', 'Label updated.');
    }

    public function destroy(FeeLabel $feeLabel)
    {
        $this->authorize($feeLabel);
        if ($feeLabel->structureItems()->count() > 0) {
            return back()->with('error', 'Cannot delete — label is used in fee structures.');
        }
        $feeLabel->delete();
        return back()->with('success', 'Label deleted.');
    }

    public function toggle(FeeLabel $feeLabel)
    {
        $this->authorize($feeLabel);
        $feeLabel->update(['is_active' => !$feeLabel->is_active]);
        return back()->with('success', 'Label status updated.');
    }

    private function authorize(FeeLabel $label): void
    {
        if ($label->campus_id !== CampusContext::id()) abort(403);
    }
}