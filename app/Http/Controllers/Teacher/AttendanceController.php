<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // Get the authenticated teacher
    private function teacher()
    {
        return Auth::user()->teacher;
    }

    // Get the section this teacher is class teacher of
    private function classSection()
    {
        return $this->teacher()?->classTeacherOf()->with(['schoolClass', 'students.user'])->first();
    }

    // Verify teacher is class teacher of the session's section
    private function authorizeSession(AttendanceSession $session): void
    {
        if ($session->teacher_id !== $this->teacher()->id) abort(403);
    }

    // ── Take Attendance ──────────────────────────────────────────────────────
    public function take(Request $request)
    {
        $teacher = $this->teacher();
        $section = $this->classSection();

        if (!$section) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'You are not assigned as class teacher of any section.');
        }

        $date = $request->get('date', today()->toDateString());

        // Check if session already exists for this date
        $session = AttendanceSession::where('section_id', $section->id)
            ->whereDate('date', $date)
            ->with('records.student')
            ->first();

        // Get all students in this section
        $students = Student::where('section_id', $section->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        return view('teacher.attendance.take', compact(
            'teacher', 'section', 'date', 'session', 'students'
        ));
    }

    // ── Save Attendance ──────────────────────────────────────────────────────
    public function save(Request $request)
    {
        $request->validate([
            'date'        => ['required', 'date'],
            'attendance'  => ['required', 'array'],
            'attendance.*.status' => ['required', 'in:present,absent,late,leave'],
        ]);

        $teacher = $this->teacher();
        $section = $this->classSection();

        if (!$section) {
            return back()->with('error', 'You are not assigned as class teacher.');
        }

        // Check if a submitted/locked session exists
        $existing = AttendanceSession::where('section_id', $section->id)
            ->whereDate('date', $request->date)
            ->first();

        if ($existing && ($existing->isSubmitted() || $existing->isLocked())) {
            return back()->with('error', 'This attendance session is already submitted and locked.');
        }

        DB::transaction(function () use ($request, $teacher, $section, $existing) {
            $academicYear = $this->currentAcademicYear();

            // Create or update session
            $session = $existing ?? AttendanceSession::create([
                'campus_id'    => $teacher->campus_id,
                'class_id'     => $section->class_id,
                'section_id'   => $section->id,
                'teacher_id'   => $teacher->id,
                'date'         => $request->date,
                'academic_year'=> $academicYear,
                'status'       => 'draft',
                'locked'       => false,
            ]);

            // Save records
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

    // ── Submit Attendance ────────────────────────────────────────────────────
    public function submit(Request $request, AttendanceSession $session)
    {
        $this->authorizeSession($session);

        if ($session->isLocked()) {
            return back()->with('error', 'This session is locked.');
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

    // ── History ──────────────────────────────────────────────────────────────
    public function history(Request $request)
    {
        $teacher = $this->teacher();
        $section = $this->classSection();

        $query = AttendanceSession::where('teacher_id', $teacher->id)
            ->with(['section', 'schoolClass', 'records']);

        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year ?? date('Y'));
        }

        $sessions = $query->latest('date')->paginate(20);

        // Monthly summary for chart
        $month = $request->get('month', date('n'));
        $year  = $request->get('year', date('Y'));

        $monthlySessions = AttendanceSession::where('teacher_id', $teacher->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'submitted')
            ->with('records')
            ->get();

        $chartData = $monthlySessions->map(fn($s) => [
            'date'    => $s->date->format('d'),
            'present' => $s->present_count,
            'absent'  => $s->absent_count,
            'late'    => $s->late_count,
        ])->sortBy('date')->values();

        return view('teacher.attendance.history', compact(
            'teacher', 'section', 'sessions', 'chartData', 'month', 'year'
        ));
    }

    // ── Show Session Detail ──────────────────────────────────────────────────
    public function show(AttendanceSession $session)
    {
        $this->authorizeSession($session);

        $session->load([
            'section.schoolClass',
            'records.student',
        ]);

        return view('teacher.attendance.show', compact('session'));
    }

    // ── Student Report ───────────────────────────────────────────────────────
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

        // Build student summary
        $students = Student::where('section_id', $section->id)
            ->where('status', 'active')
            ->get();

        $summary = [];
        foreach ($students as $student) {
            $records = collect();
            foreach ($sessions as $session) {
                $record = $session->records->firstWhere('student_id', $student->id);
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

    private function currentAcademicYear(): string
    {
        $y = (int) date('Y');
        return date('n') >= 4 ? "$y-" . ($y + 1) : ($y - 1) . "-$y";
    }
}