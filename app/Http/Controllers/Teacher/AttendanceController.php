<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // ── Helpers ──────────────────────────────────────────────────────────────

    private function teacher()
    {
        return Auth::user()->teacher;
    }

    private function classSection()
    {
        return $this->teacher()
            ?->classTeacherOf()
            ->with(['schoolClass', 'students'])
            ->first();
    }

    private function authorizeSession(AttendanceSession $session): void
    {
        if ($session->teacher_id !== $this->teacher()->id) abort(403);
    }

    private function currentAcademicYear(): string
    {
        $y = (int) date('Y');
        return date('n') >= 4 ? "$y-" . ($y + 1) : ($y - 1) . "-$y";
    }

    // ── Take Attendance ───────────────────────────────────────────────────────
    public function take(Request $request)
    {
        $teacher = $this->teacher();
        $section = $this->classSection();

        if (!$section) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'You are not assigned as class teacher of any section.');
        }

        $date = $request->get('date', today()->toDateString());

        // ── Block future dates ───────────────────────────────────────────────
        if ($date > today()->toDateString()) {
            $date = today()->toDateString();
            return redirect()
                ->route('teacher.attendance.take', ['date' => $date])
                ->with('error', 'Attendance cannot be taken for future dates.');
        }

        // Check existing session
        $session = AttendanceSession::where('section_id', $section->id)
            ->whereDate('date', $date)
            ->with('records.student')
            ->first();

        // Get students
        $students = Student::where('section_id', $section->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        $isLocked   = $session && ($session->isLocked() || $session->isSubmitted());
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
            'date'                        => ['required', 'date', 'before_or_equal:today'],
            'attendance'                  => ['required', 'array'],
            'attendance.*.status'         => ['required', 'in:present,absent,late,leave'],
        ]);

        $teacher = $this->teacher();
        $section = $this->classSection();

        if (!$section) {
            return back()->with('error', 'You are not assigned as class teacher.');
        }

        // ── Block future dates server-side ───────────────────────────────────
        if ($request->date > today()->toDateString()) {
            return back()->with('error', 'Attendance cannot be saved for future dates.');
        }

        // ── Block edits on locked/submitted sessions ─────────────────────────
        $existing = AttendanceSession::where('section_id', $section->id)
            ->whereDate('date', $request->date)
            ->first();

        if ($existing && ($existing->isSubmitted() || $existing->isLocked())) {
            return back()->with('error', 'This attendance session is locked and cannot be edited.');
        }

        DB::transaction(function () use ($request, $teacher, $section, $existing) {
            $session = $existing ?? AttendanceSession::create([
                'campus_id'     => $teacher->campus_id,
                'class_id'      => $section->class_id,
                'section_id'    => $section->id,
                'teacher_id'    => $teacher->id,
                'date'          => $request->date,
                'academic_year' => $this->currentAcademicYear(),
                'status'        => 'draft',
                'locked'        => false,
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
            return back()->with('error', 'This session is already locked.');
        }

        if ($session->records()->count() === 0) {
            return back()->with('error', 'Cannot submit — no attendance records found.');
        }

        $session->update([
            'status'       => 'submitted',
            'locked'       => true,
            'submitted_at' => now(),
            'remarks'      => $request->remarks,
        ]);

        return redirect()->route('teacher.attendance.history')
            ->with('success', 'Attendance submitted and locked successfully.');
    }

    // ── History — Day-wise + Student-wise ────────────────────────────────────
    public function history(Request $request)
    {
        $teacher = $this->teacher();
        $section = $this->classSection();

        // Default date = today
        $selectedDate = $request->get('date', today()->toDateString());
        $viewMode     = $request->get('view', 'day'); // 'day' or 'student'

        // Month/year for calendar
        $month = (int) $request->get('month', date('n'));
        $year  = (int) $request->get('year', date('Y'));

        // Date range for student view
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to', today()->toDateString());

        // ── Day-wise: all sessions for this teacher ──────────────────────────
        $sessionsQuery = AttendanceSession::where('teacher_id', $teacher->id)
            ->with(['section.schoolClass', 'records']);

        // Filter by date
        if ($request->filled('date')) {
            $sessionsQuery->whereDate('date', $selectedDate);
        } else {
            // Default: show today's session if exists, else current month
            $sessionsQuery->whereMonth('date', $month)
                          ->whereYear('date', $year);
        }

        $sessions = $sessionsQuery->latest('date')->paginate(20);

        // ── Student-wise: selected section students + their records ──────────
        $students    = collect();
        $sessionDays = collect();
        $studentGrid = [];

        if ($section) {
            // All sessions in date range for this section
            $sessionDays = AttendanceSession::where('section_id', $section->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('status', 'submitted')
                ->with('records')
                ->orderBy('date')
                ->get();

            $students = Student::where('section_id', $section->id)
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get();

            // Build grid: student_id → date → status
            foreach ($students as $student) {
                $studentGrid[$student->id] = [];
                foreach ($sessionDays as $sess) {
                    $record = $sess->records->firstWhere('student_id', $student->id);
                    $studentGrid[$student->id][$sess->date->toDateString()] = $record?->status ?? null;
                }
            }
        }

        // ── Calendar: days in selected month that have sessions ──────────────
        $calendarSessions = AttendanceSession::where('teacher_id', $teacher->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->keyBy(fn($s) => $s->date->toDateString());

        // Days in month
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDay    = \Carbon\Carbon::create($year, $month, 1)->dayOfWeek; // 0=Sun

        return view('teacher.attendance.history', compact(
            'teacher', 'section', 'sessions',
            'students', 'sessionDays', 'studentGrid',
            'selectedDate', 'viewMode',
            'month', 'year', 'dateFrom', 'dateTo',
            'calendarSessions', 'daysInMonth', 'firstDay'
        ));
    }

    // ── Show Session Detail ───────────────────────────────────────────────────
    public function show(AttendanceSession $session)
    {
        $this->authorizeSession($session);

        $session->load(['section.schoolClass', 'records.student']);

        return view('teacher.attendance.show', compact('session'));
    }

    // ── Student Report (monthly summary) ─────────────────────────────────────
    public function studentReport(Request $request)
    {
        $teacher = $this->teacher();
        $section = $this->classSection();

        if (!$section) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'No section assigned.');
        }

        $month = $request->get('month', date('n'));
        $year  = $request->get('year', date('Y'));

        $sessions = AttendanceSession::where('section_id', $section->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'submitted')
            ->with('records.student')
            ->get();

        $workingDays = $sessions->count();

        $students = Student::where('section_id', $section->id)
            ->where('status', 'active')
            ->get();

        $summary = [];
        foreach ($students as $student) {
            $records = collect();
            foreach ($sessions as $sess) {
                $record = $sess->records->firstWhere('student_id', $student->id);
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
                    ? round(($records->where('status', 'present')->count() / $workingDays) * 100, 1)
                    : 0,
            ];
        }

        return view('teacher.attendance.student-report', compact(
            'teacher', 'section', 'summary', 'workingDays', 'month', 'year'
        ));
    }
}