<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\AcademicYearContext;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────────
    private function teacher()
    {
        return Auth::user()->teacher;
    }

    private function classSection()
    {
        return $this->teacher()
            ?->classTeacherOf()
            ->with(['schoolClass'])
            ->first();
    }

    private function authorizeSession(AttendanceSession $session): void
    {
        if ($session->teacher_id !== $this->teacher()->id) abort(403);
    }

    private function yearId(): int
    {
        return AcademicYearContext::id();
    }

    // ── Students in section via enrollment (not direct query) ─────────────────
    private function sectionStudents(int $sectionId): \Illuminate\Support\Collection
    {
        return Student::whereHas('enrollments', fn($q) => $q
            ->where('section_id', $sectionId)
            ->where('academic_year_id', $this->yearId())
            ->where('status', 'active')
        )
        ->with(['enrollments' => fn($q) => $q
            ->where('academic_year_id', $this->yearId())
        ])
        ->orderBy('full_name')
        ->get()
        ->each(fn($s) => $s->enrollment = $s->enrollments->first());
    }

    // ── Take Attendance ───────────────────────────────────────────────────────
    public function take(Request $request)
    {
        $teacher = $this->teacher();
        $section = $this->classSection();

        if (!$section) {
            return redirect()->route('teacher.dashboard')
                ->with('error',
                    'You are not assigned as class teacher of any section.');
        }

        $date = $request->get('date', today()->toDateString());

        // Block future dates
        if ($date > today()->toDateString()) {
            return redirect()
                ->route('teacher.attendance.take',
                    ['date' => today()->toDateString()])
                ->with('error',
                    'Attendance cannot be taken for future dates.');
        }

        // Check existing session
        $session = AttendanceSession::where('section_id', $section->id)
            ->where('academic_year_id', $this->yearId())    // ← NEW
            ->whereDate('date', $date)
            ->with('records.student')
            ->first();

        // Students via enrollment
        $students = $this->sectionStudents($section->id);

        $isLocked   = $session &&
            ($session->isLocked() || $session->isSubmitted());
        $isEditable = !$isLocked;

        return view('teacher.attendance.take', compact(
            'teacher', 'section', 'date', 'session',
            'students', 'isLocked', 'isEditable'
        ));
    }

    // ── Save (draft) ──────────────────────────────────────────────────────────
    public function save(Request $request)
    {
        $request->validate([
            'date'                => ['required', 'date',
                'before_or_equal:today'],
            'attendance'          => ['required', 'array'],
            'attendance.*.status' => ['required',
                'in:present,absent,late,leave'],
        ]);

        $teacher = $this->teacher();
        $section = $this->classSection();

        if (!$section) {
            return back()->with('error',
                'You are not assigned as class teacher.');
        }

        // Block future dates server-side
        if ($request->date > today()->toDateString()) {
            return back()->with('error',
                'Attendance cannot be saved for future dates.');
        }

        // Block edits on locked/submitted sessions
        $existing = AttendanceSession::where('section_id', $section->id)
            ->where('academic_year_id', $this->yearId())    // ← NEW
            ->whereDate('date', $request->date)
            ->first();

        if ($existing &&
            ($existing->isSubmitted() || $existing->isLocked())) {
            return back()->with('error',
                'This attendance session is locked and cannot be edited.');
        }

        DB::transaction(function () use ($request, $teacher, $section, $existing) {
            $session = $existing ?? AttendanceSession::create([
                'campus_id'        => $teacher->campus_id,
                'class_id'         => $section->class_id,
                'section_id'       => $section->id,
                'teacher_id'       => $teacher->id,
                'date'             => $request->date,
                'academic_year_id' => $this->yearId(),    // ← FK not string
                'status'           => 'draft',
                'locked'           => false,
            ]);

            foreach ($request->attendance as $studentId => $data) {
                AttendanceRecord::updateOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'student_id'            => $studentId,
                    ],
                    [
                        'status'  => $data['status'],
                        'remarks' => $data['remarks'] ?? null,
                    ]
                );
            }
        });

        return back()->with('success', 'Attendance saved as draft.');
    }

    // ── Submit (lock) ─────────────────────────────────────────────────────────
    public function submit(Request $request, AttendanceSession $session)
    {
        $this->authorizeSession($session);

        if ($session->isLocked() || $session->isSubmitted()) {
            return back()->with('error',
                'This session is already locked.');
        }

        if ($session->records()->count() === 0) {
            return back()->with('error',
                'Cannot submit — no attendance records found.');
        }

        $session->update([
            'status'       => 'submitted',
            'locked'       => true,
            'submitted_at' => now(),
            'remarks'      => $request->remarks,
        ]);

        return redirect()->route('teacher.attendance.history')
            ->with('success',
                'Attendance submitted and locked successfully.');
    }

    // ── History ───────────────────────────────────────────────────────────────
    public function history(Request $request)
    {
        $teacher  = $this->teacher();
        $section  = $this->classSection();
        $yearId   = $this->yearId();

        $viewMode = $request->get('view', 'day');
        $month    = (int) $request->get('month', date('n'));
        $year     = (int) $request->get('year', date('Y'));
        $dateFrom = $request->get('date_from',
            now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',
            today()->toDateString());

        // Day-wise sessions — scoped to current academic year
        $sessionsQuery = AttendanceSession::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $yearId)    // ← NEW
            ->with(['section.schoolClass', 'records']);

        if ($request->filled('date')) {
            $sessionsQuery->whereDate('date', $request->date);
        } else {
            $sessionsQuery->whereMonth('date', $month)
                          ->whereYear('date', $year);
        }

        $sessions = $sessionsQuery->latest('date')->paginate(20);

        // Student-wise — via enrollments
        $students    = collect();
        $sessionDays = collect();
        $studentGrid = [];

        if ($section) {
            $sessionDays = AttendanceSession::where('section_id', $section->id)
                ->where('academic_year_id', $yearId)    // ← NEW
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('status', 'submitted')
                ->with('records')
                ->orderBy('date')
                ->get();

            // Students via enrollment
            $students = $this->sectionStudents($section->id);

            foreach ($students as $student) {
                $studentGrid[$student->id] = [];
                foreach ($sessionDays as $sess) {
                    $record = $sess->records
                        ->firstWhere('student_id', $student->id);
                    $studentGrid[$student->id]
                        [$sess->date->toDateString()] =
                            $record?->status ?? null;
                }
            }
        }

        // Calendar
        $calendarSessions = AttendanceSession::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $yearId)    // ← NEW
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->keyBy(fn($s) => $s->date->toDateString());

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDay    = \Carbon\Carbon::create($year, $month, 1)->dayOfWeek;

        return view('teacher.attendance.history', compact(
            'teacher', 'section', 'sessions',
            'students', 'sessionDays', 'studentGrid',
            'viewMode', 'month', 'year', 'dateFrom', 'dateTo',
            'calendarSessions', 'daysInMonth', 'firstDay'
        ));
    }

    // ── Show ──────────────────────────────────────────────────────────────────
    public function show(AttendanceSession $session)
    {
        $this->authorizeSession($session);
        $session->load(['section.schoolClass', 'records.student']);
        return view('teacher.attendance.show', compact('session'));
    }

    // ── Student Monthly Report ─────────────────────────────────────────────────
    public function studentReport(Request $request)
    {
        $teacher = $this->teacher();
        $section = $this->classSection();
        $yearId  = $this->yearId();

        if (!$section) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'No section assigned.');
        }

        $month = $request->get('month', date('n'));
        $year  = $request->get('year', date('Y'));

        $sessions = AttendanceSession::where('section_id', $section->id)
            ->where('academic_year_id', $yearId)    // ← NEW
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'submitted')
            ->with('records.student')
            ->get();

        $workingDays = $sessions->count();

        // Students via enrollment
        $students = $this->sectionStudents($section->id);

        $summary = [];
        foreach ($students as $student) {
            $records = collect();
            foreach ($sessions as $sess) {
                $record = $sess->records
                    ->firstWhere('student_id', $student->id);
                if ($record) $records->push($record);
            }

            $summary[] = [
                'student'      => $student,
                'present'      => $records->where('status', 'present')->count(),
                'absent'       => $records->where('status', 'absent')->count(),
                'late'         => $records->where('status', 'late')->count(),
                'leave'        => $records->where('status', 'leave')->count(),
                'working_days' => $workingDays,
                'percentage'   => $workingDays > 0
                    ? round(
                        ($records->where('status', 'present')->count()
                            / $workingDays) * 100,
                        1
                    )
                    : 0,
            ];
        }

        return view('teacher.attendance.student-report', compact(
            'teacher', 'section', 'summary',
            'workingDays', 'month', 'year'
        ));
    }
}