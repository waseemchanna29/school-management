<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\FeeService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private FeeService $feeService) {}

    public function index()
    {
        $campusId = CampusContext::id();

        $stats = [
            'students'        => Student::where('campus_id', $campusId)->count(),
            'active_students' => Student::where('campus_id', $campusId)->where('status', 'active')->count(),
            'teachers'        => Teacher::where('campus_id', $campusId)->count(),
            'active_teachers' => Teacher::where('campus_id', $campusId)->where('is_active', true)->count(),
            'classes'         => SchoolClass::where('campus_id', $campusId)->where('is_active', true)->count(),
            'subjects'        => Subject::where('campus_id', $campusId)->where('is_active', true)->count(),
        ];

        $recentStudents = Student::where('campus_id', $campusId)
            ->with(['schoolClass', 'section'])->latest()->take(6)->get();

        $recentTeachers = Teacher::where('campus_id', $campusId)
            ->with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentStudents', 'recentTeachers'));
    }

    public function generateMonthlyInvoices(Request $request)
    {
        $request->validate([
            'academic_year' => ['required', 'string'],
            'month'         => ['required', 'integer', 'min:1', 'max:12'],
            'year'          => ['required', 'integer'],
            'due_date'      => ['required', 'date'],
        ]);

        $result = $this->feeService->generateMonthlyInvoicesForCampus(
            CampusContext::id(),
            $request->academic_year,
            (int) $request->month,
            (int) $request->year,
            $request->due_date
        );

        $message = "{$result['generated']} invoice(s) generated, {$result['skipped']} skipped.";

        if (!empty($result['errors'])) {
            $message .= ' Errors: ' . implode(' | ', $result['errors']);
            return redirect()->route('admin.dashboard')->with('error', $message);
        }

        return redirect()->route('admin.dashboard')->with('success', $message);
    }
}