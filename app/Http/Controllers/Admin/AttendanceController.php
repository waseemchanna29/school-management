<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
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
        $classes  = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->get();
        $sections = Section::where('campus_id', $campusId)->where('is_active', true)->get();

        return view('admin.attendance.index', compact('sessions', 'classes', 'sections'));
    }

    public function show(AttendanceSession $session)
    {
        if ($session->campus_id !== CampusContext::id()) abort(403);

        $session->load([
            'schoolClass', 'section', 'teacher',
            'records.student',
        ]);

        return view('admin.attendance.show', compact('session'));
    }

    public function unlock(AttendanceSession $session)
    {
        if ($session->campus_id !== CampusContext::id()) abort(403);

        $session->update([
            'locked' => false,
            'status' => 'draft',
        ]);

        return back()->with('success', "Attendance for {$session->date->format('d M, Y')} unlocked. Teacher can now edit it.");
    }

    public function report(Request $request)
    {
        $campusId = CampusContext::id();
        $month    = $request->get('month', date('n'));
        $year     = $request->get('year',  date('Y'));
        $classId  = $request->get('class_id');
        $sectionId= $request->get('section_id');

        $classes  = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->get();
        $sections = Section::where('campus_id', $campusId)->where('is_active', true)->get();

        // Summary data per section for selected month
        $sessionsQuery = AttendanceSession::where('campus_id', $campusId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'submitted')
            ->with(['section', 'schoolClass', 'records']);

        if ($classId)   $sessionsQuery->where('class_id', $classId);
        if ($sectionId) $sessionsQuery->where('section_id', $sectionId);

        $sessions = $sessionsQuery->get();

        // Working days per section
        $workingDays = $sessions->groupBy('section_id')
            ->map(fn($s) => $s->count());

        // Per-student summary
        $studentSummary = [];
        foreach ($sessions as $session) {
            foreach ($session->records as $record) {
                $sid = $record->student_id;
                if (!isset($studentSummary[$sid])) {
                    $studentSummary[$sid] = [
                        'student'  => $record->student,
                        'present'  => 0,
                        'absent'   => 0,
                        'late'     => 0,
                        'leave'    => 0,
                        'total'    => 0,
                    ];
                }
                $studentSummary[$sid][$record->status]++;
                $studentSummary[$sid]['total']++;
            }
        }

        // Chart data — attendance per day for the month
        $dailyData = $sessions->groupBy(fn($s) => $s->date->format('Y-m-d'))
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
}