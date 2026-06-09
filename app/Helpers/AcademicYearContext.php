<?php

namespace App\Helpers;

use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AcademicYearContext
{
    const SESSION_KEY = 'active_academic_year_id';

    /**
     * Get the currently active academic year model.
     */
    public static function current(): ?AcademicYear
    {
        $id = Session::get(self::SESSION_KEY);
        if (!$id) return null;
        return AcademicYear::find($id);
    }

    /**
     * Get just the ID.
     */
    public static function id(): ?int
    {
        return Session::get(self::SESSION_KEY);
    }

    /**
     * Get the name string (e.g. "2024-2025").
     */
    public static function name(): ?string
    {
        return static::current()?->name;
    }

    /**
     * Set the active academic year.
     */
    public static function set(int $yearId): void
    {
        Session::put(self::SESSION_KEY, $yearId);
    }

    /**
     * Clear the academic year from session.
     */
    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Check if a given campus has access to this year.
     */
    public static function campusHasAccess(int $campusId): bool
    {
        $id = static::id();
        if (!$id) return false;
        return AcademicYear::where('id', $id)
            ->where('campus_id', $campusId)
            ->exists();
    }

    /**
     * Get all academic years for the current user's campus.
     * For teachers — uses their campus_id from teacher profile.
     * For admins — uses CampusContext.
     */
    public static function availableYears(): \Illuminate\Database\Eloquent\Collection
    {
        $user = Auth::user();

        $campusId = null;

        if ($user->isAdmin()) {
            $campusId = CampusContext::id();
        } elseif ($user->isTeacher()) {
            $campusId = $user->teacher?->campus_id;
        }

        if (!$campusId) return collect();

        return AcademicYear::where('campus_id', $campusId)
            ->orderByDesc('start_date')
            ->get();
    }

    /**
     * Get all active students enrolled in a campus for the current year.
     */
    public static function enrolledStudents(int $campusId): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Student::with([
            'enrollments' => fn($q) => $q
                ->where('academic_year_id', static::id())
                ->where('campus_id', $campusId)
                ->with(['schoolClass', 'section']),
        ])
            ->whereHas(
                'enrollments',
                fn($q) => $q
                    ->where('academic_year_id', static::id())
                    ->where('campus_id', $campusId)
                    ->where('status', 'active')
            )
            ->get()
            ->each(function ($student) use ($campusId) {
                // Set a convenient shortcut on each student
                $student->enrollment = $student->enrollments->first();
            });
    }

    /**
     * Get enrolled students for a specific section in the current year.
     */
    public static function studentsInSection(int $sectionId, int $campusId): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Student::whereHas(
            'enrollments',
            fn($q) => $q
                ->where('academic_year_id', static::id())
                ->where('campus_id', $campusId)
                ->where('section_id', $sectionId)
                ->where('status', 'active')
        )
            ->with([
                'enrollments' => fn($q) => $q
                    ->where('academic_year_id', static::id())
            ])
            ->orderBy('full_name')
            ->get()
            ->each(fn($s) => $s->enrollment = $s->enrollments->first());
    }
}
