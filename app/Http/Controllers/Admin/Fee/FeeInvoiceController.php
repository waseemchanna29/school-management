<?php

namespace App\Http\Controllers\Admin\Fee;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\FeeInvoice;
use App\Models\Student;
use App\Services\FeeService;
use Illuminate\Http\Request;

class FeeInvoiceController extends Controller
{
    public function __construct(private FeeService $fee) {}

    public function index(Request $request)
    {
        $campusId = CampusContext::id();

        $query = FeeInvoice::where('campus_id', $campusId)
            ->with(['student.schoolClass', 'student.section']);

        if ($request->filled('status'))        $query->where('status', $request->status);
        if ($request->filled('billing_month')) $query->where('billing_month', $request->billing_month);
        if ($request->filled('billing_year'))  $query->where('billing_year', $request->billing_year);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('student', fn($q) => $q
                ->where('full_name', 'like', "%$s%")
                ->orWhere('roll_number', 'like', "%$s%"))
                ->orWhere('invoice_number', 'like', "%$s%");
        }

        $invoices = $query->latest()->paginate(20);
        $years    = range(date('Y') - 1, date('Y') + 1);

        return view('admin.fee.invoices.index', compact('invoices', 'years'));
    }

    // Single invoice generate form
    public function create(Request $request)
    {
        $campusId = CampusContext::id();
        $students = Student::where('campus_id', $campusId)
            ->where('status', 'active')
            ->whereHas('schedulerAssignment')
            ->with(['schoolClass', 'section'])
            ->orderBy('full_name')
            ->get();

        $student = $request->filled('student_id')
            ? Student::where('campus_id', $campusId)->find($request->student_id)
            : null;

        return view('admin.fee.invoices.create', compact('students', 'student'));
    }

    // Store single invoice
    public function store(Request $request)
    {
        $request->validate([
            'student_id'    => ['required', 'exists:students,id'],
            'billing_month' => ['required', 'integer', 'min:1', 'max:12'],
            'billing_year'  => ['required', 'integer'],
            'due_date'      => ['required', 'date'],
            'outstanding'   => ['nullable', 'numeric', 'min:0'],
            'fine'          => ['nullable', 'numeric', 'min:0'],
            'discount'      => ['nullable', 'numeric', 'min:0'],
            'remarks'       => ['nullable', 'string'],
        ]);

        $student = Student::where('campus_id', CampusContext::id())
            ->findOrFail($request->student_id);

        try {
            $invoice = $this->fee->generateInvoice(
                $student,
                (int) $request->billing_month,
                (int) $request->billing_year,
                $request->due_date,
                (float) ($request->outstanding ?? 0),
                (float) ($request->fine        ?? 0),
                (float) ($request->discount    ?? 0),
                $request->remarks
            );

            return redirect()->route('admin.fee.invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} created.");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // Show single invoice + print slip
    public function show(FeeInvoice $invoice)
    {
        if ($invoice->campus_id !== CampusContext::id()) abort(403);

        $invoice->load([
            'student.schoolClass',
            'student.section',
            'items',
            'payments',
            'feeScheduler',
        ]);

        $setting = CampusSetting::where('campus_id', $invoice->campus_id)->first();
        $campus  = $invoice->campus;

        return view('admin.fee.invoices.show', compact('invoice', 'setting', 'campus'));
    }

    // Bulk generate — form
    public function bulkCreate()
    {
        $years = range(date('Y') - 1, date('Y') + 1);
        return view('admin.fee.invoices.bulk', compact('years'));
    }

    // Bulk generate — process
    public function bulkStore(Request $request)
    {
        $request->validate([
            'billing_month' => ['required', 'integer', 'min:1', 'max:12'],
            'billing_year'  => ['required', 'integer'],
            'due_date'      => ['required', 'date'],
            'outstanding'   => ['nullable', 'numeric', 'min:0'],
            'fine'          => ['nullable', 'numeric', 'min:0'],
            'discount'      => ['nullable', 'numeric', 'min:0'],
        ]);

        $result = $this->fee->bulkGenerate(
            CampusContext::id(),
            (int) $request->billing_month,
            (int) $request->billing_year,
            $request->due_date,
            (float) ($request->outstanding ?? 0),
            (float) ($request->fine        ?? 0),
            (float) ($request->discount    ?? 0)
        );

        $msg = "{$result['generated']} invoice(s) generated, {$result['skipped']} skipped (already exist).";

        if (!empty($result['errors'])) {
            $msg .= ' Issues: ' . implode(' | ', $result['errors']);
            return redirect()->route('admin.fee.invoices.index')->with('warning', $msg);
        }

        return redirect()->route('admin.fee.invoices.index')->with('success', $msg);
    }

    // Edit adjustments (outstanding, fine, discount, due date)
    public function adjust(Request $request, FeeInvoice $invoice)
    {
        if ($invoice->campus_id !== CampusContext::id()) abort(403);
        if ($invoice->status === 'paid') return back()->with('error', 'Cannot edit a fully paid invoice.');

        $request->validate([
            'outstanding' => ['nullable', 'numeric', 'min:0'],
            'fine'        => ['nullable', 'numeric', 'min:0'],
            'discount'    => ['nullable', 'numeric', 'min:0'],
            'due_date'    => ['nullable', 'date'],
            'remarks'     => ['nullable', 'string'],
        ]);

        $invoice->update([
            'outstanding' => $request->outstanding ?? 0,
            'fine'        => $request->fine        ?? 0,
            'discount'    => $request->discount    ?? 0,
            'due_date'    => $request->due_date ?? $invoice->due_date,
            'remarks'     => $request->remarks,
        ]);
        $invoice->recalculate();

        return back()->with('success', 'Invoice adjustments saved.');
    }

    public function waive(FeeInvoice $invoice)
    {
        if ($invoice->campus_id !== CampusContext::id()) abort(403);
        $invoice->update(['status' => 'waived', 'balance' => 0]);
        return back()->with('success', 'Invoice waived.');
    }

    public function destroy(FeeInvoice $invoice)
    {
        if ($invoice->campus_id !== CampusContext::id()) abort(403);
        if ($invoice->payments()->count() > 0) {
            return back()->with('error', 'Cannot delete invoice with payments recorded.');
        }
        $invoice->items()->delete();
        $invoice->delete();
        return redirect()->route('admin.fee.invoices.index')->with('success', 'Invoice deleted.');
    }
}