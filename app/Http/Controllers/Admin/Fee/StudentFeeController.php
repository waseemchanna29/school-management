<?php

namespace App\Http\Controllers\Admin\Fee;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\FeeScheduler;
use App\Models\Student;
use App\Models\StudentScheduler;
use App\Models\StudentSchedulerItem;
use App\Services\FeeService;
use Illuminate\Http\Request;

class StudentFeeController extends Controller
{
    public function __construct(private FeeService $fee) {}

    // Show student's fee profile
    // ── Update show() to load enrollment data ─────────────────────────────────────
    public function show(Student $student)
    {
        $this->gate($student);

        $yearId = \App\Helpers\AcademicYearContext::id();

        // Verify student is enrolled this year
        $enrollment = \App\Models\StudentEnrollment::where('student_id', $student->id)
            ->where('campus_id', CampusContext::id())
            ->where('academic_year_id', $yearId)
            ->with(['schoolClass', 'section'])
            ->first();

        $assignment = \App\Models\StudentScheduler::where('student_id', $student->id)
            ->where('academic_year_id', $yearId)    // ← year-scoped
            ->with('feeScheduler')
            ->first();

        $items = \App\Models\StudentSchedulerItem::where('student_id', $student->id)
            ->orderBy('sort_order')
            ->get();

        $schedulers = \App\Models\FeeScheduler::where('campus_id', CampusContext::id())
            ->where('is_active', true)
            ->with('items')
            ->get();

        $invoices = \App\Models\FeeInvoice::where('student_id', $student->id)
            ->where('academic_year_id', $yearId)    // ← year-scoped
            ->latest()
            ->paginate(12);

        return view('admin.fee.student', compact(
            'student',
            'enrollment',
            'assignment',
            'items',
            'schedulers',
            'invoices'
        ));
    }

    // ── Update assign() to include academic_year_id ──────────────────────────────
    public function assign(Request $request, Student $student)
    {
        $this->gate($student);
        $yearId = \App\Helpers\AcademicYearContext::id();

        $request->validate([
            'fee_scheduler_id' => ['required', 'exists:fee_schedulers,id'],
            'assigned_date'    => ['required', 'date'],
        ]);

        $scheduler = \App\Models\FeeScheduler::where('campus_id', CampusContext::id())
            ->findOrFail($request->fee_scheduler_id);

        // Remove old assignment for this year only
        \App\Models\StudentScheduler::where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->delete();
        \App\Models\StudentSchedulerItem::where('student_id', $student->id)
            ->delete();

        // Create new assignment with year
        \App\Models\StudentScheduler::create([
            'student_id'       => $student->id,
            'campus_id'        => $student->campus_id,
            'academic_year_id' => $yearId,    // ← NEW
            'fee_scheduler_id' => $scheduler->id,
            'assigned_date'    => $request->assigned_date,
        ]);

        // Copy items
        foreach ($scheduler->items as $i => $item) {
            \App\Models\StudentSchedulerItem::create([
                'student_id'       => $student->id,
                'campus_id'        => $student->campus_id,
                'fee_scheduler_id' => $scheduler->id,
                'label'            => $item->label,
                'amount'           => $item->amount,
                'is_active'        => true,
                'sort_order'       => $i,
            ]);
        }

        return back()->with(
            'success',
            "\"{$scheduler->name}\" assigned to {$student->full_name}."
        );
    }
    // Update a single item on the student's personal copy
    public function updateItem(Request $request, StudentSchedulerItem $item)
    {
        if ($item->campus_id !== CampusContext::id()) abort(403);

        $request->validate([
            'label'     => ['required', 'string', 'max:150'],
            'amount'    => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'note'      => ['nullable', 'string', 'max:255'],
        ]);

        $item->update([
            'label'     => $request->label,
            'amount'    => $request->amount,
            'is_active' => $request->boolean('is_active', true),
            'note'      => $request->note,
        ]);

        return back()->with('success', 'Fee item updated for this student only.');
    }

    // Add a custom item to student's fee
    public function addItem(Request $request, Student $student)
    {
        $this->gate($student);

        $assignment = StudentScheduler::where('student_id', $student->id)->firstOrFail();

        $request->validate([
            'label'  => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:0'],
            'note'   => ['nullable', 'string'],
        ]);

        $max = StudentSchedulerItem::where('student_id', $student->id)->max('sort_order') ?? -1;

        StudentSchedulerItem::create([
            'student_id'       => $student->id,
            'campus_id'        => $student->campus_id,
            'fee_scheduler_id' => $assignment->fee_scheduler_id,
            'label'            => $request->label,
            'amount'           => $request->amount,
            'is_active'        => true,
            'sort_order'       => $max + 1,
            'note'             => $request->note,
        ]);

        return back()->with('success', 'Custom fee item added.');
    }

    // Remove an item from student's fee
    public function removeItem(StudentSchedulerItem $item)
    {
        if ($item->campus_id !== CampusContext::id()) abort(403);
        $item->delete();
        return back()->with('success', 'Fee item removed.');
    }

    // Remove scheduler assignment from student
    public function unassign(Student $student)
    {
        $this->gate($student);
        StudentScheduler::where('student_id', $student->id)->delete();
        StudentSchedulerItem::where('student_id', $student->id)->delete();
        return back()->with('success', 'Scheduler unassigned from student.');
    }

    private function gate(Student $s): void
    {
        if ($s->campus_id !== CampusContext::id()) abort(403);
    }
}
