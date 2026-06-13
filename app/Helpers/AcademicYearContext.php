<?php

namespace App\Helpers;

use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AcademicYearContext
{
    const SESSION_KEY = 'active_academic_year_id';

    public static function current(): ?AcademicYear
    {
        $id = Session::get(self::SESSION_KEY);
        if (!$id) return null;
        return AcademicYear::find($id);
    }

    public static function id(): ?int
    {
        return Session::get(self::SESSION_KEY);
    }

    public static function name(): ?string
    {
        return static::current()?->name;
    }

    public static function set(int $yearId): void
    {
        Session::put(self::SESSION_KEY, $yearId);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public static function campusHasAccess(int $campusId): bool
    {
        $id = static::id();
        if (!$id) return false;
        return AcademicYear::where('id', $id)
            ->where('campus_id', $campusId)
            ->exists();
    }

    /**
     * Get available academic years for the current user.
     *
     * Admin  → all years for their campus
     * Teacher → ONLY years assigned by admin via teacher_academic_years
     */
    public static function availableYears(): \Illuminate\Database\Eloquent\Collection
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $campusId = CampusContext::id();
            if (!$campusId) return collect();

            return AcademicYear::where('campus_id', $campusId)
                ->orderByDesc('start_date')
                ->get();
        }

        if ($user->isTeacher()) {
            $teacher = $user->teacher;
            if (!$teacher) return collect();

            // Only return years admin has explicitly assigned
            return $teacher->academicYears()
                ->orderByDesc('start_date')
                ->get();
        }

        return collect();
    }

    /**
     * Check if the current teacher is authorized to access the selected year.
     */
    public static function teacherCanAccessYear(int $academicYearId): bool
    {
        $user = Auth::user();

        // Admins and super admins always have access
        if (!$user->isTeacher()) return true;

        $teacher = $user->teacher;
        if (!$teacher) return false;

        return $teacher->hasYearAccess($academicYearId);
    }

    public static function studentsInSection(
        int $sectionId,
        int $campusId
    ): \Illuminate\Database\Eloquent\Collection {
        return \App\Models\Student::whereHas('enrollments', fn($q) => $q
            ->where('academic_year_id', static::id())
            ->where('campus_id', $campusId)
            ->where('section_id', $sectionId)
            ->where('status', 'active')
        )
        ->with(['enrollments' => fn($q) => $q
            ->where('academic_year_id', static::id())
        ])
        ->orderBy('full_name')
        ->get()
        ->each(fn($s) => $s->enrollment = $s->enrollments->first());
    }

    public static function enrolledStudents(
        int $campusId
    ): \Illuminate\Database\Eloquent\Collection {
        return \App\Models\Student::with([
            'enrollments' => fn($q) => $q
                ->where('academic_year_id', static::id())
                ->where('campus_id', $campusId)
                ->with(['schoolClass', 'section']),
        ])
        ->whereHas('enrollments', fn($q) => $q
            ->where('academic_year_id', static::id())
            ->where('campus_id', $campusId)
            ->where('status', 'active')
        )
        ->get()
        ->each(function ($student) {
            $student->enrollment = $student->enrollments->first();
        });
    }
}