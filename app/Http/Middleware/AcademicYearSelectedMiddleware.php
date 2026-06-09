<?php

namespace App\Http\Middleware;

use App\Helpers\AcademicYearContext;
use App\Helpers\CampusContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicYearSelectedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Super admin bypasses year selection entirely
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Must have a year selected
        if (!AcademicYearContext::id()) {
            return redirect()->route('academic-year.select')
                ->with('info', 'Please select an academic year to continue.');
        }

        // Validate year still belongs to the correct campus
        if ($user->isAdmin()) {
            $campusId = CampusContext::id();
            if ($campusId && !AcademicYearContext::campusHasAccess($campusId)) {
                AcademicYearContext::clear();
                return redirect()->route('academic-year.select')
                    ->with('error', 'Academic year access changed. Please select again.');
            }
        }

        if ($user->isTeacher()) {
            $campusId = $user->teacher?->campus_id;
            if ($campusId && !AcademicYearContext::campusHasAccess($campusId)) {
                AcademicYearContext::clear();
                return redirect()->route('academic-year.select')
                    ->with('error', 'Academic year access changed. Please select again.');
            }
        }

        return $next($request);
    }
}