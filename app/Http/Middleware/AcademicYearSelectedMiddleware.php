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

        // Super admin bypasses year selection
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Must have a year selected
        if (!AcademicYearContext::id()) {
            return redirect()->route('academic-year.select')
                ->with('info', 'Please select an academic year to continue.');
        }

        // For teachers — verify they are still authorized for selected year
        if ($user->isTeacher()) {
            $yearId  = AcademicYearContext::id();
            $teacher = $user->teacher;

            if (!$teacher) {
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Teacher profile not found.');
            }

            // Check teacher still has access to selected year
            if (!$teacher->hasYearAccess($yearId)) {
                AcademicYearContext::clear();
                return redirect()->route('academic-year.select')
                    ->with('error',
                        'You no longer have access to that academic year. ' .
                        'Please select another.');
            }
        }

        // For admins — verify year belongs to campus
        if ($user->isAdmin()) {
            $campusId = CampusContext::id();
            if ($campusId && !AcademicYearContext::campusHasAccess($campusId)) {
                AcademicYearContext::clear();
                return redirect()->route('academic-year.select')
                    ->with('error',
                        'Academic year mismatch. Please select again.');
            }
        }

        return $next($request);
    }
}