<?php

namespace App\Http\Controllers\Admin\Fee;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\FeeLabel;
use App\Models\FeeStructure;
use App\Models\FeeInvoice;
use App\Models\Student;
use App\Models\StudentFee;
use App\Services\FeeService;
use Illuminate\Http\Request;

class StudentFeeController extends Controller
{
    public function __construct(private FeeService $feeService) {}

    public function show(Student $student)
    {
        if ($student->campus_id !== CampusContext::id()) abort(403);

        $academicYear = request('academic_year', $this->currentAcademicYear());

        // All personal fee lines for this student this year
        $studentFees = StudentFee::where('student_id', $student->id)
            ->where('academic_year', $academicYear)
            ->with(['feeLabel', 'feeStructure'])
            ->orderBy('fee_structure_id')
            ->get();

        // Group by structure for display
        $feesByStructure = $studentFees->groupBy(fn($f) => $f->feeStructure?->name ?? 'Custom / No Structure');

        // All structures for this student's class to allow assigning
        $availableStructures = FeeStructure::where('campus_id', CampusContext::id())
            ->where('class_id', $student->class_id)
            ->where('is_active', true)
            ->with('items.feeLabel')
            ->get();

        // All labels for adding custom fee
        $labels = FeeLabel::where('campus_id', CampusContext::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Invoices this year
        $invoices = FeeInvoice::where('student_id', $student->id)
            ->where('academic_year', $academicYear)
            ->latest()
            ->get();

        $years = $this->academicYears();

        return view('admin.fee.student.show', compact(
            'student', 'studentFees', 'feesByStructure',
            'availableStructures', 'labels', 'invoices',
            'academicYear', 'years'
        ));
    }

    public function assign(Request $request, Student $student)
    {
        if ($student->campus_id !== CampusContext::id()) abort(403);

        $request->validate([
            'fee_structure_id' => ['required', 'exists:fee_structures,id'],
            'academic_year'    => ['required', 'string'],
        ]);

        $structure = FeeStructure::where('campus_id', CampusContext::id())
            ->findOrFail($request->fee_structure_id);

        $this->feeService->assignStructureToStudent(
            $student,
            $structure->id,
            $request->academic_year
        );

        return back()->with('success', "\"{$structure->name}\" assigned to {$student->full_name} successfully.");
    }

    public function updateFee(Request $request, StudentFee $studentFee)
    {
        if ($studentFee->campus_id !== CampusContext::id()) abort(403);

        $request->validate([
            'amount'    => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'note'      => ['nullable', 'string', 'max:255'],
        ]);

        $studentFee->update([
            'amount'    => $request->amount,
            'is_active' => $request->boolean('is_active', true),
            'note'      => $request->note,
        ]);

        return back()->with('success', 'Fee updated. This change only affects this student.');
    }

    public function addFee(Request $request, Student $student)
    {
        if ($student->campus_id !== CampusContext::id()) abort(403);

        $request->validate([
            'fee_label_id'  => ['required', 'exists:fee_labels,id'],
            'amount'        => ['required', 'numeric', 'min:0'],
            'academic_year' => ['required', 'string'],
            'note'          => ['nullable', 'string'],
        ]);

        // Check if same label already exists with no structure (custom)
        $exists = StudentFee::where('student_id', $student->id)
            ->where('fee_label_id', $request->fee_label_id)
            ->where('academic_year', $request->academic_year)
            ->whereNull('fee_structure_id')
            ->exists();

        if ($exists) {
            return back()->with('error', 'This custom fee label already exists for this student this year.');
        }

        StudentFee::create([
            'student_id'       => $student->id,
            'campus_id'        => $student->campus_id,
            'fee_label_id'     => $request->fee_label_id,
            'fee_structure_id' => null,
            'academic_year'    => $request->academic_year,
            'amount'           => $request->amount,
            'is_active'        => true,
            'note'             => $request->note,
        ]);

        return back()->with('success', 'Custom fee line added to student.');
    }

    public function destroyFee(StudentFee $studentFee)
    {
        if ($studentFee->campus_id !== CampusContext::id()) abort(403);
        $studentFee->delete();
        return back()->with('success', 'Fee line removed from student.');
    }

    private function currentAcademicYear(): string
    {
        $y = (int) date('Y');
        return date('n') >= 4 ? "$y-" . ($y + 1) : ($y - 1) . "-$y";
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