<?php

namespace App\Http\Middleware;

use App\Helpers\CampusContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampusSelectedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Super admin doesn't need campus selection for admin routes
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Admin must have a campus selected
        if (!CampusContext::id()) {
            return redirect()->route('campus.select')
                ->with('info', 'Please select a campus to continue.');
        }

        // Validate admin still has access to stored campus
        if (!CampusContext::adminHasAccess(CampusContext::id())) {
            CampusContext::clear();
            return redirect()->route('campus.select')
                ->with('error', 'Your campus access has changed. Please select again.');
        }

        return $next($request);
    }
}