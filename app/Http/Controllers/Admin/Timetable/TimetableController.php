<?php

namespace App\Http\Controllers\Admin\Timetable;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\PeriodTemplate;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    const DAYS = [
        'Mon' => 'Monday',
        'Tue' => 'Tuesday',
        'Wed' => 'Wednesday',
        'Thu' => 'Thursday',
        'Fri' => 'Friday',
        'Sat' => 'Saturday',
    ];

    public function index(Request $request)
    {
        $campusId = CampusContext::id();

        $query = Timetable::where('campus_id', $campusId)
            ->with(['schoolClass', 'section']);

        if ($request->filled('class_id'))      $query->where('class_id', $request->class_id);
        if ($request->filled('academic_year')) $query->where('academic_year', $request->academic_year);
        if ($request->filled('is_active'))     $query->where('is_active', $request->is_active === '1');

        $timetables = $query->latest()->paginate(20);
        $classes    = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->orderBy('name')->get();
        $years      = $this->academicYears();

        return view('admin.timetable.index', compact('timetables', 'classes', 'years'));
    }

    public function create()
    {
        $campusId = CampusContext::id();
        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->with('sections')->orderBy('name')->get();
        $sections = Section::where('campus_id', $campusId)->where('is_active', true)->get();
        $years    = $this->academicYears();
        $allDays  = self::DAYS;

        $hasPeriods = PeriodTemplate::where('campus_id', $campusId)->where('is_active', true)->exists();

        return view('admin.timetable.create', compact('classes', 'sections', 'years', 'allDays', 'hasPeriods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'class_id'      => ['required', 'exists:classes,id'],
            'section_id'    => ['required', 'exists:sections,id'],
            'academic_year' => ['required', 'string'],
            'days'          => ['required', 'array', 'min:1'],
            'days.*'        => ['in:Mon,Tue,Wed,Thu,Fri,Sat'],
            'notes'         => ['nullable', 'string'],
        ]);

        $timetable = Timetable::create([
            'campus_id'     => CampusContext::id(),
            'name'          => $request->name,
            'class_id'      => $request->class_id,
            'section_id'    => $request->section_id,
            'academic_year' => $request->academic_year,
            'days'          => $request->days,
            'is_active'     => true,
            'notes'         => $request->notes,
        ]);

        return redirect()->route('admin.timetable.edit', $timetable)
            ->with('success', 'Timetable created. Now fill in the schedule below.');
    }

    public function show(Timetable $timetable)
    {
        $this->authorize($timetable);

        $campusId = CampusContext::id();
        $periods  = PeriodTemplate::where('campus_id', $campusId)
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('start_time')
            ->get();

        $timetable->load([
            'entries.periodTemplate',
            'entries.subject',
            'entries.teacher',
            'schoolClass',
            'section',
        ]);

        // Build grid: period_id → day → entry
        $grid = [];
        foreach ($periods as $period) {
            $grid[$period->id] = [];
            foreach ($timetable->days as $day) {
                $grid[$period->id][$day] = $timetable->entries
                    ->where('period_template_id', $period->id)
                    ->where('day', $day)
                    ->first();
            }
        }

        $allDays = self::DAYS;

        return view('admin.timetable.show', compact('timetable', 'periods', 'grid', 'allDays'));
    }

    public function edit(Timetable $timetable)
    {
        $this->authorize($timetable);

        $campusId = CampusContext::id();

        $periods  = PeriodTemplate::where('campus_id', $campusId)
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('start_time')
            ->get();

        $subjects = Subject::where('campus_id', $campusId)
            ->where('class_id', $timetable->class_id)
            ->where('is_active', true)
            ->orderBy('name')->get();

        $teachers = Teacher::where('campus_id', $campusId)
            ->where('is_active', true)
            ->orderBy('full_name')->get();

        $timetable->load(['entries.periodTemplate', 'entries.subject', 'entries.teacher', 'schoolClass', 'section']);

        // Build grid: period_id → day → entry
        $grid = [];
        foreach ($periods as $period) {
            $grid[$period->id] = [];
            foreach ($timetable->days as $day) {
                $grid[$period->id][$day] = $timetable->entries
                    ->where('period_template_id', $period->id)
                    ->where('day', $day)
                    ->first();
            }
        }

        $allDays = self::DAYS;

        return view('admin.timetable.edit', compact(
            'timetable', 'periods', 'subjects', 'teachers', 'grid', 'allDays'
        ));
    }

    public function saveGrid(Request $request, Timetable $timetable)
    {
        $this->authorize($timetable);

        $request->validate([
            'entries'                      => ['nullable', 'array'],
            'entries.*.period_template_id' => ['required', 'exists:period_templates,id'],
            'entries.*.day'                => ['required', 'in:Mon,Tue,Wed,Thu,Fri,Sat'],
            'entries.*.type'               => ['required', 'in:lesson,break,free'],
            'entries.*.subject_id'         => ['nullable', 'exists:subjects,id'],
            'entries.*.teacher_id'         => ['nullable', 'exists:teachers,id'],
            'entries.*.custom_label'       => ['nullable', 'string', 'max:100'],
        ]);

        $campusId = CampusContext::id();
        $conflicts = [];

        DB::transaction(function () use ($request, $timetable, $campusId, &$conflicts) {
            // Clear existing entries for this timetable
            $timetable->entries()->delete();

            if (empty($request->entries)) return;

            foreach ($request->entries as $entryData) {
                $type       = $entryData['type'];
                $subjectId  = $type === 'lesson' ? ($entryData['subject_id'] ?? null) : null;
                $teacherId  = $type === 'lesson' ? ($entryData['teacher_id'] ?? null) : null;
                $customLabel = in_array($type, ['break','free']) ? ($entryData['custom_label'] ?? null) : null;

                // Soft conflict check: same teacher, same day, same period, different timetable
                if ($teacherId && $type === 'lesson') {
                    $conflict = TimetableEntry::where('teacher_id', $teacherId)
                        ->where('day', $entryData['day'])
                        ->where('period_template_id', $entryData['period_template_id'])
                        ->where('type', 'lesson')
                        ->whereHas('timetable', fn($q) => $q->where('campus_id', $campusId)
                            ->where('id', '!=', $timetable->id)
                            ->where('is_active', true))
                        ->with(['teacher', 'timetable.schoolClass', 'timetable.section'])
                        ->first();

                    if ($conflict) {
                        $conflicts[] = [
                            'teacher'  => $conflict->teacher->full_name,
                            'day'      => $entryData['day'],
                            'period'   => PeriodTemplate::find($entryData['period_template_id'])?->label,
                            'conflict' => $conflict->timetable->name,
                        ];
                    }
                }

                TimetableEntry::create([
                    'timetable_id'       => $timetable->id,
                    'period_template_id' => $entryData['period_template_id'],
                    'day'                => $entryData['day'],
                    'type'               => $type,
                    'subject_id'         => $subjectId,
                    'teacher_id'         => $teacherId,
                    'custom_label'       => $customLabel,
                ]);
            }
        });

        if (!empty($conflicts)) {
            $msg = 'Timetable saved with ' . count($conflicts) . ' teacher conflict(s): ';
            $msg .= collect($conflicts)->map(fn($c) =>
                "{$c['teacher']} on {$c['day']} / {$c['period']} (also in {$c['conflict']})"
            )->join(' | ');
            return redirect()->route('admin.timetable.edit', $timetable)->with('warning', $msg);
        }

        return redirect()->route('admin.timetable.show', $timetable)
            ->with('success', 'Timetable saved successfully.');
    }

    public function destroy(Timetable $timetable)
    {
        $this->authorize($timetable);
        $timetable->entries()->delete();
        $timetable->delete();
        return redirect()->route('admin.timetable.index')->with('success', 'Timetable deleted.');
    }

    public function toggleActive(Timetable $timetable)
    {
        $this->authorize($timetable);
        $timetable->update(['is_active' => !$timetable->is_active]);
        return back()->with('success', 'Timetable status updated.');
    }

    public function teacherView(Teacher $teacher)
    {
        if ($teacher->campus_id !== CampusContext::id()) abort(403);

        $campusId = CampusContext::id();
        $periods  = PeriodTemplate::where('campus_id', $campusId)
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('start_time')
            ->get();

        // All active entries for this teacher
        $entries = TimetableEntry::where('teacher_id', $teacher->id)
            ->where('type', 'lesson')
            ->whereHas('timetable', fn($q) => $q->where('campus_id', $campusId)->where('is_active', true))
            ->with(['timetable.schoolClass', 'timetable.section', 'subject', 'periodTemplate'])
            ->get();

        // Build grid: period_id → day → entry
        $allDays = self::DAYS;
        $grid    = [];

        foreach ($periods as $period) {
            $grid[$period->id] = [];
            foreach (array_keys($allDays) as $day) {
                $grid[$period->id][$day] = $entries
                    ->where('period_template_id', $period->id)
                    ->where('day', $day)
                    ->first();
            }
        }

        return view('admin.timetable.teacher-view', compact('teacher', 'periods', 'grid', 'allDays'));
    }

    private function authorize(Timetable $t): void
    {
        if ($t->campus_id !== CampusContext::id()) abort(403);
    }

    private function academicYears(): array
    {
        $years = [];
        $start = (int) date('Y') - 1;
        for ($i = $start; $i <= $start + 4; $i++) {
            $years[] = $i . '-' . ($i + 1);
        }
        return $years;
    }
}