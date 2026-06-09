<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // ── Index — all sessions ──────────────────────────────────────────────────
    public function index(Request $request)
    {
        $campusId = CampusContext::id();

        $query = AttendanceSession::where('campus_id', $campusId)
            ->with(['schoolClass', 'section', 'teacher', 'records']);

        if ($request->filled('class_id'))   $query->where('class_id', $request->class_id);
        if ($request->filled('section_id')) $query->where('section_id', $request->section_id);
        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('date'))       $query->whereDate('date', $request->date);
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year ?? date('Y'));
        }

        $sessions = $query->latest('date')->paginate(20);
        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->get();
        $sections = Section::where('campus_id', $campusId)
            ->where('is_active', true)
            ->with('schoolClass')->get();

        return view('admin.attendance.index',
            compact('sessions', 'classes', 'sections'));
    }

    // ── Show + Inline Edit ────────────────────────────────────────────────────
    public function show(AttendanceSession $session)
    {
        $this->gate($session);

        $session->load([
            'schoolClass',
            'section',
            'teacher',
            'records.student',
        ]);

        // All students in this section — to catch any not yet recorded
        $allStudents = Student::where('section_id', $session->section_id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        return view('admin.attendance.show',
            compact('session', 'allStudents'));
    }

    // ── Update a single student's record (inline) ─────────────────────────────
    public function updateRecord(Request $request, AttendanceSession $session, Student $student)
    {
        $this->gate($session);

        $request->validate([
            'status'  => ['required', 'in:present,absent,late,leave'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        AttendanceRecord::updateOrCreate(
            [
                'attendance_session_id' => $session->id,
                'student_id'            => $student->id,
            ],
            [
                'status'  => $request->status,
                'remarks' => $request->remarks,
            ]
        );

        // Recalculate session status
        if ($session->status === 'draft') {
            $session->update(['status' => 'draft']);
        }

        return back()->with('success',
            "{$student->full_name}'s attendance updated.");
    }

    // ── Bulk update all records in a session ──────────────────────────────────
    public function updateSession(Request $request, AttendanceSession $session)
    {
        $this->gate($session);

        $request->validate([
            'records'          => ['required', 'array'],
            'records.*.status' => ['required', 'in:present,absent,late,leave'],
        ]);

        DB::transaction(function () use ($request, $session) {
            foreach ($request->records as $studentId => $data) {
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

        return back()->with('success', 'All attendance records updated.');
    }

    // ── Unlock ────────────────────────────────────────────────────────────────
    public function unlock(AttendanceSession $session)
    {
        $this->gate($session);

        $session->update([
            'locked' => false,
            'status' => 'draft',
        ]);

        return back()->with('success',
            "Attendance for {$session->date->format('d M, Y')} unlocked.");
    }

    // ── Lock (admin force lock) ───────────────────────────────────────────────
    public function lock(AttendanceSession $session)
    {
        $this->gate($session);

        $session->update([
            'locked'       => true,
            'status'       => 'submitted',
            'submitted_at' => $session->submitted_at ?? now(),
        ]);

        return back()->with('success',
            "Attendance for {$session->date->format('d M, Y')} locked.");
    }

    // ── Delete entire session ─────────────────────────────────────────────────
    public function destroy(AttendanceSession $session)
    {
        $this->gate($session);

        DB::transaction(function () use ($session) {
            $session->records()->delete();
            $session->delete();
        });

        return redirect()->route('admin.attendance.index')
            ->with('success',
                "Attendance session for {$session->date->format('d M, Y')} deleted.");
    }

    // ── Monthly Report ────────────────────────────────────────────────────────
    public function report(Request $request)
    {
        $campusId  = CampusContext::id();
        $month     = $request->get('month', date('n'));
        $year      = $request->get('year', date('Y'));
        $classId   = $request->get('class_id');
        $sectionId = $request->get('section_id');

        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->get();
        $sections = Section::where('campus_id', $campusId)
            ->where('is_active', true)->with('schoolClass')->get();

        $sessionsQuery = AttendanceSession::where('campus_id', $campusId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'submitted')
            ->with(['section', 'schoolClass', 'records']);

        if ($classId)   $sessionsQuery->where('class_id', $classId);
        if ($sectionId) $sessionsQuery->where('section_id', $sectionId);

        $sessions = $sessionsQuery->get();

        $workingDays = $sessions->groupBy('section_id')
            ->map(fn($s) => $s->count());

        $studentSummary = [];
        foreach ($sessions as $session) {
            foreach ($session->records as $record) {
                $sid = $record->student_id;
                if (!isset($studentSummary[$sid])) {
                    $studentSummary[$sid] = [
                        'student'       => $record->student,
                        'working_days'  => $workingDays[$session->section_id] ?? 0,
                        'present'       => 0,
                        'absent'        => 0,
                        'late'          => 0,
                        'leave'         => 0,
                        'total'         => 0,
                    ];
                }
                $studentSummary[$sid][$record->status]++;
                $studentSummary[$sid]['total']++;
            }
        }

        $dailyData = $sessions
            ->groupBy(fn($s) => $s->date->format('Y-m-d'))
            ->map(fn($daySessions) => [
                'date'    => $daySessions->first()->date->format('d M'),
                'present' => $daySessions->sum('present_count'),
                'absent'  => $daySessions->sum('absent_count'),
            ])
            ->sortKeys()
            ->values();

        return view('admin.attendance.report', compact(
            'sessions', 'studentSummary', 'dailyData',
            'classes', 'sections', 'month', 'year',
            'classId', 'sectionId', 'workingDays'
        ));
    }

    // ── Student attendance history ─────────────────────────────────────────────
    public function studentAttendance(Request $request, Student $student)
    {
        if ($student->campus_id !== CampusContext::id()) abort(403);

        $month    = (int) $request->get('month', date('n'));
        $year     = (int) $request->get('year', date('Y'));
        $dateFrom = $request->get('date_from',
            now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to', today()->toDateString());

        // All submitted sessions for student's section
        $sessionsQuery = AttendanceSession::where('section_id', $student->section_id)
            ->where('campus_id', CampusContext::id())
            ->with(['records' => fn($q) => $q->where('student_id', $student->id)])
            ->orderBy('date');

        // Monthly summary sessions
        $monthlySessions = (clone $sessionsQuery)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        // Date range sessions for the grid
        $rangeSessions = (clone $sessionsQuery)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->where('status', 'submitted')
            ->get();

        // Build record map: date → record
        $recordMap = [];
        foreach ($rangeSessions as $sess) {
            $record = $sess->records->first();
            $recordMap[$sess->date->toDateString()] = [
                'session' => $sess,
                'record'  => $record,
                'status'  => $record?->status,
                'remarks' => $record?->remarks,
            ];
        }

        // Monthly summary counts
        $summary = [
            'present'      => 0,
            'absent'       => 0,
            'late'         => 0,
            'leave'        => 0,
            'working_days' => $monthlySessions->count(),
        ];

        foreach ($monthlySessions as $sess) {
            $rec = $sess->records->first();
            if ($rec) $summary[$rec->status]++;
        }

        $summary['percentage'] = $summary['working_days'] > 0
            ? round(($summary['present'] / $summary['working_days']) * 100, 1)
            : 0;

        // Calendar data
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDay    = \Carbon\Carbon::create($year, $month, 1)->dayOfWeek;

        $calendarData = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $calendarData[$dateStr] = $recordMap[$dateStr] ?? null;
        }

        $student->load(['schoolClass', 'section']);

        return view('admin.attendance.student', compact(
            'student', 'summary', 'recordMap', 'rangeSessions',
            'month', 'year', 'dateFrom', 'dateTo',
            'daysInMonth', 'firstDay', 'calendarData'
        ));
    }

    private function gate(AttendanceSession $s): void
    {
        if ($s->campus_id !== CampusContext::id()) abort(403);
    }
}