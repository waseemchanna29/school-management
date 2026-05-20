<?php

namespace App\Http\Controllers\Admin\Fee;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\FeeService;
use Illuminate\Http\Request;

class FeeInvoiceController extends Controller
{
    public function __construct(private FeeService $feeService) {}

    public function index(Request $request)
    {
        $campusId = CampusContext::id();
        $query    = FeeInvoice::where('campus_id', $campusId)
            ->with(['student', 'payments']);

        if ($request->filled('student_id')) $query->where('student_id', $request->student_id);
        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('period_type')) $query->where('period_type', $request->period_type);
        if ($request->filled('academic_year')) $query->where('academic_year', $request->academic_year);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('invoice_number', 'like', "%$s%")
                ->orWhereHas('student', fn($sq) => $sq->where('full_name', 'like', "%$s%")
                    ->orWhere('roll_number', 'like', "%$s%")));
        }

        $invoices = $query->latest()->paginate(20);
        $classes  = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->get();
        $years    = $this->academicYears();

        return view('admin.fee.invoices.index', compact('invoices', 'classes', 'years'));
    }

    public function create(Request $request)
    {
        $campusId = CampusContext::id();
        $student  = $request->filled('student_id')
            ? Student::where('campus_id', $campusId)->findOrFail($request->student_id)
            : null;

        $students = Student::where('campus_id', $campusId)->where('status', 'active')
            ->orderBy('full_name')->get();
        $years    = $this->academicYears();

        return view('admin.fee.invoices.create', compact('students', 'years', 'student'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id'    => ['required', 'exists:students,id'],
            'academic_year' => ['required', 'string'],
            'period_type'   => ['required', 'in:monthly,yearly,one_time'],
            'month'         => ['nullable', 'integer', 'min:1', 'max:12'],
            'year'          => ['required', 'integer'],
            'period_label'  => ['nullable', 'string'],
            'due_date'      => ['required', 'date'],
        ]);

        $student = Student::where('campus_id', CampusContext::id())->findOrFail($request->student_id);

        try {
            $invoice = $this->feeService->generateInvoice(
                $student,
                $request->academic_year,
                $request->period_type,
                $request->year,
                $request->month,
                $request->period_label,
                $request->due_date
            );

            return redirect()->route('admin.fee.invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} generated.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(FeeInvoice $invoice)
    {
        if ($invoice->campus_id !== CampusContext::id()) abort(403);
        $invoice->load(['student.schoolClass', 'student.section', 'items.feeLabel', 'payments']);
        return view('admin.fee.invoices.show', compact('invoice'));
    }

    public function updateAdjustments(Request $request, FeeInvoice $invoice)
    {
        if ($invoice->campus_id !== CampusContext::id()) abort(403);
        if ($invoice->status === 'paid') return back()->with('error', 'Cannot edit a fully paid invoice.');

        $request->validate([
            'discount' => ['nullable', 'numeric', 'min:0'],
            'fine'     => ['nullable', 'numeric', 'min:0'],
            'remarks'  => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        $invoice->update([
            'discount' => $request->discount ?? 0,
            'fine'     => $request->fine ?? 0,
            'remarks'  => $request->remarks,
            'due_date' => $request->due_date ?? $invoice->due_date,
        ]);

        $invoice->recalculate();

        return back()->with('success', 'Invoice adjustments saved.');
    }

    public function waive(FeeInvoice $invoice)
    {
        if ($invoice->campus_id !== CampusContext::id()) abort(403);
        $invoice->update(['status' => 'waived', 'balance' => 0]);
        return back()->with('success', 'Invoice marked as waived.');
    }

    public function destroy(FeeInvoice $invoice)
    {
        if ($invoice->campus_id !== CampusContext::id()) abort(403);
        if ($invoice->payments()->count() > 0) {
            return back()->with('error', 'Cannot delete invoice with recorded payments.');
        }
        $invoice->items()->delete();
        $invoice->delete();
        return back()->with('success', 'Invoice deleted.');
    }

    private function academicYears(): array
    {
        $years = [];
        $start = (int) date('Y') - 1;
        for ($i = $start; $i <= $start + 3; $i++) {
            $years[] = $i . '-' . ($i + 1);
        }
        return $years;
    }
}