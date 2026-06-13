<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AcademicYearContext;
use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    // ── Index — year-aware student list ──────────────────────────────────────
    public function index(Request $request)
    {
        $campusId = CampusContext::id();
        $yearId   = AcademicYearContext::id();

        // Base query: students enrolled this year in this campus
        $query = StudentEnrollment::where('academic_year_id', $yearId)
            ->where('campus_id', $campusId)
            ->with(['student', 'schoolClass', 'section']);

        // Filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas(
                'student',
                fn($q) => $q
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('cnic', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
            )->orWhere('roll_number', 'like', "%{$search}%");
        }

        $enrollments = $query->orderBy('roll_number')->paginate(25);

        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->orderBy('name')->get();
        $sections = Section::where('campus_id', $campusId)
            ->where('is_active', true)
            ->with('schoolClass')->get();

        return view(
            'admin.students.index',
            compact('enrollments', 'classes', 'sections')
        );
    }

    // ── Show student profile ──────────────────────────────────────────────────
    public function show(Request $request, Student $student)
    {
        $this->gate($student);

        $campusId = CampusContext::id();

        // Year selection: profile can show any year independently
        // Default to session year, but allow override via ?year_id=
        $yearId = (int) $request->get(
            'year_id',
            AcademicYearContext::id()
        );

        $activeTab = $request->get('tab', 'overview');

        // All years this student has been enrolled (for year switcher)
        $studentYears = StudentEnrollment::where('student_id', $student->id)
            ->with('academicYear')
            ->orderByDesc('academic_year_id')
            ->get()
            ->map(fn($e) => $e->academicYear)
            ->filter()
            ->unique('id')
            ->values();

        // Enrollment for selected year
        $enrollment = StudentEnrollment::where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->with(['schoolClass', 'section', 'academicYear', 'campus'])
            ->first();

        // All enrollments for history tab
        $allEnrollments = StudentEnrollment::where('student_id', $student->id)
            ->with(['schoolClass', 'section', 'academicYear', 'campus'])
            ->orderByDesc('academic_year_id')
            ->get();

        // ── Attendance tab data ───────────────────────────────────────────────────
        $attendanceData = [];
        if ($activeTab === 'attendance' || $activeTab === 'overview') {
            $month = (int) $request->get('att_month', date('n'));
            $year  = (int) $request->get('att_year',  date('Y'));

            $sectionId = $enrollment?->section_id;

            $monthlySessions = $sectionId
                ? \App\Models\AttendanceSession::where('section_id', $sectionId)
                ->where('academic_year_id', $yearId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->with(['records' => fn($q) => $q
                    ->where('student_id', $student->id)])
                ->orderBy('date')
                ->get()
                : collect();

            $attSummary = [
                'present'      => 0,
                'absent'       => 0,
                'late'         => 0,
                'leave'        => 0,
                'working_days' => $monthlySessions->count(),
            ];

            foreach ($monthlySessions as $sess) {
                $rec = $sess->records->first();
                if ($rec) $attSummary[$rec->status]++;
            }

            $attSummary['percentage'] = $attSummary['working_days'] > 0
                ? round(($attSummary['present']
                    / $attSummary['working_days']) * 100, 1)
                : 0;

            // Calendar map
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $firstDay    = \Carbon\Carbon::create($year, $month, 1)->dayOfWeek;

            $calendarMap = [];
            foreach ($monthlySessions as $sess) {
                $rec = $sess->records->first();
                $calendarMap[$sess->date->toDateString()] = $rec?->status;
            }

            // Year-level summary (all months)
            $yearSummary = $sectionId
                ? \App\Models\AttendanceSession::where('section_id', $sectionId)
                ->where('academic_year_id', $yearId)
                ->where('status', 'submitted')
                ->with(['records' => fn($q) => $q
                    ->where('student_id', $student->id)])
                ->get()
                ->reduce(function ($carry, $sess) {
                    $rec = $sess->records->first();
                    $carry['total']++;
                    if ($rec) $carry[$rec->status]++;
                    return $carry;
                }, [
                    'total' => 0,
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'leave' => 0
                ])
                : [
                    'total' => 0,
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'leave' => 0
                ];

            $yearSummary['percentage'] = $yearSummary['total'] > 0
                ? round(($yearSummary['present']
                    / $yearSummary['total']) * 100, 1)
                : 0;

            $attendanceData = compact(
                'monthlySessions',
                'attSummary',
                'yearSummary',
                'calendarMap',
                'daysInMonth',
                'firstDay',
                'month',
                'year'
            );
        }

        // ── Performance tab data ──────────────────────────────────────────────────
        $performanceData = [];
        if ($activeTab === 'performance' || $activeTab === 'overview') {
            $perfService = app(\App\Services\PerformanceService::class);

            $termReports = [];
            foreach (\App\Services\PerformanceService::TERMS as $termNum => $termLabel) {
                $report = $perfService->getStudentReport($student, $yearId, $termNum);
                if (!empty($report['subject_results'])) {
                    $termReports[$termNum] = [
                        'label'   => $termLabel,
                        'report'  => $report,
                    ];
                }
            }

            $activeTerm = (int) $request->get('term', array_key_first($termReports) ?? 1);

            $performanceData = compact('termReports', 'activeTerm');
        }

        // ── Fee tab data ───────────────────────────────────────────────────────────
        $feeData = [];
        if ($activeTab === 'fees' || $activeTab === 'overview') {
            $invoices = \App\Models\FeeInvoice::where('student_id', $student->id)
                ->where('academic_year_id', $yearId)
                ->with(['items', 'payments'])
                ->orderBy('billing_month')
                ->get();

            $feeScheduler = \App\Models\StudentScheduler::where('student_id', $student->id)
                ->where('academic_year_id', $yearId)
                ->with('feeScheduler.items')
                ->first();

            $feeSummary = [
                'total_billed'  => $invoices->sum('net_amount'),
                'total_paid'    => $invoices->sum('paid_amount'),
                'total_balance' => $invoices->sum('balance'),
                'unpaid_count'  => $invoices->where('status', 'unpaid')->count(),
                'paid_count'    => $invoices->where('status', 'paid')->count(),
            ];

            $feeData = compact('invoices', 'feeScheduler', 'feeSummary');
        }

        $student->load(['parentRecord', 'campus']);

        $selectedYear = \App\Models\AcademicYear::find($yearId);

        return view('admin.students.show', compact(
            'student',
            'enrollment',
            'allEnrollments',
            'studentYears',
            'selectedYear',
            'yearId',
            'activeTab',
            'attendanceData',
            'performanceData',
            'feeData'
        ));
    }

    // ── Edit student personal info ────────────────────────────────────────────
    public function edit(Student $student)
    {
        $this->gate($student);
        return view('admin.students.edit', compact('student'));
    }

    // ── Update student personal info ──────────────────────────────────────────
    public function update(Request $request, Student $student)
    {
        $this->gate($student);

        $request->validate([
            'full_name'     => ['required', 'string', 'max:200'],
            'father_name'   => ['required', 'string', 'max:200'],
            'mother_name'   => ['nullable', 'string', 'max:200'],
            'cnic'          => ['nullable', 'string', 'max:20'],
            'gender'        => ['required', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date'],
            'blood_group'   => ['nullable', 'string', 'max:5'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'address'       => ['nullable', 'string'],
            'photo'         => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'status'        => ['required', 'in:active,left,graduated'],
        ]);

        if ($request->hasFile('photo')) {
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $student->photo = $request->file('photo')
                ->store('students', 'public');
        }

        $student->update([
            'full_name'     => $request->full_name,
            'father_name'   => $request->father_name,
            'mother_name'   => $request->mother_name,
            'cnic'          => $request->cnic,
            'gender'        => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'blood_group'   => $request->blood_group,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'status'        => $request->status,
            'photo'         => $student->photo,
        ]);

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student profile updated.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────
    public function destroy(Student $student)
    {
        $this->gate($student);

        if ($student->enrollments()->count() > 0) {
            return back()->with(
                'error',
                'Cannot delete student with enrollment records. ' .
                    'Remove all enrollments first.'
            );
        }

        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted.');
    }

    private function gate(Student $student): void
    {
        // Student belongs to any campus the admin manages
        $adminCampusIds = auth()->user()
            ->campuses->pluck('id')->toArray();

        if (!in_array($student->campus_id, $adminCampusIds)) {
            abort(403);
        }
    }
}
