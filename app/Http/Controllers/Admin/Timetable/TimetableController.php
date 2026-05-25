<?php

namespace App\Http\Controllers\Admin\Timetable;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Models\TimetablePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    // ─── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $campusId = CampusContext::id();

        $query = Timetable::where('campus_id', $campusId)
            ->with(['schoolClass', 'section']);

        if ($request->filled('class_id'))      $query->where('class_id', $request->class_id);
        if ($request->filled('section_id'))    $query->where('section_id', $request->section_id);
        if ($request->filled('academic_year')) $query->where('academic_year', $request->academic_year);
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $timetables = $query->withCount('periods')->latest()->paginate(20);
        $classes    = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->orderBy('name')->get();
        $sections   = Section::where('campus_id', $campusId)->where('is_active', true)->get();
        $years      = $this->academicYears();
        return view('admin.timetable.index', compact('timetables', 'classes', 'sections', 'years'));
    }

    // ─── Create ─────────────────────────────────────────────────────────────
    public function create()
    {
        $campusId = CampusContext::id();
        $classes  = SchoolClass::where('campus_id', $campusId)->where('is_active', true)
            ->with('sections')->orderBy('name')->get();
        $sections = Section::where('campus_id', $campusId)->where('is_active', true)
            ->with('schoolClass')->get();
        $years    = $this->academicYears();

        return view('admin.timetable.create', compact('classes', 'sections', 'years'));
    }

    // ─── Store ──────────────────────────────────────────────────────────────
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
            ->with('success', 'Timetable created. Now define your time periods, then fill the schedule.');
    }

    // ─── Show (read-only view) ───────────────────────────────────────────────
    public function show(Timetable $timetable)
    {
        $this->authorize($timetable);

        $timetable->load([
            'periods',
            'entries.subject',
            'entries.teacher',
            'entries.timetablePeriod',
            'schoolClass',
            'section',
        ]);

        $grid    = $timetable->buildGrid();
        $periods = $timetable->periods;
        $days    = $timetable->orderedDays();

        return view('admin.timetable.show', compact('timetable', 'grid', 'periods', 'days'));
    }

    // ─── Edit (grid editor) ─────────────────────────────────────────────────
    public function edit(Timetable $timetable)
    {
        $this->authorize($timetable);

        $campusId = CampusContext::id();

        $timetable->load([
            'periods',
            'entries.subject',
            'entries.teacher',
            'entries.timetablePeriod',
            'schoolClass',
            'section',
        ]);

        $subjects = Subject::where('campus_id', $campusId)
            ->where('class_id', $timetable->class_id)
            ->where('is_active', true)
            ->orderBy('name')->get();

        $teachers = Teacher::where('campus_id', $campusId)
            ->where('is_active', true)
            ->orderBy('full_name')->get();

        $grid    = $timetable->buildGrid();
        $periods = $timetable->periods;
        $days    = $timetable->orderedDays();

        // Default active day = first day
        $activeDay = request('day', $days[0] ?? 'Mon');

        return view('admin.timetable.edit', compact(
            'timetable',
            'periods',
            'subjects',
            'teachers',
            'grid',
            'days',
            'activeDay'
        ));
    }

    // ─── Save Grid ──────────────────────────────────────────────────────────
    public function saveGrid(Request $request, Timetable $timetable)
    {
        $this->authorize($timetable);

        $request->validate([
            'day'                         => ['required', 'in:Mon,Tue,Wed,Thu,Fri,Sat'],
            'entries'                     => ['nullable', 'array'],
            'entries.*.timetable_period_id' => ['required', 'exists:timetable_periods,id'],
            'entries.*.type'              => ['required', 'in:lesson,break,free'],
            'entries.*.subject_id'        => ['nullable', 'exists:subjects,id'],
            'entries.*.teacher_id'        => ['nullable', 'exists:teachers,id'],
            'entries.*.custom_label'      => ['nullable', 'string', 'max:100'],
        ]);

        $day       = $request->day;
        $campusId  = CampusContext::id();
        $conflicts = [];

        DB::transaction(function () use ($request, $timetable, $day, $campusId, &$conflicts) {
            // Delete existing entries for this day only
            TimetableEntry::where('timetable_id', $timetable->id)
                ->where('day', $day)
                ->delete();

            if (empty($request->entries)) return;

            foreach ($request->entries as $entryData) {
                $type        = $entryData['type'];
                $subjectId   = $type === 'lesson' ? ($entryData['subject_id']  ?? null) : null;
                $teacherId   = $type === 'lesson' ? ($entryData['teacher_id']  ?? null) : null;
                $customLabel = $type === 'break'  ? ($entryData['custom_label'] ?? null) : null;

                // Soft conflict check — same teacher, same day, same period, different timetable
                if ($teacherId && $type === 'lesson') {
                    $conflict = TimetableEntry::where('teacher_id', $teacherId)
                        ->where('day', $day)
                        ->where('timetable_period_id', $entryData['timetable_period_id'])
                        ->where('type', 'lesson')
                        ->whereHas('timetable', fn($q) => $q
                            ->where('campus_id', $campusId)
                            ->where('id', '!=', $timetable->id)
                            ->where('is_active', true))
                        ->with([
                            'teacher',
                            'timetable.schoolClass',
                            'timetable.section',
                            'timetablePeriod',
                        ])
                        ->first();

                    if ($conflict) {
                        $conflicts[] = sprintf(
                            '%s on %s / %s (also in %s)',
                            $conflict->teacher->full_name,
                            $day,
                            $conflict->timetablePeriod->label,
                            $conflict->timetable->name
                        );
                    }
                }

                TimetableEntry::create([
                    'timetable_id'        => $timetable->id,
                    'timetable_period_id' => $entryData['timetable_period_id'],
                    'day'                 => $day,
                    'type'                => $type,
                    'subject_id'          => $subjectId,
                    'teacher_id'          => $teacherId,
                    'custom_label'        => $customLabel,
                ]);
            }
        });

        // Determine next day to redirect to
        $days    = $timetable->orderedDays();
        $nextDay = $days[array_search($day, $days) + 1] ?? null;

        if (!empty($conflicts)) {
            $msg = count($conflicts) . ' teacher conflict(s): ' . implode(' | ', $conflicts);
            return redirect()
                ->route('admin.timetable.edit', array_merge(
                    ['timetable' => $timetable->id],
                    $nextDay ? ['day' => $nextDay] : []
                ))
                ->with('warning', "Day saved with warnings — {$msg}");
        }

        if ($nextDay) {
            return redirect()
                ->route('admin.timetable.edit', ['timetable' => $timetable->id, 'day' => $nextDay])
                ->with('success', "{$day} saved. Now filling {$nextDay}.");
        }

        return redirect()
            ->route('admin.timetable.show', $timetable)
            ->with('success', 'Timetable fully saved.');
    }

    // ─── Destroy ────────────────────────────────────────────────────────────
    public function destroy(Timetable $timetable)
    {
        $this->authorize($timetable);

        DB::transaction(function () use ($timetable) {
            $timetable->entries()->delete();
            $timetable->periods()->delete();
            $timetable->delete();
        });

        return redirect()->route('admin.timetable.index')
            ->with('success', 'Timetable deleted.');
    }

    // ─── Toggle Active ───────────────────────────────────────────────────────
    public function toggleActive(Timetable $timetable)
    {
        $this->authorize($timetable);
        $timetable->update(['is_active' => !$timetable->is_active]);
        return back()->with('success', 'Timetable status updated.');
    }

    // ─── Teacher Schedule View ───────────────────────────────────────────────
    public function teacherView(Teacher $teacher)
    {
        if ($teacher->campus_id !== CampusContext::id()) abort(403);

        $campusId = CampusContext::id();

        // Collect all active entries for this teacher across all timetables
        $entries = TimetableEntry::where('teacher_id', $teacher->id)
            ->where('type', 'lesson')
            ->whereHas('timetable', fn($q) => $q
                ->where('campus_id', $campusId)
                ->where('is_active', true))
            ->with([
                'timetable.schoolClass',
                'timetable.section',
                'subject',
                'timetablePeriod',
            ])
            ->get();

        // Collect all unique periods across all timetables this teacher is in,
        // grouped by time for display (we normalise by start_time + label)
        $allPeriods = $entries
            ->map(fn($e) => $e->timetablePeriod)
            ->filter()
            ->unique(fn($p) => $p->start_time . $p->label)
            ->sortBy('start_time')
            ->values();

        // Build grid: period key → day → entry
        $grid = [];
        foreach ($allPeriods as $period) {
            $key = $period->start_time . '|' . $period->label;
            $grid[$key] = [];
            foreach (array_keys(Timetable::DAY_LABELS) as $day) {
                // Find matching entry: same day, same start_time + label
                $grid[$key][$day] = $entries->first(function ($e) use ($day, $period) {
                    return $e->day === $day
                        && $e->timetablePeriod
                        && $e->timetablePeriod->start_time === $period->start_time
                        && $e->timetablePeriod->label      === $period->label;
                });
            }
        }

        $days = array_keys(Timetable::DAY_LABELS);

        return view('admin.timetable.teacher-view', compact(
            'teacher',
            'allPeriods',
            'grid',
            'days',
            'entries'
        ));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────
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
