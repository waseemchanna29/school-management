<?php

namespace App\Http\Controllers\Admin\Fee;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\FeeLabel;
use App\Models\FeeStructure;
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
        $studentFees  = StudentFee::where('student_id', $student->id)
            ->where('academic_year', $academicYear)
            ->with('feeLabel')
            ->get();

        $structures = FeeStructure::where('campus_id', CampusContext::id())
            ->where('class_id', $student->class_id)
            ->where('is_active', true)
            ->get();

        $labels       = FeeLabel::where('campus_id', CampusContext::id())->where('is_active', true)->get();
        $years        = $this->academicYears();
        $invoices     = $student->load('campus')->getRelation('campus') ? null : null;

        return view('admin.fee.student.show', compact('student', 'studentFees', 'structures', 'labels', 'academicYear', 'years'));
    }

    public function assign(Request $request, Student $student)
    {
        if ($student->campus_id !== CampusContext::id()) abort(403);

        $request->validate([
            'fee_structure_id' => ['required', 'exists:fee_structures,id'],
            'academic_year'    => ['required', 'string'],
        ]);

        $this->feeService->assignStructureToStudent($student, $request->fee_structure_id, $request->academic_year);

        return back()->with('success', 'Fee structure assigned to student successfully.');
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

        return back()->with('success', 'Student fee updated. This change only affects this student.');
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

        $existing = StudentFee::where('student_id', $student->id)
            ->where('fee_label_id', $request->fee_label_id)
            ->where('academic_year', $request->academic_year)
            ->first();

        if ($existing) {
            return back()->with('error', 'This fee label is already assigned for this academic year. Edit it instead.');
        }

        StudentFee::create([
            'student_id'    => $student->id,
            'campus_id'     => $student->campus_id,
            'fee_label_id'  => $request->fee_label_id,
            'academic_year' => $request->academic_year,
            'amount'        => $request->amount,
            'is_active'     => true,
            'note'          => $request->note,
        ]);

        return back()->with('success', 'Custom fee added to student.');
    }

    public function destroyFee(StudentFee $studentFee)
    {
        if ($studentFee->campus_id !== CampusContext::id()) abort(403);
        $studentFee->delete();
        return back()->with('success', 'Fee removed from student.');
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